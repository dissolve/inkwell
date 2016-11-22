<?php
    require('init.php');

//get list of unverified subscriptions

$subscribers = $db->getUnverified();

foreach($subscribers as $subscriber){

    $challenge = 'TODO:MAKESOMETHINGRANDOM'; //TODO

    $feed = $db->getFeed($subscriber['feed_id']);

    //TODO If feed is new, verify that the URL they are subscribing to has this site set as its hub and it has rel=self :/
    //can possibly do this in the original subscription

    $callback_url = $subscriber['callback_url'] . (strpos($subscriber['callback_url'], '?') === false ? '?' : '&') . 
        'hub.mode=subscribe' .
        '&hub.topic=' . $feed['url'] . //todo, encode?
        '&hub.challenge=' . $challenge .
        '&hub.lease_seconds=' . $subscriber['lease_seconds'] 
        ;

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_URL, $callback_url );
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_NOBODY, true);

	$content = curl_exec ($ch);

	curl_close ($ch);

    if($content == $challenge){
        $db->setVerified($subscriber['subscription_id']);
    } else {
        $db->unsubscribe($subscriber['subscription_id']);
    }

}

$subscribers = $db->getUnsubscriptionRequests();

//get list of unverified unsubscribe requests
foreach($subscribers as $subscriber){

    $challenge = 'TODO:MAKESOMETHINGRANDOM'; //TODO

    $feed = $db->getFeed($subscriber['feed_id']);

    $callback_url = $subscriber['callback_url'] . (strpos($subscriber['callback_url'], '?') === false ? '?' : '&') . 
        'hub.mode=unsubscribe' .
        '&hub.topic=' . $feed['url'] . //todo, encode?
        '&hub.challenge=' . $challenge .
        '&hub.lease_seconds=' . $subscriber['lease_seconds'] 
        ;

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_URL, $callback_url );
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, true); 
	curl_setopt($ch, CURLOPT_NOBODY, true);

	$content = curl_exec ($ch);

	curl_close ($ch);

    if($content == $challenge){
        $db->unsubscribe($subscriber['subscription_id']);
    } else {
        $db->rejectUnsubscribe($subscriber['subscription_id']);
    }
}

// do push of data to subscribers from webmentions received


$webmentions = $db->getWebmentions();

foreach($webmentions as $webmentions){
    //lookup feed_id based on url
    $feed_id = $db->getFeedForURL($webmention['source']);

    //if we have any subscribers
    if($feed_id){
        //fetch data from page
        
        $success = fetchAndStore($webmention['source']);

        if($success){
            // push out to these subscribers
            notifySubscribers($feed_id);
        }

    }
    
}

