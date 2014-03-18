<?php
    require_once dirname(__FILE__).'/config.php';

    // ASD
    if(fct_POST('timestamp')==""){
        date_default_timezone_set('America/Montreal');
        $timestamp = time();
    }else{
        $timestamp = fct_POST('timestamp');
    }
    $state = fct_POST('state');
    $mode = fct_POST('mode');
    $author = fct_POST('author');
    $text = fct_POST('text');
    $image = fct_POST('image');
    $type = "";
    $file = "";
    if($image==""){
        $image = false;
    }else{
        $file = $image;
        $image = true;
    }
    $error = false;
    if($state == "Send"){
        if(isset($_FILES['files']['name']) && !empty($_FILES['files']['name'])){
            //$folder = 'D:\xampp\htdocs\technoblog\utilisateur\uploads\\';
            //$folder = 'F:\Sites\technoblog\utilisateur\uploads\\';
            $folder = '/home/domkev/webapps/technosoiree/utilisateur/uploads//';
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
        //On set la variable de quel type de post que c'est
        if($image==true && $text!=""){
            $type = "hybrid";
        }else{
            if($image==true && $text==""){
                $type = "image";
            }else if($image==false && $text!=""){
                $type = "text";
            }
        }
        if($author == "" || $type == ""){
            $error = true;
        }else{
            // Connecting to the database
            $db = db_connection();
            mysqli_set_charset($db,"utf8");

            // Parse the data
            $author = htmlspecialchars($author);
            $text = htmlspecialchars($text);
            $author = mysqli_real_escape_string($db,$author);
            $text = mysqli_real_escape_string($db,$text);
            $request = "INSERT INTO posts (timestamp, author, text, image, status, type) VALUES ('".$timestamp."', '".$author."', '".$text."', '".$file."', 'moderation', '".$type."');";
            echo $request;
            mysqli_query($db,$request);
            mysqli_close($db);
            //header('Location: index.php?succes');
        }
    }
?>
<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
    <head>
        <meta charset='utf-8'/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Soumettre un message - Technosoirée</title>
        <link rel="stylesheet" href="utilisateur/styles.css" media="all">
    </head>
    <body>
        <div id="overlay"></div>
        <h1>Technosoirée</h1>
        <form action="soumettre.php" enctype="multipart/form-data" id="formEntry" method="post">
            <ul>
                <li>
                    <label for="author">Votre nom (obligatoire) :</label>
                    <input <?php if($error==true && $author == ""){echo "class='error'";} ?> id="author" type="text" name="author" <?php if($error==true){echo "value='".$author."'";} ?> >
                </li>
                <li>
                    <label for="text">Votre message :</label>
                    <input <?php if($error==true && $text == ""){echo "class='error'";} ?> id="text" type="text" name="text" <?php if($error==true){echo "value='".$text."'";} ?> >
                </li>
                <li>
                    <label for="imagefile">Image :</label>
                    <input id="imagefile" type="file" accept="image/*" name="imagefile">
                </li>
            </ul>
            <p <?php if($error!=true && $type != ""){echo "style='display:none;'";} ?> id="nocontent"><strong>Il vous faut envoyer au minimum un message ou une image.</strong></p>
            <p>Vous pouvez envoyer un message, une image ou les deux en même temps.</p>
            <p>Les contenus jugés innapproprié ne seront pas publiés.</p>
            <input id="btnCancel" type="reset" value="Effacer">
            <input id="btnSubmit" name="btnSubmit" type="submit" value="Send">
            <input id="state" name="state" type="hidden" value="Send">
            <input id="timestamp" name="timestamp" type="hidden" value="">
            <input id="image" name="image" type="hidden" value="">
        </form>
        <script src="utilisateur/jquery-1.8.0.min.js"></script>
        <script src="utilisateur/scripts.js"></script>
    </body>
</html>