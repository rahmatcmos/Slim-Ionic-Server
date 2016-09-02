<?php

// set timezone for timestamps etc
date_default_timezone_set('UTC');

use Illuminate\Database\Capsule\Manager as Capsule;

define("MIGRATIONS_PATH", realpath(dirname(__DIR__, 1)."/database/migrations"));
define("SEEDS_PATH", realpath(dirname(__DIR__, 1)."/database/seeds"));

/**
 * Configure the database and boot Eloquent
 */
$capsule = new Capsule;
$settings = require __DIR__ . '/../app/settings.php';
$capsule->addConnection($settings['settings']['db']);

$capsule->setAsGlobal();

$capsule->bootEloquent();

