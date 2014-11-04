<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(TRUE);
\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);

$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
    ->addDirectory(__DIR__ . '/../vendor/others')
    ->addDirectory(__DIR__ . '/../vendor/zvitek/')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/parameters.neon');

$container = $configurator->createContainer();

return $container;
