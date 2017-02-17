<?php

/**
 * Class Sfile
 */
class Sfile {

    /**
     * @var string
     */
    private $filename;
    /**
     * @var bool
     */
    private $created = false;

    /**
     * @var bool
     */
    private $index = false;

    /**
     * @param $num
     * @param $index
     */
    public function __construct($num, $index) {

        $this->index = $index;
        $config = require('config.php');

        $name = /*$this->basepath . */'sitemap/sitemap';
        if($num !== NULL) {
            $name .= '_' . ($num) . '.xml.gz';
        } else {
            $name .= '.xml';
        }

        $this->filename = $name;

    }

    /**
     * @return string
     */
    public function getFileName() { return $this->filename; }

    /**
     * @param $str
     */
    public function write($str) {

        if(!$this->created) {
            file_put_contents($this->filename, $this->header(), FILE_APPEND);
            $this->created = true;
        }

        file_put_contents($this->filename, $str, FILE_APPEND);

    }

    /**
     * @return string
     */
    private function header() {

        $header =
            '<?xml version="1.0" encoding="UTF-8"?>'."\n";

        $header .=
            ($this->index) ? '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
                : '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        return $header;


    }

    /**
     * @return string
     */
    private function footer() {

        $footer =
            ($this->index) ? '</sitemapindex>'."\n"
                : '</urlset>'."\n";

        return $footer;

    }

    /**
     * @param $file
     */
    private function gzip($file) {
        file_put_contents($file, gzencode(file_get_contents($file)));
    }

    /**
     *
     */
    public function __destruct() {

        if($this->created) {
            file_put_contents($this->filename, $this->footer(), FILE_APPEND);
            $this->gzip($this->filename);
        }

    }

}