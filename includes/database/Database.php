<?php
/***************************************************************************
 *   copyright              : (C) 2008 - 2017 WeBid
 *   site                   : http://www.webidsupport.com/
 *   Barnealogy             : (C) 2024 Barnealogy, a division of Pacific Animal & Outdoor
 *   site                   : https://barnealogy.com
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

if (!defined('InWeBid')) {
    exit('Access denied');
}

abstract class Database
{
    // Database connection handle
    protected $conn;
    protected $CHARSET;
    protected $lastquery;
    protected $fetchquery;
    protected $error;
    protected $fetch_methods = [];
    
    public $DBPrefix;

    // Constructor: sets error suppression state
    public function __construct()
    {
        $this->error_supress = !(defined('WeBidDebug') && WeBidDebug);
    }

    /**
     * Establishes a database connection using MySQLi or PDO
     */
    abstract public function connect($DbHost, $DbUser, $DbPassword, $DbDatabase, $DBPrefix, $CHARSET = 'UTF-8');

    /**
     * Executes a direct query (use prepared statements when possible for security)
     */
    abstract public function direct_query($query);

    /**
     * Executes a parameterized query using prepared statements
     */
    abstract public function query($query, $params = array());

    /**
     * Fetches a single result row
     */
    abstract public function fetch($result = null, $method = 'FETCH_ASSOC');

    /**
     * Fetches all rows from a result set
     */
    abstract public function fetchall($result = null, $method = 'FETCH_ASSOC');

    /**
     * Returns a specific column value from the current result set
     */
    abstract public function result($column = null, $result = null, $method = 'FETCH_ASSOC');

    /**
     * Returns the number of rows in a result set
     */
    abstract public function numrows($result = null);

    /**
     * Retrieves the last inserted ID in the database
     */
    abstract public function lastInsertId();

    /**
     * Cleans parameters for queries
     */
    abstract protected function clean_params($query, $params);

    /**
     * Finds the key in the parameter array
     */
    abstract protected function find_key($params, $val);

    /**
     * Builds the final parameters for a query
     */
    abstract protected function build_params($params);

    /**
     * Handles any database errors, optionally logs them
     */
    abstract protected function error_handler($error);
}

?>
