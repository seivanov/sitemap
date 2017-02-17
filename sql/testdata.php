<?php

require_once('lib/mysql.php');

/**
 * @param int $num
 * @return string
 */
function getRandomName($num = 10) {

    $string = '';
    $characters = "abcdefghijklmnopqrstuvwxyz";

    for ($p = 0; $p < $num; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }

    return $string;

}

$tables = [

    'company',
    'country',
    'news',
    'price',
    'pub',
    'staticpages',

];

$sql = Mysql::getInstance();

foreach($tables as $table) {
    $sql->insert("DELETE FROM {$table}");
    for ($i = 0; $i < 100; $i++)
        $sql->insert("INSERT INTO {$table} SET content = '" . getRandomName() . "'");
}