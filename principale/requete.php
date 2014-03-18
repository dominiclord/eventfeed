<?php
    require_once dirname(__FILE__).'/../config.php';

    // Connecting to the database
    $db = db_connection();
    mysqli_set_charset($db,"utf8");

    // asd
    $etat = fct_POST('etat');
    if(is_array($etat)){
        $donnees = $etat;
        $etat = "Modifier";
    }
    $timestamp = fct_POST('timestamp');

    switch($etat){
        case "Publier":
            date_default_timezone_set('America/Montreal');
            $timestamp_modified = time();
            $request = "UPDATE posts SET status = 'publier', timestamp_modified = '".$timestamp_modified."' WHERE timestamp = ".$timestamp;
            mysqli_query($db,$request);
            mysqli_close($db);
            return true;
        break;
        case "Charger":
            $db = db_connection();
            mysqli_set_charset($db,"utf8");
            $request = "SELECT * FROM posts WHERE status = 'approuver' ORDER BY timestamp_modified ASC";
            $statuts = mysqli_query($db,$request);
            mysqli_close($db);
            $aStatuts = array();
            while ($row = mysqli_fetch_assoc($statuts)){
                $aStatuts[] = $row;
            }
            // Supply header for JSON mime type
            header("Content-type: application/json");
            echo json_encode($aStatuts);
        break;
        case "Parametres":
            $request = "SELECT * FROM parametres";
            $params = mysqli_query($db,$request);
            mysqli_close($db);
            $aParams = array();
            while ($row = mysqli_fetch_assoc($params)){
                $aParams = $row;
            }
            // Supply header for JSON mime type
            header("Content-type: application/json");
            echo json_encode($aParams);
        break;
    }