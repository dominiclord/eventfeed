<?php

use \Slim\Slim as Slim;
use \Utils\RandomStringGenerator;

require_once 'vendor/autoload.php';
require_once 'utils/index.php';

$app = new Slim();
$pdo = new PDO('mysql:dbname=eventfeed_local;host:127.0.0.1','root','root');
$db  = new NotORM($pdo);

$app->config(array(
    'debug'          => true,
    'templates.path' => 'templates'
));

// Display after post submit
$app->get('/submit', function () use ($app) {
    $app->render('success.php');
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
        $base_path = __DIR__.'/';
        $upload_path = 'uploads/';
        $dir = $base_path.$upload_path;
        $filename = $token;
        $max_filesize = 134220000; //128M
        $target = $dir.$filename;
        $file_date = $_FILES['image'];

        if (!is_writable($dir)) {
            throw new Exception('Error: upload directory is not writeable');
            die();
        }
        if (!file_exists($target)) {
            throw new Exception('Error: file already exists');
            die();
        }

        $info = new finfo(FILEINFO_MIME_TYPE);

        // Tesf for mimetype
        //$info->file($file_data['tmp_name']));

        $filesize = filesize( $file_data['tmp_name'] );

        if ( $filesize > $max_filesize ) {
            throw new Exception('Error: file too big');
            die();
        }

        if( move_uploaded_file( $file_data['tmp_name'], $target) ){
            die();

            if($extension!='.png'&&$extension!='.PNG'){
                chmod($folder.$file, 0755);
                $exif = exif_read_data($folder.$file);
                if(isset($exif['Orientation'])){
                    $ort = $exif['Orientation'];
                    switch($ort){
                        case 1:// normal
                            $source = imagecreatefromjpeg($folder.$file);
                            imagejpeg($source,$folder.$file,50);
                        break;
                        case 3:// 180 rotate left
                            $source = imagecreatefromjpeg($folder.$file);
                            $rotate = imagerotate($source, 180, -1);
                            imagejpeg($rotate,$folder.$file,50);
                        break;
                        case 6:// 90 rotate right
                            $source = imagecreatefromjpeg($folder.$file);
                            $rotate = imagerotate($source, -90, -1);
                            imagejpeg($rotate,$folder.$file,50);
                        break;
                        case 8:// 90 rotate left
                            $source = imagecreatefromjpeg($folder.$file);
                            $rotate = imagerotate($source, 90, -1);
                            imagejpeg($rotate,$folder.$file,50);
                        break;
                    }
                }
            }

            $image = true;

        }

    }

    if( $image === true && $text !== "" ){
        $type = "hybrid";
    }else{
        if( $image==true && $text=="" ){
            $type = "image";
        }elseif( $image === false && $text !== "" ){
            $type = "text";
        }
    }

    if($author === "" || $type === ""){
        $error = true;
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
    $app->render('user.php');
});

$app->run();