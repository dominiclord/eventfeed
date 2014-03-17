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
    $etat = fct_POST('etat');
    if(is_array($etat)){
        $donnees = $etat;
        $etat = "Modifier";
    }
    $timestamp = fct_POST('timestamp');
    switch($etat){
        case "Approuver":
            $bdLien = bd_connexion();
            mysqli_set_charset($bdLien,"utf8");
            $requete = "UPDATE posts SET statut = 'approuver' WHERE timestamp = ".$timestamp;
            mysqli_query($bdLien,$requete);
            mysqli_close($bdLien);
            return true;
        break;
        case "Modifier":
            date_default_timezone_set('America/Montreal');
            $timestamp_modifier = time();
            $bdLien = bd_connexion();
            mysqli_set_charset($bdLien,"utf8");
            $requete = 'UPDATE posts SET auteur = "'.$donnees['auteur'].'", texte = "'.$donnees['texte'].'", timestamp_modifier = "'.$timestamp_modifier.'" WHERE timestamp = '.$donnees['timestamp'];
            mysqli_query($bdLien,$requete);
            mysqli_close($bdLien);
            return true;
        break;
        case "Charger":
            $bdLien = bd_connexion();
            mysqli_set_charset($bdLien,"utf8");
            $requete = "SELECT * FROM  posts WHERE timestamp = ".$timestamp;
            $statuts = mysqli_query($bdLien,$requete);
            mysqli_close($bdLien);
            $aStatuts = array();
            while ($row = mysqli_fetch_assoc($statuts)){
                $aStatuts = $row;
            }
            header("Content-type: application/json");
            echo json_encode($aStatuts);
        break;
        case "Rejeter":
            date_default_timezone_set('America/Montreal');
            $timestamp_modifier = time();
            $bdLien = bd_connexion();
            mysqli_set_charset($bdLien,"utf8");
            $requete = "UPDATE posts SET statut = 'rejeter', timestamp_modifier = '".$timestamp_modifier."' WHERE timestamp = ".$timestamp;
            mysqli_query($bdLien,$requete);
            mysqli_close($bdLien);
            return true;
        break;
    }