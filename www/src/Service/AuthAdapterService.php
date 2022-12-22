<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;

class AuthAdapterService implements AdapterInterface
{
    /** @var EntityRepository<User>  */
    private EntityRepository $userRepository;
    private string $username;
    private string $password;

    /**
     * @param EntityRepository<User> $userRepository
     * @param string $username
     * @param string $password
     */
    public function __construct(EntityRepository $userRepository, string $username, string $password)
    {
        $this->userRepository = $userRepository;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return Result
     */
    public function authenticate(): Result
    {
        $user = $this->userRepository->findOneBy(['email' => $this->username]);

        if (empty($user)) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ["Логин и пароль не совпадают!"]);
        }

        if (!password_verify($this->password, $user->getPassword())) {
            return new Result(
                Result::FAILURE_CREDENTIAL_INVALID,
                null,
                ["Логин и пароль не совпадают!"]);
        }

        return new Result(Result::SUCCESS, $user,[]);
    }
}