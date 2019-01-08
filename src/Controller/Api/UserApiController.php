<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/user")
 */
class UserApiController extends AbstractController
{
    /** @var UserService */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/", name="api.user", methods={"GET"})
     */
    public function getUser(): Response
    {
        $user = $this->userService->getCurrentUser();

        $result = array(
            'id'       => $user->getId(),
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
            'admin'    => $this->isGranted('ROLE_ADMIN')
        );

        return $this->jsonObjectResponse($result);
    }

    /**
     * @Route("/list", name="api.user.list", methods={"GET"})
     */
    public function getUsers(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->createEmptyResponse(401);
        }

        $result = array();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();
        /** @var User $user */
        foreach ($users as $user) {
            $item = array();
            $item['id'] = $user->getId();
            $item['username'] = $user->getUsername();
            $item['email'] = $user->getEmail();
            $result[] = $item;
        }

        return $this->jsonObjectResponse($result);
    }

    private function jsonObjectResponse($object) : JsonResponse
    {
        $jsonContent = json_encode($object);
        return new JsonResponse($jsonContent, 200, array(), true);
    }

    private function createEmptyResponse($code = 200)
    {
        $response = new Response(null, $code);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
