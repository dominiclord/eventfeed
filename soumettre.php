<?php
    // fonction pour les requetes de données en POST
    function fct_POST($nom, $defaut=''){
        if (isset($_POST[$nom])==true) return($_POST[$nom]);
        return($defaut);
    }
    // fonction de connexion à la BDD
    function bd_connexion(){
        $bdLier = mysqli_connect('localhost','root','root','ef_local');
        if (mysqli_connect_errno()){
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        return($bdLier);
    }
    if(fct_POST('timestamp')==""){
        date_default_timezone_set('America/Montreal');
        $timestamp = time();
    }else{
        $timestamp = fct_POST('timestamp');
    }
    $etat = fct_POST('etat');
    $mode = fct_POST('mode');
    $auteur = fct_POST('auteur');
    $texte = fct_POST('texte');
    $image = fct_POST('image');
    $type = "";
    $fichier = "";
    if($image==""){
        $image = false;
    }else{
        $fichier = $image;
        $image = true;
    }
    $erreur = false;
    if($etat == "Envoyer"){
        if(isset($_FILES['files']['name']) && !empty($_FILES['files']['name'])){
            //$dossier = 'D:\xampp\htdocs\technoblog\utilisateur\uploads\\';
            //$dossier = 'F:\Sites\technoblog\utilisateur\uploads\\';
            $dossier = '/home/domkev/webapps/technosoiree/utilisateur/uploads//';
            $taille = filesize($_FILES['files']['tmp_name']);
            $extensions = array('.png','.jpg','.jpeg','.PNG','.JPG','.JPEG');
            $extension = strrchr($_FILES['files']['name'],'.');
            if(!in_array($extension, $extensions)){
                echo 'ERROR you must upload the right type';
            }else{
                $fichier = $timestamp.$extension;
                if(move_uploaded_file($_FILES['files']['tmp_name'],$dossier.$fichier)){
                    if($extension!='.png'&&$extension!='.PNG'){
                        chmod($dossier.$fichier, 0755);
                        $exif = exif_read_data($dossier.$fichier);
                        if(isset($exif['Orientation'])){
                            $ort = $exif['Orientation'];
                            switch($ort){
                                case 1:// normal
                                    $source = imagecreatefromjpeg($dossier.$fichier);
                                    imagejpeg($source,$dossier.$fichier,50);
                                break;
                                case 3:// 180 rotate left
                                    $source = imagecreatefromjpeg($dossier.$fichier);
                                    $rotate = imagerotate($source, 180, -1);
                                    imagejpeg($rotate,$dossier.$fichier,50);
                                break;
                                case 6:// 90 rotate right
                                    $source = imagecreatefromjpeg($dossier.$fichier);
                                    $rotate = imagerotate($source, -90, -1);
                                    imagejpeg($rotate,$dossier.$fichier,50);
                                break;
                                case 8:// 90 rotate left
                                    $source = imagecreatefromjpeg($dossier.$fichier);
                                    $rotate = imagerotate($source, 90, -1);
                                    imagejpeg($rotate,$dossier.$fichier,50);
                                break;
                            }
                        }
                    }
                    $image = true;
                }
            }
        }
        //On set la variable de quel type de post que c'est
        if($image==true && $texte!=""){
            $type = "hybride";
        }else{
            if($image==true && $texte==""){
                $type = "image";
            }else if($image==false && $texte!=""){
                $type = "texte";
            }
        }
        if($auteur == "" || $type == ""){
            $erreur = true;
        }else{
            $bdLien = bd_connexion();
            mysqli_set_charset($bdLien,"utf8");
            $auteur = htmlspecialchars($auteur);
            $texte = htmlspecialchars($texte);
            $auteur = mysqli_real_escape_string($bdLien,$auteur);
            $texte = mysqli_real_escape_string($bdLien,$texte);
            $requete = "INSERT INTO posts (timestamp, auteur, texte, image, statut, type) VALUES ('".$timestamp."', '".$auteur."', '".$texte."', '".$fichier."', 'moderation', '".$type."');";
            mysqli_query($bdLien,$requete);
            mysqli_close($bdLien);
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
                    <label for="auteur">Votre nom (obligatoire) :</label>
                    <input <?php if($erreur==true && $auteur == ""){echo "class='error'";} ?> id="auteur" type="text" name="auteur" <?php if($erreur==true){echo "value='".$auteur."'";} ?> >
                </li>
                <li>
                    <label for="texte">Votre message :</label>
                    <input <?php if($erreur==true && $texte == ""){echo "class='error'";} ?> id="texte" type="text" name="texte" <?php if($erreur==true){echo "value='".$texte."'";} ?> >
                </li>
                <li>
                    <label for="imagefile">Image :</label>
                    <input id="imagefile" type="file" accept="image/*" name="imagefile">
                </li>
            </ul>
            <p <?php if($erreur!=true && $type != ""){echo "style='display:none;'";} ?> id="nocontent"><strong>Il vous faut envoyer au minimum un message ou une image.</strong></p>
            <p>Vous pouvez envoyer un message, une image ou les deux en même temps.</p>
            <p>Les contenus jugés innapproprié ne seront pas publiés.</p>
            <input id="btnCancel" type="reset" value="Effacer">
            <input id="btnSubmit" name="btnSubmit" type="submit" value="Envoyer">
            <input id="etat" name="etat" type="hidden" value="Envoyer">
            <input id="timestamp" name="timestamp" type="hidden" value="">
            <input id="image" name="image" type="hidden" value="">
        </form>
        <script src="utilisateur/jquery-1.8.0.min.js"></script>
        <script src="utilisateur/scripts.js"></script>
    </body>
</html>