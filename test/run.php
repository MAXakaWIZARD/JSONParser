<?php
header('Content-type: text/html; charset=utf-8');
define('BASE_PATH', realpath(__DIR__ . '/../'));

require_once BASE_PATH . '/package/Token.php';
require_once BASE_PATH . '/package/Lexer.php';
require_once BASE_PATH . '/package/Parser.php';
require_once BASE_PATH . '/package/ParserException.php';
include('CustomParser.php');

$parser = new \CustomParser();

$startTime = microtime(true);
//$parser->parse('data.json');
//$parser->parse('invalid.json');
$parser->parse('tournaments.json');
//$parser->parse('teams.json');
$timeTook = microtime(true) - $startTime;

echo "Parsing took: " . sprintf('%.5f', $timeTook) . " sec<br/>";
echo "Peak memory usage: " . sprintf('%.2f', memory_get_peak_usage() / 1048576) . " Mb<br/>";

echo '<pre>' . print_r($parser->getData(), true) . '</pre>';