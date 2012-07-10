<?php
set_time_limit(0);

header('Content-type: text/html; charset=utf-8');
define('BASE_PATH', realpath(__DIR__ . '/../'));

$method = 'jsonparser';
//$method = 'jsondecode';

if ($method == 'jsonparser') {
    require_once BASE_PATH . '/package/Token.php';
    require_once BASE_PATH . '/package/Lexer.php';
    require_once BASE_PATH . '/package/Parser.php';
    require_once BASE_PATH . '/package/ParserException.php';
    include('CustomParser.php');

    $parser = new \CustomParser();

    $startTime = microtime(true);
    $parser->parse('data/data.json');
    //$parser->parse('data/invalid.json');
    //$parser->parse('data/tournaments.json');
    $parser->parse('data/coaches.json');
    //$parser->parse('data/teams.json');
    //$parser->parse('data/matches.json');

    $result = $parser->getData();
} elseif ($method == 'jsondecode') {
    $startTime = microtime(true);
    $result = json_decode(file_get_contents('data/coaches.json'), true);
} elseif ($method == 'jslint') {
    require_once BASE_PATH . '/../jsonlint/src/Seld/JsonLint/JsonParser.php';
    require_once BASE_PATH . '/../jsonlint/src/Seld/JsonLint/Lexer.php';
    require_once BASE_PATH . '/../jsonlint/src/Seld/JsonLint/ParsingException.php';
    require_once BASE_PATH . '/../jsonlint/src/Seld/JsonLint/Undefined.php';

    $parser = new \Seld\JsonLint\JsonParser();

    $startTime = microtime(true);

    //$json = file_get_contents('data/tournaments.json');
    $json = file_get_contents('data/teams.json');
    $result = $parser->parse($json);
}

$timeTook = microtime(true) - $startTime;

echo "Parsing took: " . sprintf('%.5f', $timeTook) . " sec<br/>";
echo "Peak memory usage: " . sprintf('%.2f', memory_get_peak_usage() / 1048576) . " Mb<br/>";

echo '<pre>' . print_r($result, true) . '</pre>';