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



//given a feed_id, notify all subscribers of the update
function notifySubscribers($feed_id)
{
    $subscribers = $db->getSubscribers($feed_id);

    $feed_content = $db->getFeedContent($feed_id);
	$body = $feed_content['content'];
	$content_type = $feed_content['content-type'];

    foreach($subscribers as $subscriber){

        if($subscriber['secret']){
            //todo generate signature

            //X_Hub_Signature = 
            //method =
        }

        
        //post to  $subscriber's callback_url

        $ch = curl_init( $subscriber['callback_url']);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . $content_type,
            'Content-Length: ' . strlen($body))
        );

        $result = curl_exec($ch); 
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



	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_NOBODY, true);

	$content = curl_exec ($ch);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    //TODO replace $url with rel=self
    
	$feed_id = $db->getFeedForURL($url);

    if(!$feed_id){
        //todo create feed
    }

	curl_close ($ch);


    //TODO add some method to prevent flooding a URL
    //TODO check that target url actually uses this as a hub
    $db->setContent($feed_id, $content, $content_type);

}


