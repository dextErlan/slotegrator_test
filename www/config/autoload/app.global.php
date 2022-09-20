<?php

use App\Command\FillDatabase;
use App\Controller\Action\GiveawayAction;
use App\Controller\Action\IndexAction;
use App\Controller\Action\LoginAction;
use App\Entity\User;
use App\Factory\GiftServiceFactory;
use App\Repository\UserRepository;
use App\Service\GiveawayService;
use App\Service\RequestToBankAPIService;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
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
            FillDatabase::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(EntityManagerInterface::class);

                return new $requestedName($dependency);
            },
            GiftServiceFactory::class => function(ContainerInterface $container, $requestedName) {
                $em = $container->get(EntityManagerInterface::class);
                $bankAPIService = $container->get(RequestToBankAPIService::class);

                return new $requestedName($em, $bankAPIService);
            },
            UserRepository::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(EntityManagerInterface::class);

                return new $requestedName($dependency, User::class);
            },
            AuthenticationServiceInterface::class => AuthenticationService::class,
            GiveawayService::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(GiftServiceFactory::class);

                return new $requestedName($dependency);
            },
            RequestToBankAPIService::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(CurlHttpClient::class);

                return new $requestedName($dependency);
            },
            IndexAction::class =>  InvokableFactory::class,
            LoginAction::class =>  function(ContainerInterface $container, $requestedName) {
                $authService = $container->get(AuthenticationServiceInterface::class);
                $userRepository = $container->get(UserRepository::class);

                return new $requestedName($authService, $userRepository);
            },
            GiveawayAction::class =>  function(ContainerInterface $container, $requestedName) {
                $authService = $container->get(AuthenticationServiceInterface::class);
                $giveawayService = $container->get(GiveawayService::class);

                return new $requestedName($authService, $giveawayService);
            },
        ],
    ],

    'debug' => false,
];
