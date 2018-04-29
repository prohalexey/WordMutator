<?php

require_once './vendor/autoload.php';

use App\WordGraph;
use App\WordMutator;
use Fhaculty\Graph\Graph;

$startWord = 'мозг';
$endWord = 'мрак';

try {
    $startWordLen = mb_strlen($startWord, 'UTF-8');

    $logger = new \Apix\Log\Logger\File('./var/log.txt');
    $logger->setMinLevel('info');

    $graph = new Graph();
    $wordGraph = new WordGraph($graph, $logger, $startWordLen);

    $mutator = new WordMutator($wordGraph);
    // If uncomment, cache must be deleted
    // $mutator->setRange(2);
    $path = $mutator->getPath($startWord, $endWord);

    $vertices = [];
    foreach ($path->getVertices() as $vertex) {
        /** @var \Fhaculty\Graph\Vertex $vertex */
        $vertices[] = $vertex->getId();
    }

    echo implode("\r\n", $vertices);
} catch (\Throwable $e) {
    echo $e->getMessage();
}
