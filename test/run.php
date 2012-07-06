<?php
include('CustomParser.php');
$parser = new CustomParser();

$parser->parse('tournaments.json');
print_r($parser->getData());