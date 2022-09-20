<?php

namespace App\Controller\Action;

use App\Repository\UserRepository;
use App\Service\AuthAdapterService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginAction implements RequestHandlerInterface
{
    private AuthenticationServiceInterface $authService;
    private UserRepository $userRepository;

    public function __construct(AuthenticationServiceInterface $authService, UserRepository $userRepository)
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->authService->hasIdentity()) {
            return new JsonResponse("Вы вошли в систему!");
        }

        $username = $request->getAttribute('email');
        $password = $request->getAttribute('password');

        $authAdapter = new AuthAdapterService($this->userRepository, $username, $password);

        $result = $this->authService->authenticate($authAdapter);

        if (! $result->isValid()) {
            return new JsonResponse(['error' => $result->getMessages()], 401);
        }

        return new JsonResponse("Вы вошли в систему!");
    }
}