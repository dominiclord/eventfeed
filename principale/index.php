<?php
    function db_connect(){
        $db_connect = mysqli_connect('localhost','root','root','ef_local');
        if (mysqli_connect_errno()){
            printf('Connect failed: %s\n', mysqli_connect_error());
            exit();
        }
        return($db_connect);
    }
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>EventFeed</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.6.1.min.js"></script>
    </head>
    <body>
        <header>
            <!--<img height="50px" src="./img/logo.png">-->
            <p>EventFeed</p>
        </header>

<?php
    $db = db_connect();
    mysqli_set_charset($db,'utf8');
    $request = "SELECT * FROM posts WHERE status = 'published' ORDER BY timestamp ASC";
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
            case 'texte':
                $sPosts.='texte"><p><strong>'.$post['auteur'].' :</strong> '.$post["texte"].'</p>';
            break;
            case 'hybride':
                $sPosts.='hybride">
                    <img src="../utilisateur/uploads/'.$post["image"].'">
                    <div class="texte"><p><strong>'.$post['auteur'].' :</strong> '.$post["texte"].'</p></div>';
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
        <div id="colonnes">
            <div class="colonne">
                <?php echo $leftPosts; ?>
            </div>
            <div class="colonne">
                <?php echo $rightPosts; ?>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.0.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>