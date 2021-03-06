<?php

/**
* Main EventFeed functions and routing
*
* @author Dominic Lord <dlord@outlook.com>
* @copyright 2015 dominiclord
* @version 2015-07-01
* @link http://github.com/dominiclord/eventfeed
* @since Version 2015-07-01
*/

use \Slim\Slim as Slim;
use \Utils\RandomStringGenerator;

require_once '../vendor/autoload.php';
require_once '../utils/index.php';

$app = new Slim([
    'view'           => new \Slim\Mustache\Mustache(),
    'debug'          => true,
    'templates.path' => 'assets/templates'
]);
$pdo = new PDO('mysql:dbname=eventfeed_local;host:127.0.0.1','root','root');
$db  = new NotORM($pdo);

/**
 * Main interface display
 * @param $app  Application
 * @param $db   Database connection
 */
$app->get('/main(/)', function ( ) use ($app, $db) {

    $left_posts  = [];
    $right_posts = [];

    $count = 1;
    $posts = $db
        ->posts()
        ->where('status','published')
        ->order('timestamp DESC');

    $color_palette = new ColorPalette('#4285F4', [
        'variance' => 25
    ]);

    foreach ($posts as $post) {

        $color = $color_palette->render();

        /**
         * @todo : Figure out how to output structure automatically with NotORM
         */
        $_post = [
            'id'                 => $post['id'],
            'timestamp'          => $post['timestamp'],
            'timestamp_modified' => $post['timestamp_modified'],
            'author'             => $post['author'],
            'text'               => $post['text'],
            'image'              => $post['image'],
            'status'             => $post['status'],
            'type'               => $post['type'],
            'color'              => ''
        ];

        switch ( $post['type'] ) {
            case 'text':
                $_post['has_text'] = true;
                break;
            case 'hybrid':
                $_post['has_text']  = true;
                $_post['has_image'] = true;
                break;
            case 'image':
                $_post['has_image'] = true;
                break;
        }

        if( $count % 2 ){
            $left_posts[]  = $_post;
        }else{
            $right_posts[] = $_post;
        }

        $count++;
    }

    $columns = [
        [
            'posts' => $left_posts
        ],
        [
            'posts' => $right_posts
        ]
    ];

    $app->view()->setData([
        'columns' => $columns
    ]);

    $app->render('main');

});

/**
 * Fetch posts for main interface
 * @param $app  Application
 * @param $db   Database connection
 */
$app->get('/main/posts(/)', function ( ) use ($app, $db) {

    $response = [
        'status' => 'error',
        'posts'  => []
    ];

    try{
        $posts = $db
            ->posts()
            ->where('status','approved')
            ->order('timestamp ASC');

        foreach ($posts as $post) {

            /**
             * @todo : Figure out how to output structure automatically with NotORM
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
                    $_post['has_text'] = true;
                    break;
                case 'hybrid':
                    $_post['has_text']  = true;
                    $_post['has_image'] = true;
                    break;
                case 'image':
                    $_post['has_image'] = true;
                    break;
            }

            $response['posts'][] = $_post;

        }

        $response['status'] = 'ok';
        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);

    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
    die();
});

/**
 * Set post to published
 * @todo Add authentification
 * @param $app  Application
 * @param $db   Database connection
 */
$app->put('/main/posts/:id', function ( $id = null ) use ( $app, $db ) {
    $app->response()->headers->set('Content-Type', 'application/json');
    try{

        $data = $app->request()->put();
        $post = $db->{'posts'}[$id];

        $app->response()->headers->set('Content-Type', 'application/json');

        if( $post ) {

            $post['status'] = 'published';

            $post->update();

            $app->response->setStatus(200);
            echo '{"success":{"text":"Post modified successfully"}}';
        } else {
            throw new PDOException('No posts found.');
        }

    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
    die();
});

/**
 * Simple, catchall moderation interface routing. Specific routing is handled by Backbone
 *
 * @param $app  Application
 * @param $db   Database connection
 * @todo  Add authentification
 * @see   https://gist.github.com/funkatron/1447169
 */
$app->get('/moderation(/)(:view)', function ($view = null) use ($app, $db) {

    $app->render('moderation');

    /*
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

        $_post = [
            'id'                 => $post['id'],
            'timestamp'          => $post['timestamp'],
            'timestamp_modified' => $post['timestamp_modified'],
            'author'             => $post['author'],
            'text'               => $post['text'],
            'image'              => $post['image'],
            'image_height'       => (!empty($image_size) ? $image_size[0] : null),
            'image_width'        => (!empty($image_size) ? $image_size[1] : null),
            'status'             => $post['status'],
            'type'               => $post['type']
        ];

        switch ($post['type']) {
            case 'text':
                $_post['has_text'] = true;
                break;
            case 'hybrid':
                $_post['has_text']  = true;
                $_post['has_image'] = true;
                break;
            case 'image':
                $_post['has_image'] = true;
                break;
        }

        $view_data['posts'][] = $_post;
    }

    $app->view()->setData( $view_data );
    */
})->conditions(array('view' => '.+'));

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

/*
=============================

URL                           HTTP Method  Operation
/api/v1/posts                 GET          Returns an array of posts
/api/v1/posts/:id             GET          Returns the post with id of :id
/api/v1/posts                 POST         Adds a new post and return it with an id attribute added
/api/v1/posts/:id             PUT          Updates the contact with id of :id

=============================
*/

/**
 * Main API group
 * @param $app  Application
 * @param $db   Database connection
 */
$app->group('/api', function () use ($app, $db) {

    /**
     * API group v1
     * @param $app  Application
     * @param $db   Database connection
     */
    $app->group('/v1', function () use ($app, $db) {


        /**
        * Fetch all posts
        * @todo Add authentification
        * @param $app  Application
        * @param $db   Database connection
        */
        $app->get('/posts', function ( ) use ($app, $db) {

            // @todo If status is other than published, request authorization
            $status = ($app->request->get('status')) ? : 'published';

            try {
                $posts = $db
                    ->posts()
                    ->where('status', $status)
                    ->order('timestamp DESC');

                $_posts = [];

                foreach ($posts as $post) {

                    try {
                        $image_size = getimagesize( __DIR__ . '/uploads/' . $post['image'] );
                    } catch (Exception $e) {
                        $image_size = null;
                    }

                    $_post = $post;
                    $_post['image_height'] = (!empty($image_size) ? $image_size[0] : null);
                    $_post['image_width'] = (!empty($image_size) ? $image_size[1] : null);

                    switch ($post['type']) {
                        case 'text':
                            $_post['has_text'] = true;
                            break;
                        case 'hybrid':
                            $_post['has_text'] = true;
                            $_post['has_image'] = true;
                            break;
                        case 'image':
                            $_post['has_image'] = true;
                            break;
                    }

                    $_posts[] = $_post;
                }

                if (count($_posts)) {
                    $response = [
                        'results' => $_posts,
                        'status' => 'OK'
                    ];
                } else {
                    throw new PDOException('No posts found.');
                }
            } catch(PDOException $e) {
                $response = [
                    'error_message' => $e->getMessage(),
                    'results' => [],
                    'status' => 'ERROR'
                ];
            }

            $app->response()->headers->set('Content-Type', 'application/json');
            $app->response()->setStatus(200);
            echo json_encode($response);
            die();
        });


        /**
         * Fetch a single post
         * @todo Add authentification
         * @param $app  Application
         * @param $db   Database connection
         */
        $app->get('/posts/:id', function ( $id = null ) use ( $app, $db ) {

            try {
                $row = $db->{'posts'}[$id];

                if ($row) {
                    $response = [
                        'results' => $row,
                        'status' => 'OK'
                    ];
                } else {
                    throw new PDOException('No posts found.');
                }
            } catch(PDOException $e) {
                $response = [
                    'error_message' => $e->getMessage(),
                    'results' => [],
                    'status' => 'ERROR'
                ];
            }

            $app->response()->headers->set('Content-Type', 'application/json');
            $app->response()->setStatus(200);
            echo json_encode($response);
            die();
        });


        /**
         * Create a post
         * @todo Add authentification
         * @param $app  Application
         * @param $db   Database connection
         */
        $app->post('/posts', function () use ($app, $db) {

            $request_body = $app->request()->getBody();
            $data = json_decode($request_body);

            if (empty($data->timestamp)) {
                $timestamp_date = new \DateTime( 'now', new \DateTimeZone('America/Montreal') );
                $timestamp = $timestamp_date->getTimestamp();
            }

            $author    = empty($data->author) ? '' : $data->author;
            $text      = empty($data->text) ? '' : $data->text;
            $image     = empty($data->image) ? '' : $data->image;
            $type      = "";
            $file_name = "";

            if ( $image === "" ) {
                $image = false;
            } else {
                $image = true;
            }

            $error = false;

            // Generate a unique id for the post
            $generator = new RandomStringGenerator;
            $token = $generator->generate(40);

            try {

                if( $image ) {

                    // File upload properties
                    $base_path    = __DIR__.'/';
                    $upload_path  = 'uploads/';
                    $dir          = $base_path . $upload_path;
                    $max_filesize = 134220000; //128M
                    $finfo         = new finfo(FILEINFO_MIME_TYPE);
                    $mimetypes    = [
                        'image/gif',
                        'image/png',
                        'image/jpeg'
                    ];

                    $file_name = $token;
                    $file_data = $data->image;
                    $file_size = strlen($file_data);
                    $file_type = mime_content_type($file_data);

                    $file = $file_data;
                    $file = preg_replace('#^data:image/\w+;base64,#i', '', $file);
                    $file = str_replace(' ', '+', $file);
                    $file = base64_decode($file);

                    if (isset($file_type)) {
                        $extension = str_replace('image/', '', $file_type);
                        // Personal preference!
                        if ($extension === 'jpeg') {
                            $extension = 'jpg';
                        }
                        $file_name .= '.' . $extension;
                    } else {
                        $file_name .= '.jpg';
                    }

                    $target = $dir . $file_name;

                    /**
                     * @todo
                     * Manage image failures gracefully
                     */
                    if (!in_array($file_type, $mimetypes)) {
                        throw new Exception('Error: rejected mimetype');
                    }

                    if (!is_writable($dir)) {
                        throw new Exception('Error: upload directory is not writeable');
                    }

                    if (file_exists($target)) {
                        /**
                         * @todo
                         * Generate new token? This could mean the token already exists in the database as well
                         */
                        throw new Exception('Error: file already exists');
                    }

                    if ($file_size > $max_filesize) {
                        throw new Exception('Error: file too big');
                    }

                    if (file_put_contents($target, $file)) {
                        $img = new \Charcoal\Image\Imagick\ImagickImage();
                        $img->open($target);
                        $img->set_data([
                            'target'=>$target,
                            'effects'=>[
                                [
                                    'type'=>'autoorientation'
                                ]
                            ]
                        ]);
                        $img->process();
                        $img->save();

                        $image = true;
                    }else{
                        $image = false;
                    }

                }

                if ( $image === true && $text !== '' ) {
                    $type = 'hybrid';
                } elseif ( $image === true && $text === '' ) {
                    $type = 'image';
                } elseif ( $image === false && $text !== '' ) {
                    $type = 'text';
                }

                if ($author === '' || $type === '') {
                    throw new Exception('Empty author or post type');
                } else {

                    $post = [
                        'id'        => $token,
                        'timestamp' => $timestamp,
                        'author'    => $author,
                        'text'      => $text,
                        'image'     => $file_name,
                        'status'    => 'moderation',
                        'type'      => $type
                    ];

                    $result = $db->posts->insert($post);

                    switch ( $type ) {
                        case 'text':
                            $result['has_text'] = true;
                            break;
                        case 'hybrid':
                            $result['has_text']  = true;
                            $result['has_image'] = true;
                            break;
                        case 'image':
                            $result['has_image'] = true;
                            break;
                    }

                    $response = [
                        'results' => $result,
                        'status' => 'OK'
                    ];
                }

            } catch(Exception $e) {
                $response = [
                    'error_message' => $e->getMessage(),
                    'results' => [],
                    'status' => 'ERROR'
                ];
            }

            $app->response()->headers->set('Content-Type', 'application/json');
            $app->response()->setStatus(200);
            echo json_encode($response);
            die();
        });


        /**
         * Modify a post
         * @todo Add authentification
         * @param $app  Application
         * @param $db   Database connection
         */
        $app->put('/posts/:id', function ( $id = null ) use ( $app, $db ) {
            try{

                $data = $app->request()->put();
                $post = $db->{'posts'}[$id];

                $app->response()->headers->set('Content-Type', 'application/json');

                if( $post ) {

                    $timestamp_date = new \DateTime( 'now', new \DateTimeZone('America/Montreal') );
                    $timestamp = $timestamp_date->getTimestamp();

                    foreach ($data as $key => $value) {
                        $post[$key] = $value;
                    }

                    // If no status is set, data is being modified, and we need to update the modified_timestamp
                    if( empty( $data['status'] ) ) {
                        $post['timestamp_modified'] = $timestamp;
                    }

                    $post->update();

                    $app->response->setStatus(200);
                    $app->response()->headers->set('Content-Type', 'application/json');
                    echo '{"success":{"text":"Post modified successfully"}}';
                } else {
                    throw new PDOException('No posts found.');
                }

            } catch(PDOException $e) {
                $app->response()->setStatus(404);
                echo '{"error":{"text":"'. $e->getMessage() .'"}}';
            }
            die();
        });

    });

});

$app->run();