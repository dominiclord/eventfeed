<?php
    function bd_connexion(){
        //$bdLier = mysqli_connect('localhost','root','','bd_technosoiree');
        $bdLier = mysqli_connect('localhost','domkev_techno','de2ac8f6','domkev_techno');
        if (mysqli_connect_errno()){
            printf('Connect failed: %s\n', mysqli_connect_error());
            exit();
        }
        return($bdLier);
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Technosoirée</title>
        <meta name="viewport" content="width=device-width">
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main-alt.css">
        <script src="js/vendor/modernizr-2.6.1.min.js"></script>
    </head>
    <body>

<?php
    $bdLien = bd_connexion();
    mysqli_set_charset($bdLien,'utf8');
    $requete = "SELECT * FROM posts ORDER BY timestamp ASC";
    $statuts = mysqli_query($bdLien,$requete);
    mysqli_close($bdLien);
    $chaine='';
    while ($row = mysqli_fetch_assoc($statuts)){
        $chaine.='
            <div data-timestamp="'.$row['timestamp'].'" class="post '.$row['statut'].' ';
        switch($row['type']){
            case 'texte':
                $chaine.='texte"><p><strong>'.$row['auteur'].' :</strong> '.$row["texte"].'</p>';
            break;
            case 'hybride':
                $chaine.='hybride">
                    <img src="../utilisateur/uploads/'.$row["image"].'">
                    <div class="texte"><p><strong>'.$row['auteur'].' :</strong> '.$row["texte"].'</p></div>';
            break;
            case 'image':
                $chaine.='hybride">
                    <img src="../utilisateur/uploads/'.$row["image"].'">
                    <div class="texte"><p><strong>'.$row['auteur'].' :</strong> image only</p></div>';
            break;
        }
        $chaine.='
            </div>';
    }
?>

        <section>
<?php
    echo $chaine;
?>
        </section>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.0.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>