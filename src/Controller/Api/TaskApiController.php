<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Services\UserService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Templating\FilterExtension;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * @Route("/api/task")
 */
class TaskApiController extends AbstractController
{
    const TASKS_PER_PAGE = 8;
    private $router;
    /** @var UserService */
    private $userService;
    /** @var UploaderHelper */
    private $uploaderHelper;
    /** @var FilterExtension */
    private $imagineFilter;

    public function __construct(
        UrlGeneratorInterface $router,
        UserService $userService,
        UploaderHelper $uploaderHelper,
        CacheManager $cache
    ) {
        $this->router = $router;
        $this->userService = $userService;
        $this->uploaderHelper = $uploaderHelper;
        $this->imagineFilter = new FilterExtension($cache);
    }

    public function start()
    {
        return $this->render('spa/index.html.twig');
    }

    /**
     * @Route("/list/{page<\d+>?}", name="api.task.tasks", methods={"GET"})
     *
     * @param int            $page
     * @param TaskRepository $taskRepository
     *
     * @return Response
     */
    public function tasks($page = 0, TaskRepository $taskRepository): Response
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

        $result = [];
        $result['nbpages'] = $pager->getNbPages();

        $result['tasks'] = [];
        $tasks = $page > 0 ?
            $query
                ->setFirstResult(($page - 1) * self::TASKS_PER_PAGE)
                ->setMaxResults(self::TASKS_PER_PAGE)->getResult() :
            $query->getResult();
        /* @var Task $task */
        foreach ($tasks as $task) {
            $result['tasks'][] = $this->generateTaskObject($task);
        }

        return $this->jsonObjectResponse($result);
    }

    /**
     * @Route("/{id<\d+>}", name="api.task", methods={"GET"})
     *
     * @param Task $task
     *
     * @return Response
     */
    public function task(Task $task): Response
    {
        if ($response = $this->accessControl($task)) {
            return $response;
        }

        $result = $this->generateTaskObject($task);

        return $this->jsonObjectResponse($result);
    }

    /**
     * @Route("/save/{id<\d+>?}", name="api.task.save", methods={"POST"})
     *
     * @param Request   $request
     * @param Task|null $task
     *
     * @return Response
     */
    public function save(Request $request, Task $task = null): Response
    {
        if (null == $task) {
            $task = new Task();
        } elseif ($response = $this->accessControl($task)) {
            return $response;
        }

        $formData = [
            'description' => $request->request->get('description'),
            'duration' => [
                'days' => $request->request->get('duration_days'),
                'hours' => $request->request->get('duration_hours'),
            ],
            'additionalData' => [
                'info' => $request->request->get('info'),
                'priority' => $request->request->get('priority'),
            ],
        ];

        $user = $request->request->get('userid');
        if ($this->isGranted('ROLE_ADMIN') && $user) {
            $formData['user'] = $user;
        }

        $deleteFile = $request->request->get('deleteFile');
        if ($deleteFile) {
            $formData['attachmentFile'] = ['delete' => 1];
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('attachmentFile');
        if ($file) {
            $formData['attachmentFile'] = ['file' => $file];
        }

        $deleteImage = $request->request->get('deleteImage');
        if ($deleteImage) {
            $formData['imageFile'] = ['delete' => 1];
        }

        /** @var UploadedFile $file */
        $image = $request->files->get('imageFile');
        if ($image) {
            $formData['imageFile'] = ['file' => $image];
        }

        $form = $this->createForm(TaskType::class, $task, ['csrf_protection' => false]);
        $form->submit($formData);

        if ($form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $task->setUser($this->getUser());
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
        }

        $errors = $this->getFormErrors($form);
        if (count($errors) > 0) {
            return $this->createResponse(400, $errors);
        }

        $result = $this->generateTaskObject($task);

        return $this->jsonObjectResponse($result);
    }

    /**
     * @Route("/{id<\d+>}", name="api.task.delete", methods={"DELETE"})
     *
     * @param Task $task
     *
     * @return Response
     */
    public function delete(Task $task): Response
    {
        if ($response = $this->accessControl($task)) {
            return $response;
        }

        $result = $this->generateTaskObject($task);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        return $this->jsonObjectResponse($result);
    }

    private function generateTaskObject(Task $task)
    {
        $result = [];
        $result['id'] = $task->getId();
        $result['description'] = $task->getDescription();
        $interval = new \DateInterval($task->getDuration());
        $result['duration_days'] = $interval->d;
        $result['duration_hours'] = $interval->h;

        $additionalData = $task->getAdditionalData();
        $result['additional_data'] = null != $additionalData ? $additionalData->getData() : [];

        if (null != $task->getAttachment()) {
            $result['attachment'] = $this->router->generate(
                'task.download', ['id' => $task->getId()]
            );
            $result['attachment_filename'] = $task->getAttachmentFileName();
        } else {
            $result['attachment'] = $result['attachment_filename'] = null;
        }

        if (null != $task->getImage()) {
            $result['image'] = $this->router->generate(
                'task.image', ['id' => $task->getId()]
            );
            $imageFile = $this->uploaderHelper->asset($task, 'imageFile');
            $imageMini = $this->imagineFilter->filter($imageFile, 'task_image_100x100');
            $result['image_mini'] = $imageMini;
            $result['image_filename'] = $task->getImageFileName();
        } else {
            $result['image'] = $result['image_mini'] = $result['image_filename'] = null;
        }

        $user = $task->getUser();
        if (null != $user) {
            $result['userid'] = $user->getId();
            $result['username'] = $user->getUsername();
        } else {
            $result['userid'] = $result['username'] = null;
        }

        return $result;
    }

    private function jsonObjectResponse($object): JsonResponse
    {
        $jsonContent = json_encode($object);

        return new JsonResponse($jsonContent, 200, [], true);
    }

    private function accessControl(Task $task)
    {
        if (!$this->userService->accessControlToTask($task)) {
            return $this->createResponse(404);
        }

        return null;
    }

    private function createResponse($code = 200, $content = null)
    {
        $response = new Response($content ? json_encode($content) : null, $code);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        // Global
        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        // Fields
        $this->getFormChildErrors($form, $errors);

        return $errors;
    }

    private function getFormChildErrors(FormInterface $form, &$errors)
    {
        foreach ($form as $child /* @var Form $child */) {
            $this->getFormChildErrors($child, $errors);
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }
    }
}
