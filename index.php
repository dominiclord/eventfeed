<?php

use \Slim\Slim as Slim;

require_once 'vendor/autoload.php';
require_once 'config.php';

$app = new Slim();

$app->config(array(
    'debug'          => true,
    'templates.path' => 'templates'
));

// Submit a post
$app->post('/submit', function () use ($app) {

});

// User interface
$app->get('/', function () use ($app) {
    $db = connect_db();
    $result = $db->query( 'SELECT * FROM posts;' );
    $data = [];

    while ( $row = $result->fetch_array(MYSQLI_ASSOC) ) {
        $data[] = $row;
    }

    //var_dump( sha1('') );
    var_dump($data);

    $app->render('user.php', array(
            'page_title' => "Your Friends",
            'data' => 'data'
        )
    );
});

$app->run();