<?php

require_once('mysql.php');

/**
 * Class Section
 */
abstract class Section {

    /**
     * @var Mysql|null
     */
    protected $sql;
    /**
     * @var string
     */
    protected $table_name;
    /**
     * @var int
     */
    protected $limit = 3;
    /**
     * @var
     */
    protected $offset;

    /**
     * @var null
     */
    protected $changefreq;
    /**
     * @var float
     */
    protected $priority;

    /**
     * @var
     */
    protected $cache;
    /**
     * @var null
     */
    protected $filter;

    /**
     * @param array $config
     */
    public function __construct($config = []) {

        $this->changefreq = $this->setChangefreq(
            (isset($config['changefreq'])) ?
                $config['changefreq'] : NULL
        );

        $this->priority = $this->setPriority(
            (isset($config['priority'])) ?
                $config['priority'] : NULL
        );

        $this->filter = (isset($config['filter'])) ?
            $config['filter'] : NULL;

        $this->sql = Mysql::getInstance();
        $table_name = strtolower(
            preg_replace("#^Section#", '', get_class($this))
        );
        $this->table_name = $table_name;
        $this->init();
    }

    /**
     * @param $changefreq
     * @return null
     */
    protected function setChangefreq($changefreq) {

        $valid = [

            'always',
            'hourly',
            'daily',
            'weekly',
            'monthly',
            'yearly',
            'never',

        ];

        if(in_array($changefreq, $valid))
            return $changefreq;

        return NULL;

    }

    /**
     * @param $priority
     * @return float
     */
    protected function setPriority($priority) {

        $cur = (float)$priority;

        if($cur >= 0.0 && $cur <= 1.0)
            return $cur;

        return 0.5;

    }

    /**
     * @return int
     */
    protected function count() {
        $result = $this->sql->query(
            "SELECT COUNT(*) as count FROM " . $this->table_name
        );
        if(is_array($result) && count($result) > 0) {
            $arr = $result[0];
            return (isset($arr['count'])) ? $arr['count'] : 0;
        }
        return 0;
    }

    /**
     *
     */
    protected function init() {
        $this->offset = 0;
    }

    /**
     *
     */
    protected function getData() {

        if(is_array($this->cache)
            && count($this->cache) > 0)
            return;

        $query = "SELECT * FROM " . $this->table_name;

        if(is_array($this->filter)) {
            $rules_line = implode(" AND ", $this->filter);
            if(!empty($rules_line))
                $query .= " WHERE " . $rules_line . " ";
        }

        $query .= " LIMIT {$this->offset}, {$this->limit}";

        $result = $this->sql->query(
            $query
        );
        if($result) {
            $this->offset += $this->limit;
        }

        $this->cache = $result;

    }

    /**
     * @return array|bool
     */
    public function getUrl() {

        $this->getData();
        if(is_array($this->cache)
            && count($this->cache) > 0) {

            $val = array_shift($this->cache);

            return [
                'url' => $this->makeRow($val),
                'updated' => $val['updated_at']
            ];

        }

        return false;

    }

    /**
     * @return null
     */
    public function getChangeFreq() { return $this->changefreq; }

    /**
     * @return float
     */
    public function getPriority() { return $this->priority; }

    /**
     * @param $values
     * @return mixed
     */
    abstract function makeRow($values);

}

/**
 * Class SectionPrice
 */
class SectionPrice       extends Section {
    /**
     * @param $values
     * @return string
     */
    public function makeRow($values) {
        return $this->table_name . '/' . $values['content'] . '.html';
    }
}

/**
 * Class SectionCompany
 */
class SectionCompany     extends Section {
    /**
     * @param $values
     * @return string
     */
    public function makeRow($values) {
        return $this->table_name . '/' . $values['content'] . '.html';
    }
}

/**
 * Class SectionNews
 */
class SectionNews        extends Section {
    /**
     * @param $values
     * @return string
     */
    public function makeRow($values) {
        return $this->table_name . '/' . $values['content'] . '.html';
    }
}

/**
 * Class SectionPub
 */
class SectionPub         extends Section {
    /**
     * @param $values
     * @return string
     */
    public function makeRow($values) {
        return $this->table_name . '/' . $values['content'] . '.html';
    }
}

/**
 * Class SectionCountry
 */
class SectionCountry     extends Section {
    /**
     * @param $values
     * @return string
     */
    public function makeRow($values) {
        return $this->table_name . '/' . $values['content'] . '/';
    }
}

/**
 * Class SectionStaticpages
 */
class SectionStaticpages extends Section {
    /**
     * @param $values
     * @return string
     */
    public function makeRow($values) {
        return $values['content'] . '/';
    }
}