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
    function db_connection(){
        // Database configuration
        $_ef_conf['database']['hostname'] = 'localhost';
        $_ef_conf['database']['username'] = 'root';
        $_ef_conf['database']['password'] = 'root';
        $_ef_conf['database']['database'] = 'ef_local';

        $dbLink = mysqli_connect($_ef_conf['database']['hostname'],$_ef_conf['database']['username'],$_ef_conf['database']['password'],$_ef_conf['database']['database']);
        if (mysqli_connect_errno()){
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        return($dbLink);
    }