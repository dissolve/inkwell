<?php
require_once('init.php');


//must be a valid webmention
if( isset($_POST['source']) && !empty($_POST['source'])){
    && isset($_POST['target']) && !empty($_POST['target'])){

    $target_parts = parse_url($_POST['target']);

    //ensure the webmention is sent to the hub domain at least
    // we could do more checking if we really wanted, but this is a fine start
    if($target_parts['host'] == $_SERVER['HTTP_HOST'] ){

        $db->storeWebmention($_POST['source'], $_POST['target']);
         header('HTTP/1.1 202 Accepted');
         exit();


    } else {
        //target is not on this host
         header('HTTP/1.1 400 Bad Request');
         echo "The specified target is not on this host.";

    }

} else {
    //source or target are missing
     header('HTTP/1.1 400 Bad Request');
     echo "Webmention request must provide bost SOURCE and TARGET values.";
}
