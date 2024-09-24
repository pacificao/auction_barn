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

class DatabasePDO extends Database
{
    protected $fetch_methods = [
        'FETCH_ASSOC' => PDO::FETCH_ASSOC,
        'FETCH_BOTH' => PDO::FETCH_BOTH,
        'FETCH_NUM' => PDO::FETCH_NUM,
    ];

    public function connect($DbHost, $DbUser, $DbPassword, $DbDatabase, $DBPrefix, $CHARSET = 'UTF-8')
    {
        $this->DBPrefix = $DBPrefix;
        $this->CHARSET = $CHARSET;
        try {
            // MySQL with PDO_MYSQL
            $this->conn = new PDO("mysql:host=$DbHost;dbname=$DbDatabase;charset=$CHARSET", $DbUser, $DbPassword);
            // set error reporting up
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // actually use prepared statements
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return true;
        } catch (PDOException $e) {
            $this->error_handler($e->getMessage());
            return false;
        }
    }

    public function error_supress($state = true)
    {
        $this->error_supress = $state;
    }

    // to run a direct query with prepared statements for security
    public function direct_query($query, $params = array())
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $this->lastquery = $stmt;
        } catch (PDOException $e) {
            $this->error_handler($e->getMessage());
        }
    }

    public function lastInsertId()
    {
        try {
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->error_handler($e->getMessage());
        }
    }

    protected function clean_params($query, $params)
    {
        // find the vars set in the query
        preg_match_all("(:[a-zA-Z0-9_]+)", $query, $set_params);
        $new_params = array();
        foreach ($set_params[0] as $val) {
            $key = $this->find_key($params, $val);
            if (isset($key)) {
                $new_params[] = $params[$key];
            }
        }
        return $new_params;
    }

    protected function find_key($params, $val)
    {
        foreach ($params as $k => $v) {
            if ($v[0] == $val) {
                return $k;
            }
        }
    }

    protected function build_params($params)
    {
        $PDO_constants = array(
            'int' => PDO::PARAM_INT,
            'str' => PDO::PARAM_STR,
            'bool' => PDO::PARAM_INT, // work-around PHP bug
            'float' => PDO::PARAM_STR
            );
        // set PDO values to params
        for ($i = 0; $i < count($params); $i++) {
            // force float
            if ($params[$i][2] == 'float') {
                $params[$i][1] = floatval($params[$i][1]);
            }
            // fix PHP bug for boolean values
            if ($params[$i][2] == 'bool' && $params[$i][1] > 1) {
                $params[$i][1] = 1;
            }
            $params[$i][2] = $PDO_constants[$params[$i][2]];
        }
        return $params;
    }

    protected function error_handler($error)
    {
        trigger_error($error, E_USER_WARNING);
        if (!$this->error_supress) {
            debug_print_backtrace();
        }
    }

    // close everything down
    public function __destruct()
    {
        // close database connection
        $this->conn = null;
    }
}
?>
