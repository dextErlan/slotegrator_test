#!/usr/bin/env php
<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use App\Command\FillDatabase;
use Symfony\Component\Console\Application;


$container = require 'config/container.php';

$application = new Application();

$application->add($container->get(FillDatabase::class));

$application->run();