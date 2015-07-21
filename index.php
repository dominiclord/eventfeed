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

// Main interface
$app->get('/main', function ( ) use ($app, $db) {

    $left_posts  = [];
    $right_posts = [];

    $count = 1;
    $posts = $db
        ->posts()
        ->where('status','published')
        ->order('timestamp DESC');

    foreach ($posts as $post) {

        /*
        * @TODO : Figure out how to output structure automatically with NotORM
        */
        $_post = [
            'id'                 => $post['id'],
            'timestamp'          => $post['timestamp'],
            'timestamp_modified' => $post['timestamp_modified'],
            'author'             => $post['author'],
            'text'               => $post['text'],
            'image'              => $post['image'],
            'status'             => $post['status'],
            'type'               => $post['type']
        ];

        switch ( $post['type'] ) {
            case 'text':
                $_post['is_text'] = true;
                break;
            case 'hybrid':
                $_post['is_hybrid'] = true;
                break;
            case 'image':
                $_post['is_image'] = true;
                break;
        }

        if( $count % 2 ){
            $left_posts[]  = $_post;
        }else{
            $right_posts[] = $_post;
        }

        $count++;
    }

    $app->view()->setData([
        'left_posts'  => $left_posts,
        'right_posts' => $right_posts
    ]);

    $app->render('main');
});

/**
* Fetch a post
* @TODO Add authentification
* @param $app  Application
* @param $db   Database connection
*/
$app->get('/posts/:id', function ( $id = null ) use ( $app, $db ) {
    try{
        $row = $db->{'posts'}[$id];

        if($row) {
            $app->response->setStatus(200);
            $app->response()->headers->set('Content-Type', 'application/json');
            echo json_encode($row);
        } else {
            throw new PDOException('No posts found.');
        }

    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    die();
});

/**
* Moderation interface
* @TODO Add authentification
* @param $app  Application
* @param $db   Database connection
*/
$app->get('/moderation(/)(:view)', function ( $view = null ) use ( $app, $db ) {

    $posts = $db->posts();

    // Default view data
    $view_data = [
        'active_view' => 'moderation',
        'posts'       => []
    ];

    // Moderation : Default view, for posts in moderation queue
    // Approved   : Posts waiting to be auto-published.
    // Published  : Posts that appear on main interface
    // Rejected   : Posts that have been rejected
    switch( $view ){
        case 'approved' :
            $posts
                ->where('status','approved')
                ->order('timestamp ASC');

            $view_data['active_approved'] = true;
        break;
        case 'published' :
            $posts
                ->where('status','published')
                ->order('timestamp DESC');

            $view_data['active_published'] = true;
        break;
        case 'rejected' :
            $posts
                ->where('status','rejected')
                ->order('timestamp DESC');

            $view_data['active_rejected'] = true;
        break;
        default:
            $posts
                ->where('status','moderation')
                ->order('timestamp ASC');

            $view_data['active_moderation'] = true;
        break;
    }


    foreach ($posts as $post) {

        try {
            $image_size = getimagesize( __DIR__ . '/uploads/' . $post['image'] );
        } catch (Exception $e) {
            $image_size = null;
        }

        /*
        * @TODO : Figure out how to output structure automatically with NotORM
        */
        $_post = [
            'id'                 => $post['id'],
            'timestamp'          => $post['timestamp'],
            'timestamp_modified' => $post['timestamp_modified'],
            'author'             => $post['author'],
            'text'               => $post['text'],
            'image'              => $post['image'],
            'image_height'       => ( ! empty( $image_size ) ? $image_size[0] : null ),
            'image_width'        => ( ! empty( $image_size ) ? $image_size[1] : null ),
            'status'             => $post['status'],
            'type'               => $post['type']
        ];

        switch ( $post['type'] ) {
            case 'text':
                $_post['is_text'] = true;
                break;
            case 'hybrid':
                $_post['is_hybrid'] = true;
                break;
            case 'image':
                $_post['is_image'] = true;
                break;
        }

        $view_data['posts'][] = $_post;
    }

    $app->view()->setData( $view_data );

    $app->render('moderation');
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

    $author    = empty( $data['author'] ) ? '' : $data['author'];
    $text      = empty( $data['text'] ) ? '' : $data['text'];
    $image     = empty( $data['image'] ) ? '' : $data['image'];
    $type      = "";
    $file_name = "";

    if( $image === "" ){
        $image = false;
    }else{
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

        }else{

            $image = false;

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
            'image'     => $file_name,
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