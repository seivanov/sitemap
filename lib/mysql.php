<?php

mysqli_report(MYSQLI_REPORT_STRICT);

/**
 * Class Mysql
 */
class Mysql {

    /**
     * @var null
     */
    private static $instance = null;
    /**
     * @var mysqli
     */
    private $link;

    /**
     * @var
     */
    private $bdname;
    /**
     * @var
     */
    private $host;
    /**
     * @var
     */
    private $username;
    /**
     * @var
     */
    private $passwd;

    /**
     * @param array $config
     */
    private function exam_config($config = []) {

        if(isset($config['db'])
            && is_array($config['db'])) {

            $cbd = &$config['db'];

            $this->host = (isset($cbd['host']))
                ? $cbd['host'] : NULL;
            $this->username = (isset($cbd['username']))
                ? $cbd['username'] : NULL;
            $this->passwd = (isset($cbd['password']))
                ? $cbd['password'] : NULL;
            $this->bdname = (isset($cbd['bdname']))
                ? $cbd['bdname'] : NULL;

        }

    }

    /**
     * @param $config
     * @throws Exception
     */
    private function __construct($config) {

        $this->exam_config($config);

        try {
            $this->link = @mysqli_connect($this->host,
                $this->username, $this->passwd, $this->bdname);
        } catch(mysqli_sql_exception $e) {

            echo $e->getMessage() . "\n";
            throw new Exception('Error connect to database');

        }

    }

    /**
     * @return Mysql|null
     */
    public static function getInstance() {

        if (self::$instance == null) {
            $config = require('config.php');
            self::$instance = new self($config);
        }

        return self::$instance;

    }

    /**
     * @param $query
     */
    public function insert($query) {
        mysqli_query($this->link, $query);
    }

    /**
     * @param $query
     * @return array
     */
    public function query($query) {
        $table = [];
        $result = mysqli_query($this->link, $query);
        if($result) {
            while ($cur = mysqli_fetch_array($result)) {
                $table[] = $cur;
            }
        }
        return $table;
    }

}