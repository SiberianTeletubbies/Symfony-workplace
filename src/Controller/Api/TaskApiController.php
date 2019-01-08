<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Services\UserService;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api/task")
 */
class TaskApiController extends AbstractController
{
    const TASKS_PER_PAGE = 8;
    private $router;
    /** @var UserService */
    private $userService;

    public function __construct(
        UrlGeneratorInterface $router,
        UserService $userService
    )
    {
        $this->router = $router;
        $this->userService = $userService;
    }

    public function start()
    {
        return $this->render('spa/index.html.twig');
    }

    /**
     * @Route("/list/{page<\d+>?1}", name="api.task.tasks", methods={"GET"})
     */
    public function tasks($page, TaskRepository $taskRepository): Response
    {
        $admin = $this->isGranted('ROLE_ADMIN');

        $queryBuilder = $taskRepository->createQueryBuilder('t');
        $query = $admin ?
            $queryBuilder->getQuery() :
            $queryBuilder->andWhere('t.user = :user')->setParameter('user', $this->getUser())->getQuery();

        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::TASKS_PER_PAGE);
        $page = $page > $pager->getNbPages() ? $pager->getNbPages() : $page;

        $result = array();
        $result['nbpages'] = $pager->getNbPages();

        $result['tasks'] = array();
        $tasks = $query->setFirstResult(($page - 1) * self::TASKS_PER_PAGE)
            ->setMaxResults(self::TASKS_PER_PAGE)->getResult();
        /* @var Task $task */
        foreach ($tasks as $task) {
            $result['tasks'][] = $this->generateTaskObject($task);
        }

        return $this->jsonObjectResponse($result);
    }

    /**
     * @Route("/{id<\d+>}", name="api.task", methods={"GET"})
     */
    public function task(Task $task): Response
    {
        if ($response = $this->accessControl($task)) {
            return $response;
        }

        $result = $this->generateTaskObject($task);

        return $this->jsonObjectResponse($result);
    }

    private function generateTaskObject(Task $task)
    {
        $result = array();
        $result['id'] = $task->getId();
        $result['description'] = $task->getDescription();
        $interval = new \DateInterval($task->getDuration());
        $result['duration_days'] = $interval->d;
        $result['duration_hours'] = $interval->h;
        if ($task->getAttachment() != null) {
            $result['attachment'] = $this->router->generate(
                'task.download', ['id' => $task->getId()]
            );
            $result['attachment_filename'] = $task->getAttachmentFileName();
        } else {
            $result['attachment'] = $result['attachment_filename'] = null;
        }
        $user = $task->getUser();
        if ($user != null) {
            $result['userid'] = $user->getId();
            $result['username'] = $user->getUsername();
        } else {
            $result['userid'] = $result['username'] = null;
        }
        return $result;
    }

    private function jsonObjectResponse($object) : JsonResponse
    {
        $jsonContent = json_encode($object);
        return new JsonResponse($jsonContent, 200, array(), true);
    }

    private function accessControl(Task $task)
    {
        if (!$this->userService->accessControlToTask($task)) {
            return $this->createEmptyResponse(401);
        }
        return null;
    }

    private function createEmptyResponse($code = 200)
    {
        $response = new Response(null, $code);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
