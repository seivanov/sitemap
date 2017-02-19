<?php

declare(ticks = 1);

require_once('lib/mysql.php');
require_once('lib/sections.php');
require_once('lib/sfile.php');

pcntl_signal(SIGINT, "signal_handler");

/**
 * Class SitemapGenerator
 */
class SitemapGenerator {

    /**
     * @var int
     */
    private $file_size_limit = 52428800; // 50MB
    /**
     * @var int
     */
    private $rows_limit = 50000; // 45000 ?

    /**
     * @var
     */
    private $basepath;
    /**
     * @var
     */
    private $baseurl;

    /**
     * @var int
     */
    private $filenum = 1;

    /**
     * @param array $config
     */
    private function exam_config($config = []) {

        $this->basepath = (isset($config['basepath']))
            ? $config['basepath'] : NULL;
        $this->baseurl = (isset($config['baseurl']))
            ? $config['baseurl'] : NULL;

    }

    /**
     *
     */
    public function __construct() {
        $this->exam_config(require('config.php'));
    }

    /**
     * @param array $build_list
     */
    public function build($build_list = []) {

        $files_list = [];

        foreach($build_list as $section) {
            $files_list = array_merge(
                $files_list, $this->generate($section)
            );
        }

        $this->genmain($files_list);

    }

    /**
     * @param array $files_list
     */
    private function genmain($files_list = []) {

        $main_file = $this->getFile(NULL, true);
        foreach($files_list as $file) {
            $main_file->write($this->makeMainBlock([
                'loc' => $file->getFileName(),
            ]));
            unset($file);
        }
        unset($main_file);

    }

    /**
     * @param $data
     * @return string
     */
    private function makeBlock($data) {

        $lastmod = date('Y-m-d');

        $block =
            "\t".'<url>'."\n";
        if(isset($data['loc']))
            $block .= "\t\t".'<loc>'.$this->baseurl .$data['loc'].'</loc>'."\n";
        if(isset($data['lastmod']))
            $block .= "\t\t".'<lastmod>'.$data['lastmod'].'</lastmod>'."\n";
        if(isset($data['changefreq']))
            $block .= "\t\t".'<changefreq>'.$data['changefreq'].'</changefreq>'."\n";
        if(isset($data['priority']))
            $block .= "\t\t".'<priority>'.$data['priority'].'</priority>'."\n";
        $block .= "\t".'</url>'."\n";

        return $block;

    }

    /**
     * @param $data
     * @return string
     */
    private function makeMainBlock($data) {

        $lastmod = date('Y-m-d');

        $block =
            "\t".'<sitemap>'."\n";
        if(isset($data['loc']))
            $block .= "\t\t".'<loc>'.$this->baseurl . $data['loc'].'</loc>'."\n";
        $block .= "\t\t".'<lastmod>'.$lastmod.'</lastmod>'."\n";
        $block .= "\t".'</sitemap>'."\n";

        return $block;

    }

    /**
     * @param $section
     * @return array
     */
    private function generate($section) {

        $files_list = [];

        $current_bytes = 0;
        $current_lines = 0;

        $file = $this->getFile($this->filenum);
        $files_list[] = $file;

        while($cur = $section->getUrl()) {

            $cururl = $cur['url'];

            $current_lines++;

            $block = $this->makeBlock([
                'loc' => $cururl,
                'lastmod' => $cur['updated'],
                'changefreq' => $section->getChangeFreq(),
                'priority' => $section->getPriority(),
            ]);

            $current_bytes += strlen($block);

            if($current_lines > $this->rows_limit
                    || $current_bytes > $this->file_size_limit) {

                $this->filenum++;
                $file = $this->getFile($this->filenum);
                $files_list[] = $file->getFileName();

                $current_lines = 1;
                $current_bytes = strlen($block);

            }

            $file->write($block);

        }

        if(!$current_lines)
            return [];

        $this->filenum++;

        return $files_list;

    }

    /**
     * @param null $num
     * @param bool|false $index
     * @return Sfile
     */
    private function getFile($num = NULL, $index = false) {
        return new Sfile($num, $index);
    }

}

$generator = new SitemapGenerator();
$generator->build([

    new SectionPrice([
        'changefreq' => 'always',
        'priority' => 0.2,
        'filter' => [
            'active = 1'
        ]
    ]),
    new SectionCompany([
        'changefreq' => 2,
        'priority' => 2,
    ]),
    new SectionNews([
        'changefreq' => 3,
        'priority' => 3,
    ]),
    new SectionPub([
        'changefreq' => 4,
        'priority' => 4,
    ]),
    new SectionCountry([
        'changefreq' => 5,
        'priority' => 5,
    ]),
    new SectionStaticpages([
        'changefreq' => 6,
        'priority' => 6,
    ])

]);

function signal_handler($signal) {
    switch($signal) {
        case SIGINT:
            print "Ctrl C\n";
    }
}