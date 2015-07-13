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

    if( isset( $_FILES['files']['name'] ) && ! empty( $_FILES['files']['name'] ) ){
        var_dump( '@TODO' );
        die();
        $folder = '/var/www/eventfeed/utilisateur/uploads//';
        $taille = filesize($_FILES['files']['tmp_name']);
        $extensions = array('.png','.jpg','.jpeg','.PNG','.JPG','.JPEG');
        $extension = strrchr($_FILES['files']['name'],'.');
        if(!in_array($extension, $extensions)){
            echo 'ERROR you must upload the right type';
        }else{
            $file = $timestamp.$extension;
            if(move_uploaded_file($_FILES['files']['tmp_name'],$folder.$file)){
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

        // Generate a unique id for the post
        $generator = new RandomStringGenerator;
        $token = $generator->generate(40);

        $post = [
            'id'        => $token,
            'timestamp' => $timestamp,
            'author'    => $author,
            'text'      => $text,
            'image'     => $file,
            'status'    => 'moderation',
            'type'      => $type
        ];

        // Parse the data
        //$author = htmlspecialchars($author);
        //$text = htmlspecialchars($text);
        //$author = mysqli_real_escape_string($db,$author);
        //$text = mysqli_real_escape_string($db,$text);
        //$request = "INSERT INTO posts (timestamp, author, text, image, status, type) VALUES ('".$timestamp."', '".$author."', '".$text."', '".$file."', 'moderation', '".$type."');";
        //mysqli_query($db,$request);
        //mysqli_close($db);

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