<?php
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

function objStart($value, $property) {
	printf("{\n");
}

function objEnd($value, $property) {
	printf("}\n");
}

function arrayStart($value, $property) {
	printf("[\n");
}

function arrayEnd($value, $property) {
	printf("]\n");
}

function property($value, $property) {
	printf("Property: %s\n", $value);
}

function scalar($value, $property) {
	printf("Value: %s\n", $value);
}

// initialize the parser object
$parser = new \Json\Parser();

// sets the callbacks
$parser->setArrayHandlers('arrayStart', 'arrayEnd');
$parser->setObjectHandlers('objStart', 'objEnd');
$parser->setPropertyHandler('property');
$parser->setScalarHandler('scalar');

// parse the document
$parser->parseDocument(__DIR__ . '/data/data.json');
