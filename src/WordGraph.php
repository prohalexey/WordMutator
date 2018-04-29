<?php

namespace App;

use \Fhaculty\Graph\Graph;
use \Fhaculty\Graph\Vertex;
use Psr\Log\LoggerInterface;

class WordGraph
{
    /**
     * @var int
     */
    private $range = 1;
    /**
     * @var
     */
    private $wordLen;

    /**
     * @var Graph
     */
    private $graph;

    /**
     * @var LoggerInterface
     */

    private $logger;

    /**
     * @var array
     */
    private $dictionary = [];

    /**
     * WordMutator constructor.
     * @param Graph $graph
     * @param LoggerInterface $logger
     * @param $wordLen
     * @throws \InvalidArgumentException
     */
    public function __construct(Graph $graph, LoggerInterface $logger, $wordLen)
    {
        $this->wordLen = $wordLen;

        $this->graph = $graph;

        $this->logger = $logger;

        $this->loadGraph();
    }

    public function setRange($range)
    {
        $this->range = $range;
    }

    /**
     * @param $word
     * @return Vertex
     */
    public function getVertex($word): Vertex
    {
        if ($this->graph->hasVertex($word)) {
            $wordVertex = $this->graph->getVertex($word);
        } else {
            $wordVertex = $this->graph->createVertex($word);
        }

        return $wordVertex;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function loadGraph()
    {
        $cacheFile = $this->getGraphCacheFilename();
        if (file_exists($cacheFile)) {
            $this->loadGraphFromCache($cacheFile);
        } else {
            $this->createGraph();
            $this->saveGraphCache($cacheFile);
        }
    }

    /**
     * @param $cacheFile
     */
    private function loadGraphFromCache($cacheFile)
    {
        $this->graph = unserialize(file_get_contents($cacheFile));
    }

    /**
     * @param $cacheFile
     * @return bool
     */
    private function saveGraphCache($cacheFile): bool
    {
        return file_put_contents($cacheFile, serialize($this->graph));
    }

    /**
     * @return string
     */
    private function getGraphCacheFilename(): string
    {
        return sprintf('./graph/graph_%d.cache', $this->wordLen);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function createGraph()
    {
        $this->loadDictionary();

        $dictionarySize = count($this->dictionary);

        foreach ($this->dictionary as $counter => $word) {
            $wordVertex = $this->getVertex($word);

            $nearestWords = $this->getNearestWords($word);
            foreach ($nearestWords as $nearestWord) {
                $nearestWordVertex = $this->getVertex($nearestWord);

                if (!$wordVertex->hasEdgeTo($nearestWordVertex)) {
                    $wordVertex->createEdgeTo($nearestWordVertex)->setWeight(1);
                }
            }

            $this->logger->info(sprintf('В граф загружено %d слов из %d', $counter, $dictionarySize));
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function loadDictionary()
    {
        $dictionaryFilename = sprintf('./dicts/dict_%s.txt', $this->wordLen);
        if (!file_exists($dictionaryFilename)) {
            throw new \InvalidArgumentException('Отсутствует словарь слов для такой длины слов');
        }
        $this->dictionary = array_unique(array_map('trim', file($dictionaryFilename)));

        $this->logger->info(sprintf('Загружено слов из словаря: %d', count($this->dictionary)));
    }

    /**
     * @param $word
     * @return array
     */
    private function getNearestWords($word): array
    {
        $nearestWords = [];

        foreach ($this->dictionary as $comparedWord) {
            $range = $this->getRange($word, $comparedWord);
            if ($range > 0 && $range < $this->range + 1) {
                $nearestWords[] = $comparedWord;
            }
        }

        return $nearestWords;
    }

    /**
     * @param string $word1
     * @param string $word2
     * @return int
     */
    private function getRange(string $word1, string $word2): int
    {
        $word1Len = mb_strlen($word1, 'UTF-8');

        $range = 0;
        for ($position = 0; $position < $word1Len; $position++) {
            if (mb_substr($word1, $position, 1, 'UTF-8') !== mb_substr($word2, $position, 1, 'UTF-8')) {
                $range++;
            }
        }

        return $range;
    }
}