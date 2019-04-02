<?php

namespace App\Controller\Security;

use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends \FOS\UserBundle\Controller\SecurityController
{
    public function loginAction(Request $request)
    {
        $authChecker = $this->container->get('security.authorization_checker');
        if ($authChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('task.index');
        }

        return parent::loginAction($request);
    }

    /**
     * @Route(
     *     "/user/register",
     *     name="user.register",
     *     methods={"POST"},
     *     condition="request.headers.get('Content-Type') == 'application/json'"
     * )
     */
    public function registerAction(
        Request $request,
        JWTTokenManagerInterface $jwtManager,
        UserManagerInterface $userManager
    )
    {
        $userData = json_decode($request->getContent(), true);

        if (empty($userData['email'])) {
            return new JsonResponse(['email' => 'required'], 400);
        }

        if (empty($userData['username'])) {
            return new Response(['username' => 'required'], 400);
        }

        if (empty($userData['password'])) {
            return new Response(['password' => 'required'], 400);
        }

        if ($userManager->findUserByEmail($userData['email'])) {
            return new JsonResponse(['email' => 'unique'], 400);
        }

        if ($userManager->findUserByUsername($userData['username'])) {
            return new JsonResponse(['username' => 'unique'], 400);
        }

        $user = $userManager->createUser();
        $user->setEnabled(true);
        $user->setEmail($userData['email']);
        $user->setUsername($userData['username']);
        $user->setPlainPassword($userData['password']);
        $userManager->updateUser($user);

        return new JsonResponse(
            [
                'id'    => $user->getId(),
                'token' => $jwtManager->create($user)
            ],
            JsonResponse::HTTP_CREATED
        );
    }
}
