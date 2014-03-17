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
    $actionLinks = "<aside><a href='#' class='edit'>Modifier</a>&nbsp;<a href='#' class='approve'>Approuver</a>&nbsp;<a href='#' class='remove'>Rejeter</a></aside>";
    $affichage = $_SERVER['QUERY_STRING'];
    $etat = fct_POST('etat');
    if($etat == "Envoyer"){
        $vitesse = fct_POST('vitesse');
        $bdLien = bd_connexion();
        mysqli_set_charset($bdLien,"utf8");
        $requete = "UPDATE parametres SET vitesse = '".$vitesse."' WHERE id = 1;";
        mysqli_query($bdLien,$requete);
        mysqli_close($bdLien);
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
    $bdLien = bd_connexion();
    mysqli_set_charset($bdLien,"utf8");
    $requete = "";
    switch($affichage){
        case "moderation":
            $requete = "SELECT * FROM posts WHERE statut = 'moderation' ORDER BY timestamp ASC";
        break;
        case "approuver":
            $requete = "SELECT * FROM posts WHERE statut = 'approuver' ORDER BY timestamp ASC";
        break;
        case "publier":
            $requete = "SELECT * FROM posts WHERE statut = 'publier' ORDER BY timestamp_modifier DESC";
        break;
        case "rejeter":
            $requete = "SELECT * FROM posts WHERE statut = 'rejeter' ORDER BY timestamp DESC";
        break;
        case "parametres":
            $requete = "SELECT * FROM parametres";
        break;
        default:
            $requete = "SELECT * FROM posts WHERE statut = 'moderation' ORDER BY timestamp ASC";
        break;
    }
    $chaine = "";
    if($affichage != "parametres"){
        $statuts = mysqli_query($bdLien,$requete);
        mysqli_close($bdLien);
        $chaine .= "
        <section id='entries'>";
        while ($row = mysqli_fetch_assoc($statuts)){
            $temptexte=$row["texte"];
            $tempimage=$row["image"];
            $chaine.='
            <article rel="'.$row['timestamp'].'">
                '.$actionLinks.'
                <h2><span class="sAuteur">'.$row['auteur'].'</span> - <span class="sType">'.$row['type'].'</span></h2>';
            switch($row['type']){
                case "texte":
                    $chaine.='
                <p class="sTexte">'.$temptexte.'</p>';
                break;
                case "hybride":
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
        $params = mysqli_query($bdLien,$requete);
        mysqli_close($bdLien);
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