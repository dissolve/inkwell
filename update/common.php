<?php
require_once('../init.php');


//given a feed_id, notify all subscribers of the update
function notifySubscribers($feed_id)
{
    $subscribers = $db->getSubscribers($feed_id);

    foreach($subscribers as $subscriber){

        //TODO 
        $body = '';


        if($subscriber['secret']){
            //todo generate signature

            //X_Hub_Signature = 
            //method =
        }

        
        //TODO post to  $subscriber['callback_url']
        //
        //TODO if response is not 2xx
            // update failcount
            //

        //TODO add to cron script retry of failed notifications

    }

}
//fetches the given URL then stores it

// returns true if updated
// returns false if error, if the data has not changed, or if we are pausing pulls on this url
function fetchAndStore($url){

    //TODO add some method to prevent flooding a URL
    //TODO check that target url actually uses this as a hub

}


