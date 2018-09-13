<?php

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'onepay` (
    `id_onepay` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_onepay`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
