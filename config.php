<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

// Acquire POST data
function fct_POST($name, $default=''){
    if (isset($_POST[$name])==true) return($_POST[$name]);
    return($default);
}
// Acquire GET data
function fct_GET($name, $default=''){
    if (isset($_GET[$name])==true) return($_GET[$name]);
    return($default);
}

function connect_db() {
    $server     = 'localhost';
    $user       = 'root';
    $pass       = 'root';
    $database   = 'eventfeed_local';
    $connection = new mysqli($server, $user, $pass, $database);

    return $connection;
}