<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('memory_limit', '1G');

header('Content-type: text/html; charset=utf-8');
define('BASE_PATH', realpath(__DIR__ . '/../'));

$method = 'jsonparser';
//$method = 'jsondecode';

if ($method == 'jsonparser') {
    require_once BASE_PATH . '/vendor/autoload.php';
    include('CustomParser.php');

    $parser = new \CustomParser();

    $startTime = microtime(true);
    //$parser->parse('data/data.json');
    //$parser->parse('data/invalid.json');
    //$parser->parse('data/tournaments.json');
    $parser->parse('data/coaches.json');
    //$parser->parse('data/teams.json');
    //$parser->parse('data/matches.json');
    //$parser->parse('data/players.json');

    $result = $parser->getData();
} elseif ($method == 'jsondecode') {
    $startTime = microtime(true);
    $result = json_decode(file_get_contents('data/coaches.json'), true);
}

$timeTook = microtime(true) - $startTime;

echo "Parsing took: " . sprintf('%.5f', $timeTook) . " sec<br/>";
echo "Peak memory usage: " . sprintf('%.2f', memory_get_peak_usage() / 1048576) . " Mb<br/>";

echo '<pre>' . print_r($result, true) . '</pre>';