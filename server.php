<?php
require_once __DIR__ . '/vendor/autoload.php';
//process http tcp
if (!isset($argv[1])) {
    return false;
}

$config = \Ruesin\Utils\Config::get('server.' . $argv[1]);
if (!$config) {
    return false;
}

//start stop reload restart
$operate = isset($argv[2]) ? $argv[2] : 'start';

$class = new \Swover\Server($config);
$class->$operate();
