<?php
/**
 * This file is part of the project TuleapRestApiBridge
 *
 * Last edited by jpvantelon@astek.fr
 *
 * Copyright ASTEK (c) 2015.
 */
$config = array(
    'application' => array(
        'logFilePath' => __DIR__.'/../log/app.log',
        'debug' => true,
    ),
    'bridge' => array(
        'tuleapServerBaseUrl' => 'https://tuleap.server.example.com',
//        'tuleapRestApiBridgeRootUrl' => 'https://tuleap.server.example.com',
//        'tuleapRestApiBridgeRedirectBase' => 'bridge',
    ),
);
