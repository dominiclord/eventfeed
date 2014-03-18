<?php
    require_once dirname(__FILE__).'/../config.php';

    // Connecting to the database
    $db = db_connection();
    mysqli_set_charset($db,"utf8");

    // ASD
    $state = fct_POST('etat');
    if(is_array($state)){
        $data = $state;
        $state = "Modifier";
    }
    $timestamp = fct_POST('timestamp');
    switch($state){
        case "Approuver":
            $request = "UPDATE posts SET status = 'approuver' WHERE timestamp = ".$timestamp;
            mysqli_query($db,$request);
            mysqli_close($db);
            return true;
        break;
        case "Modifier":
            date_default_timezone_set('America/Montreal');
            $timestamp_modified = time();
            $request = 'UPDATE posts SET author = "'.$data['author'].'", text = "'.$data['text'].'", timestamp_modified = "'.$timestamp_modified.'" WHERE timestamp = '.$data['timestamp'];
            mysqli_query($db,$request);
            mysqli_close($db);
            return true;
        break;
        case "Charger":
            $request = "SELECT * FROM  posts WHERE timestamp = ".$timestamp;
            $statuts = mysqli_query($db,$request);
            mysqli_close($db);
            $aStatuts = array();
            while ($row = mysqli_fetch_assoc($statuts)){
                $aStatuts = $row;
            }
            header("Content-type: application/json");
            echo json_encode($aStatuts);
        break;
        case "Rejeter":
            date_default_timezone_set('America/Montreal');
            $timestamp_modified = time();
            $request = "UPDATE posts SET status = 'rejeter', timestamp_modified = '".$timestamp_modified."' WHERE timestamp = ".$timestamp;
            mysqli_query($db,$request);
            mysqli_close($db);
            return true;
        break;
    }