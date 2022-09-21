<?php

use App\Command\FillDatabase;
use App\Command\SendMoneyToBank;
use App\Controller\Action\GiveawayAction;
use App\Controller\Action\IndexAction;
use App\Controller\Action\LoginAction;
use App\Factory\GiftServiceFactory;
use App\HttpKernel;
use App\Service\GiveawayService;
use App\Service\RequestToBankAPIService;
use Aura\Router\RouterContainer;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;

return [
    'dependencies' => [
        'abstract_factories' => [
            ReflectionBasedAbstractFactory::class,
        ],
        'factories' => [
            HttpKernel::class => function(ContainerInterface $container, $requestedName) {
                $routerContainer = $container->get(RouterContainer::class);

                return new $requestedName($routerContainer, $container);
            },
            RouterContainer::class => InvokableFactory::class,
            // console
            FillDatabase::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(EntityManagerInterface::class);

                return new $requestedName($dependency);
            },
            SendMoneyToBank::class => function(ContainerInterface $container, $requestedName) {
                $em = $container->get(EntityManagerInterface::class);
                $bankAPIService = $container->get(RequestToBankAPIService::class);

                return new $requestedName($em, $bankAPIService);
            },
            // factory
            GiftServiceFactory::class => function(ContainerInterface $container, $requestedName) {
                $em = $container->get(EntityManagerInterface::class);

                return new $requestedName($em);
            },
            // service
            AuthenticationService::class => InvokableFactory::class,
            GiveawayService::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(GiftServiceFactory::class);

                return new $requestedName($dependency);
            },
            RequestToBankAPIService::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(CurlHttpClient::class);

                return new $requestedName($dependency);
            },
            // action
            IndexAction::class =>  InvokableFactory::class,
            LoginAction::class =>  function(ContainerInterface $container, $requestedName) {
                $authService = $container->get(AuthenticationService::class);
                $em = $container->get(EntityManagerInterface::class);

                return new $requestedName($authService, $em);
            },
            GiveawayAction::class =>  function(ContainerInterface $container, $requestedName) {
                $authService = $container->get(AuthenticationService::class);
                $giveawayService = $container->get(GiveawayService::class);

                return new $requestedName($authService, $giveawayService);
            },
        ],
    ],
    'debug' => false,
];
