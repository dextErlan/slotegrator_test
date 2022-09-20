<?php

namespace App\Controller\Action;

use App\Service\GiveawayService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GiveawayAction implements RequestHandlerInterface
{
    private AuthenticationServiceInterface $authService;
    private GiveawayService $giveawayService;

    public function __construct(AuthenticationServiceInterface $authService, GiveawayService $giveawayService)
    {
        $this->authService = $authService;
        $this->giveawayService = $giveawayService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (! $this->authService->hasIdentity()) {
            return new JsonResponse("Необходимо войти в систему!");
        }

        $giftName = $this->giveawayService->getRandomGift($this->authService->getIdentity());

        return new JsonResponse("Поздравляем, Вы выиграли $giftName!");
    }
}