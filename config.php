<?php
    //database connection information
    define('DB_HOSTNAME','');
    define('DB_PASSWORD','');
    define('DB_USERNAME','');
    define('DB_DATABASE','');

    // default amount of time in seconds before someone must re-subscribe
    // if not supplied, how long should subscriptions last?
    // must be a positive integer
    define('DEFAULT_LEASE_LENGTH',60*60*24*30);

    // max amount of time in seconds you will allow subscriptions requests to define for their own subscription
    // negative integer or 0 will ALWAYS use DEFAULT_LEASE_LENGTH
    // otherwise, must be a positive integer
    define('MAX_LEASE_LENGTH',60*60*24*365);

    define('DATABASE_CONNECTOR', 'mysqli');

