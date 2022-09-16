#!/usr/bin/env php
<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use App\Command\FillDatabase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Console\Application;


$configData = include (dirname(__DIR__) . '/config/database.php');
$config = ORMSetup::createAnnotationMetadataConfiguration($configData['paths'],true);
$entityManager = EntityManager::create($configData['params'], $config);

$application = new Application();

$application->add(new FillDatabase($entityManager));

$application->run();