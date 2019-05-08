<?php

namespace App\Acme\CommentBundle;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use FOS\CommentBundle\Acl\RoleCommentAcl as BaseRoleCommentAcl;
use FOS\CommentBundle\Model\CommentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleCommentAcl extends BaseRoleCommentAcl
{
    private $tokenStorage;
    private $authorizationChecker;
    private $requestStack;
    private $entityManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        $createRole,
        $viewRole,
        $editRole,
        $deleteRole,
        $commentClass
    )
    {
        parent::__construct($authorizationChecker, $createRole, $viewRole, $editRole, $deleteRole, $commentClass);

        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public function canCreate()
    {
        $request = $this->requestStack->getCurrentRequest();
        $threadId = $request->get('id');

        if (preg_match('/^task_[0-9]{1,}$/', $threadId)) {
            if (preg_match('/[0-9]{1,}$/', $threadId, $matches)) {
                $id = $matches[0];
                /** @var Task $task */
                $task = $this->entityManager->find(Task::class, $id);
                $currentUser = $this->tokenStorage->getToken()->getUser();
                if (null != $task && $task->getUser() == $currentUser) {
                    return true;
                }
            }
        }

        return parent::canCreate();
    }

    public function canReply(CommentInterface $parent = null)
    {
        return self::canCreate();
    }

    public function canView(CommentInterface $comment)
    {
        return self::canCreate();
    }
}
