<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */
require_once __DIR__.'/../vendor/autoload.php';

// Configuration loading
$config = null;
if (file_exists(__DIR__.'/config.php')) {
    require __DIR__.'/config.php';
}

// Bridge loading
$app = new \AstekGo\TuleapRestApiBridge\Application($config);

return $app;
