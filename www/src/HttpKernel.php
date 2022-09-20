<?php

namespace App;

use App\Controller\Action\GiveawayAction;
use App\Controller\Action\IndexAction;
use App\Controller\Action\LoginAction;
use Aura\Router\RouterContainer;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpKernel
{
    private RouterContainer $routerContainer;
    private ServiceLocatorInterface $container;

    public function __construct(RouterContainer $routerContainer, ServiceLocatorInterface $container)
    {
        $this->routerContainer = $routerContainer;
        $this->container = $container;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $map = $this->routerContainer->getMap();

        // add a route to the map, and a handler for it
        $map->get('home', '/', IndexAction::class);
        $map->post('login', '/login', LoginAction::class);
        $map->get('giveaway', '/giveaway', GiveawayAction::class);


        $matcher = $this->routerContainer->getMatcher();

        $route = $matcher->match($request);
        if (! $route) {
            return new JsonResponse("Страница не найдена!", 404);
        }

        foreach ($route->attributes as $key => $val) {
            $request = $request->withAttribute($key, $val);
        }

        $action = $this->container->get($route->handler);

        return $action($request);
    }
}