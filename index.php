<?php

use \Slim\Slim as Slim;

include 'vendor/autoload.php';

$app = new Slim();

$app->config(array(
    'debug'          => true,
    'templates.path' => 'templates'
));

$app->get('/', function () use ($app) {
    $id = 'test';
    $app->render('user.php', array('id' => $id));
});

$app->run();