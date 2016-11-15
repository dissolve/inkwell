<?php
require_once('../init.php');


//must be a valid webmention
if( isset($_POST['source']) && !empty($_POST['source'])
    && isset($_POST['target']) && !empty($_POST['target'])){

    $target_parts = parse_url($_POST['target']);

    //ensure the webmention is sent to the hub url
    if($target_parts['host'] == $_SERVER['HTTP_HOST'] 
        && $target_parts['path'] == '/subscriptions.php'){

        //lookup feed_id based on url
        $feed_id = $db->getFeedForURL($_POST['source']);

        //if we have any subscribers
        if($feed_id){
            //fetch data from page
            
            $success = fetchAndStore($_POST['source']);

            if($success){
                // push out to these subscribers
                notifySubscribers($feed_id);
            }

        }

    }

}
