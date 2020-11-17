<?php

date_default_timezone_set("Europe/Zagreb");

$loader = require '../../vendor/autoload.php';
//$loader->add('XML', '/srv/studijweb/emrex.studij.hr/src/');
//$loader->add('Core', '/srv/studijweb/emrex.studij.hr/src/');
//$loader->add('EMREX', '/srv/studijweb/emrex.studij.hr/src/');
//$loader->add('Modules', '/srv/studijweb/emrex.studij.hr/src/');

$loader->add('XML', '../src');
$loader->add('Core', '../src');
$loader->add('EMREX', '../src/');
$loader->add('Modules', '../src');

session_start();

// Instantiate the app
$settings = require '../src/settings.php';

$container = new \Slim\Container($settings);
$container['renderer'] = new Slim\Views\PhpRenderer("../templates");
$container['Landing'] = function ($container) {
    return new Modules\Landing($container);
};

$app = new \Slim\App($container);

// Set up dependencies
require '../src/dependencies.php';

// Register DB
require '../src/db.php';

// Register routes
require '../src/routes.php';

// Run app
$app->run();