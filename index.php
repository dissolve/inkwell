<?php
    require('init.php');

    if(    !isset($_POST['hub.mode'])
        || !isset($_POST['hub.callback'])
        || !isset($_POST['hub.topic'])
    ) {        
         header('HTTP/1.1 400 Invalid Request');
         echo 'Request MUST contain hub.mode, hub.callback, and hub.topic values';
         exit();
    }

    $callback_url = $_POST['hub.callback'];
    $feed_url = $_POST['hub.topic'];

    if(strtolower($_POST['hub.mode']) == 'subscribe'){

        $lease_seconds = DEFAULT_LEASE_LENGTH;

        if( isset($_POST['hub.lease_seconds'])
            && (int)$_POST['hub.lease_seconds'] > 0
            && (int)$_POST['hub.lease_seconds'] <= MAX_LEASE_LENGTH 
        ){
            $lease_seconds = (int)$_POST['hub.lease_seconds'];
        }

        $secret = null;
        if( isset($_POST['hub.secret']) ){
            //TODO make sure its less that 200 bytes
            $secret = (int)$_POST['hub.secret'];
        }

        $feed_id = $db->getFeedForUrl($feed_url);
        if(!$feed_id){
        //TODO we check for its rel=self link and that it points to our hub
            //if so, we create the feed in the db
        }

        //store to DB 
        $subscriber_id = $db->createSubscriber($feed_id, $callback_url, $lease_seconds, $secret);

         header('HTTP/1.1 202 Accepted');
         exit();

    } elseif(strtolower($_POST['hub.mode']) == 'unsubscribe'){

        //unsubscribe
        $subscriber_id = $db->getSubscriber($feed_url, $callback_url);
        $db->requestUnsubscribe($subscriber_id);


    } else { //hub.mode is an unknown value
?>
<html>
<head>
    <link rel="webmention" href="<?php echo $BASE_URL_PATH?>/webmention.php" />
</head>
<body>
    Welcome to InkWell. 
    Documentation forthcoming.
    If you were trying to subscribe or unsubscribe to a feed, then you are required to include hub.mode, hub.callback, and hub.topic
</body>
</html>

<?php
         exit();
    }

