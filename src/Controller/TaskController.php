<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Vich\UploaderBundle\Handler\DownloadHandler;

/**
 * @Route("/task")
 * @IsGranted("ROLE_USER")
 */
class TaskController extends AbstractController
{
    const TASKS_PER_PAGE = 8;
    private $redis;

    public function __construct()
    {
        $this->redis = RedisAdapter::createConnection('redis://localhost:6379');
    }

    /**
     * @Route("/", name="task.index", methods={"GET"})
     */
    public function index(Request $request, TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();
        $page = $request->get('page', 1);

        $queryBuilder = $taskRepository->createQueryBuilder('t');
        $query = $this->isGranted('ROLE_ADMIN') ?
            $queryBuilder->getQuery() :
            $queryBuilder->andWhere('t.user = :user')->setParameter('user', $this->getUser())->getQuery();

        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(self::TASKS_PER_PAGE);

        $page = $page > $pager->getNbPages() ? $pager->getNbPages() : $page;

        $pager->setCurrentPage($page);
        $query->setFirstResult(($page - 1) * self::TASKS_PER_PAGE)
            ->setMaxResults(self::TASKS_PER_PAGE);
        $this->redis->setex("controller[{$user->getId()}].task.page", 3600, $page);

        return $this->render('task/index.html.twig', [
            'tasks' => $query->getResult(),
            'pager' => $pager,
        ]);
    }

    /**
     * @Route("/new", name="task.new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $return_url = $this->getReturnUrl();

        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirect($return_url);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'return_url' => $return_url
        ]);
    }

    /**
     * @Route("/{id}", name="task.show", methods={"GET"})
     */
    public function show(Task $task): Response
    {
        $this->accessControl($task);

        $return_url = $this->getReturnUrl();

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'return_url' => $return_url
        ]);
    }

    /**
     * @Route("/{id}/edit", name="task.edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        $this->accessControl($task);

        $return_url = $this->getReturnUrl();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($return_url);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'return_url' => $return_url
        ]);
    }

    /**
     * @Route("/{id}", name="task.delete", methods={"DELETE"})
     */
    public function delete(Request $request, Task $task): Response
    {
        $this->accessControl($task);

        $return_url = $this->getReturnUrl();

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirect($return_url);
    }

    /**
     * @Route("/{id}/attachment", name="task.download", methods={"GET"})
     */
    public function download(Task $task, DownloadHandler $downloadHandler)
    {
        $this->accessControl($task);

        return $downloadHandler->downloadObject(
            $task,
            $fileField = 'attachmentFile',
            null,
            true,
            false
        );
    }

    private function accessControl(Task $task)
    {
        if (!$this->isGranted('ROLE_ADMIN') && $task->getUser() != $this->getUser()) {
            throw $this->createAccessDeniedException('У Вас недостаточно прав');
        }
    }

    private function getReturnUrl()
    {
        $user = $this->getUser();
        $page = $this->redis->exists("controller[{$user->getId()}].task.page") ?
            $this->redis->get("controller[{$user->getId()}].task.page") : 1;
        return $this->generateUrl('task.index', [ 'page' => $page ]);
    }
}
