<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;

class UserAboutMeAction extends AbstractController
{
    public function __invoke(UserRepository $userRepository): User
    {
        return $this->getUser();
    }
}
