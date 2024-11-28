<?php declare(strict_types=1);

namespace App\Controller;

use App\Component\User\UserFactory;
use App\Component\User\UserManager;
use App\Controller\Base\AbstractController;
use App\Entity\User;

class UserCreateAction extends AbstractController
{
    public function __invoke(user $user, UserFactory $userFactory, UserManager $userManager): User
    {
        //$this->validate($user);

        $user = $userFactory->create($user->getEmail(), $user->getPassword());

        $userManager->save($user, true);

        return $user;
    }
}