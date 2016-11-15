<?php
require_once('driver.php');
final class Database {
    private $link;

    public function __construct()
    {
        $this->link = new DBMySQLi(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    }

    public function addSubscriber($feed_id, $callback_url, $lease_seconds = DEFAULT_LEASE_LENGTH, $secret = null){
        $this->link->query(
            " INSERT INTO " . DB_DATABASE . ".subscribers " .
            " SET feed_id=".(int)$feed_id .
            " , callback_url='" .$this->link->escape($callback_url). "'" .
            " , expiration=FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) + ".(int)$lease_seconds . ")" .
            (!empty($secret) ? " , secret='" .$this->link->escape($secret). "'" : ""  ) .
            " , is_verified=0" .
            " , fail_count=0" 
        );
        return $this->link->getLastId();
    }


    /*
     * topic_url: the url for the feed
     * return feed object
     */
    public function getOrCreateFeed($topic_url){

        if($feed_id = $this->getFeedForUrl($topic_url)){
            return $feed_id;
        }

        //todo validate topic_url contains self link 
        //  also store the current data of the page so we have initial data
        
        $this->link->query(
            " INSERT INTO " . DB_DATABASE . ".feeds " .
            " SET url='".$this->link->escape($topic_url)."'"
        );

        return $this->link->getLastId();

        //TODO: add field to know if we are being notified of the subscription or not (pull / push)

    }
    public function getFeedForUrl($topic_url){
        $feed_result = $this->link->query(
            " SELECT * " .
            " FROM " . DB_DATABASE . ".feeds " .
            " WHERE url='".$this->link->escape($topic_url)."';"
        );
        if($feed_result->num_rows > 0){
            return $feed_result->row['feed_id'];
        }
        return null;
    }
    // probably not needed
    /*
    public function getSubscribersByUrl($topic_url){
        $feed_id = $this->getFeedForURL($topic_url);
        return $this->getSubscribers($feed_id);
    }
     */

    public function getSubscribers($feed_id){
        $result = $this->link->query(
            " SELECT * " .
            " FROM " . DB_DATABASE . ".subscribers " .
            " WHERE feed_id=".(int)$feed_id.";"
        );
        return $result->rows;

    }

    public function getSubscriber($topic_url, $callback_url){
        if($feed_id = $this->getFeedForUrl($topic_url)){

            //TODO make sure there can be only one feed_id / callback_url combo
            $result = $this->link->query(
                " SELECT * " .
                " FROM " . DB_DATABASE . ".subscribers " .
                " WHERE feed_id=".(int)$feed_id .
                " AND callback_url='" .$this->link->escape($callback_url). "'" .
                " LIMIT 1"
            );
            if($result->num_rows > 0){
                return $result->row['subscriber_id']; 
            } else {
                //subscriber not found
                return null;
            }
            
        } else {
            //feed not found
            return null;
        }
    }

    //this will only update the DB
    // the cron script will have to take care of verifying the intention to unsubscribe
    public function requestUnsubscribe($subscriber_id){
        $this->link->query(
            " UPDATE " . DB_DATABASE . ".subscribers " .
            " SET unsubscribe_requested=1 " .
            " WHERE subscriber_id = " . (int)$subscriber_id
        );
    }

    //the actual unsubscribe function
    // as a side-effect, if the feed is no longer needed, it will alsobe deleted
    public function unsubscribe($subscriber_id){
        $query = $this->link->query(
            " SELECT feed_id " .
            " FROM  " . DB_DATABASE . ".subscribers " .
            " WHERE subscriber_id = " . (int)$subscriber_id
        );
        $feed_id = $query->row['feed_id'];


        $this->link->query(
            " DELETE FROM " . DB_DATABASE . ".subscribers " .
            " WHERE subscriber_id = " . (int)$subscriber_id .
            " LIMIT 1"
        );

        $query = $this->link->query(
            " SELECT feed_id " .
            " FROM  " . DB_DATABASE . ".subscribers " .
            " WHERE feed_id = " . (int)$feed_id
        );

        if($query->num_rows == 0){
            $this->link->query(
                " DELETE FROM " . DB_DATABASE . ".feeds " .
                " WHERE feed_id = " . (int)$feed_id .
                " LIMIT 1"
            );
        }

    }


    public function setVerified($subscription_id){
        $this->link->query(
            " UPDATE " . DB_DATABASE . ".subscribers " .
            " SET is_verified=1 " .
            " WHERE subscriber_id = " . (int)$subscriber_id
        );

    }

    public function getUnverified(){
        $feed_result = $this->link->query(
            " SELECT * " .
            " FROM " . DB_DATABASE . ".subscriptions " .
            " WHERE is_verified=0"
        );

        return $feed_result->rows;

    }

    public function getUnsubscriptionRequests(){
        $feed_result = $this->link->query(
            " SELECT * " .
            " FROM " . DB_DATABASE . ".subscriptions " .
            " WHERE unsubscribe_requested=0"
        );

        return $feed_result->rows;


    }


}
