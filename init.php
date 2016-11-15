<?php
    require_once('config.php');
    if(!defined('DATABASE_CONNECTOR')){
        echo 'DATABASE_CONNECTOR must be defined in config.php';
        //TODO just fetch the list of folders under database/ and list that out
        echo 'Look at the list of folders in database for options';

        die();
    }
        //TODO  fetch the list of folders under database/ and verify that database_connector is on of those
    require_once('database/'.strtolower(DATABASE_CONNECTOR).'/database.php');
    $db = new Database();
