<?php

require_once 'BucketDB.php';

class BucketDb_MySQL extends BucketDB
{
    function __construct($aConf)
    {
        $aConf['engine'] = empty($aConf['engine']) ? 'MyISAM' : $aConf['engine'];
        parent::__construct($aConf);

        $this->hasTransactions  = strtolower($this->aConf['engine']) == 'innodb';
    }

    function connect()
    {
        $host = $this->aConf['host'].':'.(empty($this->aConf['port']) ? 3306 : $this->aConf['port']);
        $this->db = mysql_connect($host, $this->aConf['user'], $this->aConf['password'], true);
        return @mysql_select_db($this->aConf['dbname'], $this->db);
    }

    function disconnect()
    {
        if (!empty($this->db)) {
            mysql_close($this->db);
            $this->db = null;
        }
    }

    function query($sql)
    {
        if (!empty($this->db) || $this->connect()) {
            return @mysql_query($sql, $this->db);
        }
        return false;
    }

    function error()
    {
        return mysql_error($this->db);
    }

    function fetch($result)
    {
        return mysql_fetch_assoc($result);
    }

    function free($result)
    {
        return mysql_free_result($result);
    }

    function affectedRows($result)
    {
        return mysql_affected_rows($this->db);
    }

    /**
     * It is possible to pass additional configuration options to test following table with different
     * indexes.
     *
     * 1) To use a different index in primary key use option: pkIndexType
     *   for example: 'pkIndexType' => 'USING BTREE'
     * 2) To add additional indexes use option: additionalIndexes
     *   for example: 'additionalIndexes' => ', INDEX USING BTREE (date_time)'
     *   In this case it is necessary to add any required commas so end SQL
     *   will be well fomatted.
     *
     */
    function updateCreate()
    {
        $pkIndexType = isset($this->aConf['pkIndexType']) ? $this->aConf['pkIndexType'] : '';
        $additionalIndexes = isset($this->aConf['additionalIndexes']) ? $this->aConf['additionalIndexes'] : '';
        $sql = "
            CREATE TABLE {$this->tableName} (
                date_time datetime NOT NULL,
                creative_id int NOT NULL,
                zone_id int NOT NULL,
                counter int NOT NULL DEFAULT 1,
                PRIMARY KEY $pkIndexType (date_time, creative_id, zone_id)
                $additionalIndexes
            ) ENGINE={$this->aConf['engine']}
        ";
        $this->query($sql) or die($this->error());
    }
}

?>