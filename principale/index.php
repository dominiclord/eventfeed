<?php
    require_once dirname(__FILE__).'/../config.php';

    // Connecting to the database
    $db = db_connection();
    mysqli_set_charset($db,"utf8");
?>
<!DOCTYPE html>
    <!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]><html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>EventFeed</title>
        <meta name="viewport" content="width=device-width,initial-scale=1, maximum-scale=1">
        <link rel="stylesheet" href="../assets/css/normalize.css">
        <link rel="stylesheet" href="../assets/css/main.css">
        <link rel="shortcut icon" type="image/png" href="favicon.png">
        <link rel="icon" href="favicon.ico">
        <script src="../assets/js/vendor/modernizr-2.7.1.min.js"></script>
    </head>
    <body>
        <header>
            <p>EventFeed</p>
        </header>
<?php
    $request = "SELECT * FROM posts WHERE status = 'publier' ORDER BY timestamp ASC";
    $posts = mysqli_query($db,$request);
    mysqli_close($db);
    $sPosts='';
    $alt = true;
    $leftPosts='';
    $rightPosts='';
    while($post = mysqli_fetch_assoc($posts)){
        $sPosts.='
            <div data-timestamp="'.$post['timestamp'].'" class="post ';
        switch($post['type']){
            case 'text':
                $sPosts.='text"><p><strong>'.$post['author'].' :</strong> '.$post["text"].'</p>';
            break;
            case 'hybrid':
                $sPosts.='hybrid">
                    <img src="../utilisateur/uploads/'.$post["image"].'">
                    <div class="text"><p><strong>'.$post['author'].' :</strong> '.$post["text"].'</p></div>';
            break;
            case 'image':
                $sPosts.='image"><img src="../utilisateur/uploads/'.$post["image"].'">';
            break;
        }
        $sPosts.='
            </div>';
        //Alternons entre la gauche et droite
        if($alt == true){
            $leftPosts.=$sPosts;
            $alt = false;
        }else{
            $rightPosts.=$sPosts;
            $alt = true;
        }
        $sPosts='';
    }
?>
        <div id="columns">
            <div class="column">
                <?php echo $leftPosts; ?>
            </div>
            <div class="column">
                <?php echo $rightPosts; ?>
            </div>
        </div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="../assets/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
        <script src="../assets/js/plugins.js"></script>
        <script src="../assets/js/main.js"></script>
    </body>
</html>