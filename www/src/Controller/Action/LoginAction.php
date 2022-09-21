<?php

namespace App\Controller\Action;

use App\Entity\User;
use App\Service\AuthAdapterService;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginAction implements RequestHandlerInterface
{
    private AuthenticationServiceInterface $authService;
    private EntityManagerInterface $em;

    public function __construct(AuthenticationServiceInterface $authService, EntityManagerInterface $em)
    {
        $this->authService = $authService;
        $this->em = $em;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->authService->hasIdentity()) {
            return new JsonResponse("Вы уже вошли в систему!");
        }

        $parsedBody = $request->getParsedBody();

        if (empty($parsedBody['email']) || empty($parsedBody['password'])) {
            return new JsonResponse(['error' => ['Не переданы имя пользователя и пароль!']], 400);
        }

        $authAdapter = new AuthAdapterService($this->em->getRepository(User::class), $parsedBody['email'], $parsedBody['password']);

        $result = $this->authService->authenticate($authAdapter);

        if (! $result->isValid()) {
            return new JsonResponse(['error' => $result->getMessages()], 401);
        }

        return new JsonResponse("Вы вошли в систему!");
    }
}