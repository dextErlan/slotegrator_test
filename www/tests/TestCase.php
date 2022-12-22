<?php

namespace Tests;

use App\HttpKernel;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function getContainer(): ServiceManager
    {
        return require __DIR__ . '/../config/container.php';
    }

    public function getHttpKernel(): HttpKernel
    {
        $container = $this->getContainer();

        return $container->get(HttpKernel::class);
    }

    public function getAuthService(): AuthenticationService
    {
        $container = $this->getContainer();

        return $container->get(AuthenticationService::class);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        $container = $this->getContainer();

        return $container->get(EntityManagerInterface::class);
    }
}