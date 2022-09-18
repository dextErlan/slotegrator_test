<?php

namespace App\Service;

use App\Repository\UserRepository;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Adapter\Exception\ExceptionInterface;
use Laminas\Authentication\Result;

class AuthAdapterService implements AdapterInterface
{
    private UserRepository $userRepository;
    private string $username;
    private string $password;

    public function __construct(UserRepository $userRepository, string $username, string $password)
    {
        $this->userRepository = $userRepository;
        $this->username = $username;
        $this->password = $password;
    }

    public function authenticate()
    {
        $user = $this->userRepository->findOneBy(['email' => $this->username]);

        if (empty($user)) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ["Пользователь с почтой $this->username не найден!"]);
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