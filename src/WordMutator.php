<?php

namespace App;

use Fhaculty\Graph\Walk;
use Graphp\Algorithms\ShortestPath\Dijkstra;

class WordMutator
{
    private $graph;

    /**
     * WordMutator constructor.
     * @param WordGraph $graph
     */
    public function __construct(WordGraph $graph)
    {
        $this->graph = $graph;
    }

    /**
     * @param $startWord
     * @param $endWord
     * @return Walk
     * @throws \InvalidArgumentException
     */
    public function getPath($startWord, $endWord): Walk
    {
        $startWordLen = mb_strlen($startWord, 'UTF-8');
        $endWordLen = mb_strlen($endWord, 'UTF-8');

        if ($startWordLen !== $endWordLen) {
            throw new \InvalidArgumentException('Длина слов не равна!');
        }

        try {
            $startWordVertex = $this->graph->getVertex($startWord);
            $endWordVertex = $this->graph->getVertex($endWord);
        } catch (\OutOfBoundsException $e) {
            throw new \InvalidArgumentException(sprintf('Слово %s отсутствует в словаре', $startWord));
        }

        try {
            $dijkstra = new Dijkstra($startWordVertex);
            $path = $dijkstra->getWalkTo($endWordVertex);
        } catch (\OutOfBoundsException $e) {
            throw new \InvalidArgumentException(
                'Нельзя преобразовать исходное слово с использованием текущего словаря'
            );
        }

        return $path;
    }
}