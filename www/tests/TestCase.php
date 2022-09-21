<?php

namespace Tests;

use App\HttpKernel;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationService;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function getContainer()
    {
        return require __DIR__ . '/../config/container.php';
    }

    public function getHttpKernel()
    {
        $container = $this->getContainer();

        return $container->get(HttpKernel::class);
    }

    public function getAuthService()
    {
        $container = $this->getContainer();

        return $container->get(AuthenticationService::class);
    }

    public function getEntityManager()
    {
        $container = $this->getContainer();

        return $container->get(EntityManagerInterface::class);
    }
}