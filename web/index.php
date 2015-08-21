<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */
$app = require __DIR__.'/../app/app.php';
if ($app instanceof \AstekGo\TuleapRestApiBridge\Application) {
    $app->run();
} else {
    echo 'Failed to initialize application.';
}
