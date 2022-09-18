<?php

use App\Command\FillDatabase;
use App\Entity\User;
use App\Factory\GiftServiceFactory;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Container\ContainerInterface;

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
                $dependency = $container->get(EntityManagerInterface::class);
                return new $requestedName($dependency);
            },
            UserRepository::class => function(ContainerInterface $container, $requestedName) {
                $dependency = $container->get(EntityManagerInterface::class);
                return new $requestedName($dependency, User::class);
            },
            AuthenticationService::class => InvokableFactory::class,
        ],
    ],

    'debug' => false,
];
