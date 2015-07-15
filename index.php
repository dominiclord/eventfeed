<?php

use \Slim\Slim as Slim;
use \Utils\RandomStringGenerator;

require_once 'vendor/autoload.php';
require_once 'utils/index.php';

$app = new Slim([
    'view'           => new \Slim\Mustache\Mustache(),
    'debug'          => true,
    'templates.path' => 'views'
]);
$pdo = new PDO('mysql:dbname=eventfeed_local;host:127.0.0.1','root','root');
$db  = new NotORM($pdo);

// User interface
$app->get('/main', function ( ) use ($app, $db) {

    $posts = [];

    foreach ($db->posts() as $post) {
        $posts[] = $post;
    }

    $app->view()->setData([
        'page_title' => "Your Friends",
        'data'       => 'data'
    ]);

    $app->render('user');
});

// Display after post submit
$app->get('/submit', function () use ($app) {
    $app->render('success');
});

// Submit a post
$app->post('/submit', function () use ($app, $db) {

    $app->response()->header('Content-Type', 'application/json');
    $data = $app->request()->post();

    if( empty( $data['timestamp'] ) ){
        $timestamp_date = new \DateTime( 'now', new \DateTimeZone('America/Montreal') );
        $timestamp = $timestamp_date->getTimestamp();
    }

    $author = empty( $data['author'] ) ? '' : $data['author'];
    $text   = empty( $data['text'] ) ? '' : $data['text'];
    $image  = empty( $data['image'] ) ? '' : $data['image'];
    $type   = "";
    $file   = "";

    if( $image === "" ){
        $image = false;
    }else{
        $file  = $image;
        $image = true;
    }

    $error = false;

    // Generate a unique id for the post
    $generator = new RandomStringGenerator;
    $token = $generator->generate(40);

    if( isset( $_FILES['image'] ) && ( isset( $_FILES['image']['name'] ) && $_FILES['image']['name'] ) && ( isset( $_FILES['image']['tmp_name'] ) && $_FILES['image']['tmp_name'] ) ) {

        // File upload properties
        $base_path    = __DIR__.'/';
        $upload_path  = 'uploads/';
        $dir          = $base_path.$upload_path;
        $max_filesize = 134220000; //128M
        $finfo         = new finfo(FILEINFO_MIME_TYPE);
        $mimetypes    = [
            'image/gif',
            'image/png',
            'image/jpeg'
        ];

        $file_name = $token;
        $file_data = $_FILES['image'];
        $file_info = pathinfo( $file_data['name'] );
        $file_size = filesize( $file_data['tmp_name'] );
        $file_type = $finfo->file( $file_data['tmp_name'] );

        if (isset($file_info['extension']) && $file_info['extension']) {
            $file_name .= '.'.$file_info['extension'];
        }else{
            $file_name .= '.jpg';
        }

        $target = $dir.$file_name;

        /**
        * @TODO
        * Manage image failures gracefully
        */
        if ( !is_writable($dir) ) {
            throw new Exception('Error: upload directory is not writeable');
            die();
        }

        if ( file_exists($target) ) {
            /**
            * @TODO
            * Generate new token? This could mean the token already exists in the database as well
            */
            throw new Exception('Error: file already exists');
            die();
        }

        if ( !in_array( $file_type, $mimetypes ) ) {
            throw new Exception('Error: rejected mimetype');
            die();
        }

        if ( $file_size > $max_filesize ) {
            throw new Exception('Error: file too big');
            die();
        }

        if( move_uploaded_file( $file_data['tmp_name'], $target) ){

            $imagick = new \Imagick( realpath($target) );

            $exif_data = $imagick->getImageProperties("exif:*");

            if ( ! empty( $exif_data ) && isset( $exif_data['exif:Orientation'] ) && $orientation = $exif_data['exif:Orientation'] ) {

                chmod($target, 0755);

                switch($orientation){
                    //case '1': // Normal
                    case '3':// 180 rotate left
                        $imagick->rotateimage(new \ImagickPixel('none'), 180);
                    break;
                    case '6':// 90 rotate right
                        $imagick->rotateimage(new \ImagickPixel('none'), -90);
                    break;
                    case '8':// 90 rotate left
                        $imagick->rotateimage(new \ImagickPixel('none'), 90);
                    break;
                }

                $imagick->writeImage( $target );

            }

            $image = true;

        }

    }

    if( $image === true && $text !== '' ){
        $type = 'hybrid';
    }elseif( $image === true && $text === '' ){
        $type = 'image';
    }elseif( $image === false && $text !== '' ){
        $type = 'text';
    }

    if($author === '' || $type === ''){
        throw new Exception('Error: empty author or post type');
        die();
    }else{

        $post = [
            'id'        => $token,
            'timestamp' => $timestamp,
            'author'    => $author,
            'text'      => $text,
            'image'     => $file,
            'status'    => 'moderation',
            'type'      => $type
        ];

        $result = $db->posts->insert( $post );
        $app->redirect('/submit');
    }

});

// User interface
$app->get('/', function ( ) use ($app, $db) {

    $posts = [];

    foreach ($db->posts() as $post) {
        $posts[] = $post;
    }

    //var_dump($posts);

    $app->view()->setData([
        'page_title' => "Your Friends",
        'data'       => 'data'
    ]);
    $app->render('user');
});

$app->run();