<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Services\StringFormatterService;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    private $formatter;

    public function __construct(UrlGeneratorInterface $router, StringFormatterService $formatter)
    {
        $this->router = $router;
        $this->formatter = $formatter;
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
            $item = new \stdClass();
            $item->id = $task->getId();
            $item->description = $task->getDescription();
            $item->duration = $this->formatter->formatDuration($task->getDuration());
            $item->attachment = $task->getAttachment() != null ?
                $this->router->generate(
                    'task.download', ['id' => $task->getId()]
                ) : null;
            if ($admin) {
                $user = $task->getUser();
                if ($user != null) {
                    $item->userid = $user->getId();
                    $item->username = $user->getUsername();
                } else {
                    $item->userid = $item->username = null;
                }
            }
            $result['tasks'][] = $item;
        }

        return $this->jsonObjectResponse($result);
    }

    private function jsonObjectResponse($object) : JsonResponse
    {
        $jsonContent = json_encode($object);
        return new JsonResponse($jsonContent, 200, array(), true);
    }
}
