<?php
    require_once dirname(__FILE__).'/../config.php';

    // ASD
    $actionLinks = "<aside><a href='#' class='edit'>Modifier</a>&nbsp;<a href='#' class='approve'>Approuver</a>&nbsp;<a href='#' class='remove'>Rejeter</a></aside>";
    $affichage = $_SERVER['QUERY_STRING'];
    $state = fct_POST('state');
    if($state == "Send"){
        $speed = fct_POST('speed');
        // Connecting to the database
        $db = db_connection();
        mysqli_set_charset($db,"utf8");
        $request = "UPDATE parametres SET vitesse = '".$speed."' WHERE id = 1;";
        mysqli_query($db,$request);
        mysqli_close($db);
    }
    if($affichage=="moderation"||$affichage=="");
?>

<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
    <head>
        <meta charset='utf-8'/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Modération</title>
        <link rel="stylesheet" href="styles.css" media="all">
    </head>
    <body>
        <nav>
            <ul>
                <li><a <?php echo ($affichage=='moderation'||$affichage=='' ? 'class="active"' : ""); ?> href="./?moderation">À Modérer</a></li>
                <li><a <?php echo ($affichage=='approuver' ? 'class="active"' : ""); ?> href="./?approuver">Approuvés</a></li>
                <li><a <?php echo ($affichage=='publier' ? 'class="active"' : ""); ?> href="./?publier">Publiés</a></li>
                <li><a <?php echo ($affichage=='rejeter' ? 'class="active"' : ""); ?> href="./?rejeter">Rejetés</a></li>
                <li><a <?php echo ($affichage=='parametres' ? 'class="active"' : ""); ?> href="./?parametres">Paramètres</a></li>
            </ul>
        </nav>
        <h1>Modération</h1>
<?php
    // Connecting to the database
    $db = db_connection();
    mysqli_set_charset($db,"utf8");
    $request = "";
    switch($affichage){
        case "moderation":
            $request = "SELECT * FROM posts WHERE status = 'moderation' ORDER BY timestamp ASC";
        break;
        case "approuver":
            $request = "SELECT * FROM posts WHERE status = 'approuver' ORDER BY timestamp ASC";
        break;
        case "publier":
            $request = "SELECT * FROM posts WHERE status = 'publier' ORDER BY timestamp_modified DESC";
        break;
        case "rejeter":
            $request = "SELECT * FROM posts WHERE status = 'rejeter' ORDER BY timestamp DESC";
        break;
        case "parametres":
            $request = "SELECT * FROM parametres";
        break;
        default:
            $request = "SELECT * FROM posts WHERE status = 'moderation' ORDER BY timestamp ASC";
        break;
    }
    $chaine = "";
    if($affichage != "parametres"){
        $statuts = mysqli_query($db,$request);
        mysqli_close($db);
        $chaine .= "
        <section id='entries'>";
        while ($row = mysqli_fetch_assoc($statuts)){
            $temptexte=$row["text"];
            $tempimage=$row["image"];
            $chaine.='
            <article rel="'.$row['timestamp'].'">
                '.$actionLinks.'
                <h2><span class="sAuteur">'.$row['author'].'</span> - <span class="sType">'.$row['type'].'</span></h2>';
            switch($row['type']){
                case "text":
                    $chaine.='
                <p class="sTexte">'.$temptexte.'</p>';
                break;
                case "hybrid":
                    $chaine.='
                <p><span id="sTexte">'.$temptexte.'</span><br><img class="sImage" src="../utilisateur/uploads/'.$tempimage.'"></p>';

                    list($width, $height) = getimagesize('../utilisateur/uploads/'.$tempimage);

                    $chaine.='
                <p>W : '.$width.' -- H : '.$height.'</p>';
                break;
                case "image":
                    $chaine.='
                <p><img class="sImage" src="../utilisateur/uploads/'.$tempimage.'""></p>';

                    list($width, $height) = getimagesize('../utilisateur/uploads/'.$tempimage);

                    $chaine.='
                <p>W : '.$width.' -- H : '.$height.'</p>';
                break;
            }
            $chaine.='
            </article>';
        }
        $chaine.='
        </section>';
        echo $chaine;
?>
        <form id="formEntry">
            <ul>
                <li>
                    <label for="auteur">Auteur</label>
                    <input id="auteur" type="text" name="auteur" value="" />
                </li>
                <li>
                    <label for="texte">Texte</label>
                    <input id="texte" type="text" name="texte" value="">
                </li>
            </ul>
            <input id="btnCancel" type="button" value="Cancel" />
            <input id="btnSubmit" type="submit" value="Save" />
            <input id="formMode" type="hidden" value="" />
        </form>
<?php
    }else{
        $params = mysqli_query($db,$request);
        mysqli_close($db);
        $aParams = array();
        while ($row = mysqli_fetch_assoc($params)){
            $aParams = $row;
        }
        $vitesse = $aParams['vitesse'];
?>
        <form method="post" action="?parametres" id="formParams">
            <ul>
                <li>
                    <label for="vitesse">Vitesse</label>
                    <input id="vitesse" type="tel" name="vitesse" <?php echo 'value="'.$vitesse.'"'; ?> />
                    <input id="etat" name="etat" type="hidden" value="Envoyer">
                </li>
            </ul>
            <input id="btnSubmit" type="submit" value="Save" />
        </form>
<?php
    }
?>
        <script src="jquery-1.8.0.min.js"></script>
        <script src="scripts.js"></script>
    </body>
</html>