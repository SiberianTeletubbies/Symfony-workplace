<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Services\UserService;
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
    private $redis;
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->redis = RedisAdapter::createConnection('redis://localhost:6379');
        $this->userService = $userService;
    }

    /**
     * @Route("/", name="task.index", methods={"GET"})
     *
     * @param Request        $request
     * @param TaskRepository $taskRepository
     *
     * @return Response
     */
    public function index(Request $request, TaskRepository $taskRepository): Response
    {
        $user = null;
        $page = $request->get('page', 1);

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
        }
        $pager = $taskRepository->getByPage($page, $user);

        return $this->render('task/index.html.twig', [
            'tasks' => $pager->getCurrentPageResults(),
            'pager' => $pager,
        ]);
    }

    /**
     * @Route("/new", name="task.new", methods={"GET","POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function new(Request $request): Response
    {
        $return_url = $this->getReturnUrl();

        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $task->setUser($this->getUser());
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirect($return_url);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
            'return_url' => $return_url,
        ]);
    }

    /**
     * @Route("/{id}", name="task.show", methods={"GET"})
     *
     * @param Task $task
     *
     * @return Response
     */
    public function show(Task $task): Response
    {
        $this->accessControl($task);

        $return_url = $this->getReturnUrl();

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'return_url' => $return_url,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="task.edit", methods={"GET","POST"})
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return Response
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
            'return_url' => $return_url,
        ]);
    }

    /**
     * @Route("/{id}", name="task.delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return Response
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
     * @Route("/{id}/image", name="task.image", methods={"GET"})
     *
     * @param Request         $request
     * @param Task            $task
     * @param DownloadHandler $downloadHandler
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request, Task $task, DownloadHandler $downloadHandler)
    {
        $this->accessControl($task);
        $routeName = $request->get('_route');

        return $downloadHandler->downloadObject(
            $task,
            $fileField = 'task.download' == $routeName ? 'attachmentFile' : 'imageFile',
            null,
            true,
            false
        );
    }

    private function accessControl(Task $task)
    {
        if (!$this->userService->accessControlToTask($task)) {
            throw $this->createAccessDeniedException('У Вас недостаточно прав');
        }
    }

    private function getReturnUrl()
    {
        $user = $this->getUser();
        $page = $this->redis->exists("controller[{$user->getId()}].task.page") ?
            $this->redis->get("controller[{$user->getId()}].task.page") : 1;

        return $this->generateUrl('task.index', ['page' => $page]);
    }
}
