<?php
/**
 * Predmet: TGR
 * Ukol: 3
 * Cast: 1 (car)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->runAnalyze();

$printer = new Printer($analyzer);
$printer->printResult();


class Printer{
    /**
     * @var Analyzer
     */
    private $analyzer;

    /**
     * Printer constructor.
     */
    public function __construct(Analyzer $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    public function printResult(){
        foreach ($this->analyzer->getReturnArray() as $keyNode => $nodeName){
            fwrite(STDOUT, $keyNode . ": " . $nodeName . "\n");
        }
    }
}

class Node{
    /**
     * @var
     */
    private $parentName;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $ratingEdge;

    /**
     * Node constructor.
     * @param $parentName
     * @param $name
     * @param $ratingEdge
     */
    public function __construct($parentName, $name, $ratingEdge)
    {
        $this->parentName = $parentName;
        $this->name = $name;
        $this->ratingEdge = $ratingEdge;
    }


    /**
     * @return mixed
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * @param mixed $parentName
     */
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getRatingEdge()
    {
        return $this->ratingEdge;
    }

    /**
     * @param mixed $ratingEdge
     */
    public function setRatingEdge($ratingEdge)
    {
        $this->ratingEdge = $ratingEdge;
    }
}

class Dijkstra{
    /**
     * @var array
     */
    private $returnArray;

    /**
     * @var int
     */
    private $INT_MAX_VALUE;

    /**
     * @var MapsNetwork
     */
    private $graphMaps;

    /**
     * Dijkstra constructor.
     * @param MapsNetwork $graphMaps
     */
    public function __construct(MapsNetwork $graphMaps)
    {
        $this->returnArray = array();
        $this->INT_MAX_VALUE = 0x7FFFFFFF;
        $this->graphMaps = $graphMaps;
    }

    /**
     * @return array
     */
    public function getReturnArray()
    {
        return $this->returnArray;
    }


    /**
     * @param $keysArray
     * @param $visitedNodes
     * @return int|string|null
     */
    private function searchMinKey($keysArray, $visitedNodes)
    {
        $min = $this->INT_MAX_VALUE;
        reset($keysArray);
        $minIndex = key($keysArray);

        foreach ($keysArray as $key => $value){
            if($visitedNodes[$key] === false && $value <= $min){
                $min = $value;
                $minIndex = $key;
            }
        }

        return $minIndex;
    }

    /**
     * @param $startNode
     */
    public function runDijkstra($startNode){
        $keysArray = array();
        $visitedNodes = array();

        foreach ($this->graphMaps->getListNameNode() as $value){
            $keysArray[$value] = $this->INT_MAX_VALUE;
            $visitedNodes[$value] = false;
        }

        $keysArray[$startNode] = 0;

        foreach ($this->graphMaps->getListNameNode() as $nodeName){
            $minKey = $this->searchMinKey($keysArray, $visitedNodes);
            $visitedNodes[$minKey] = true;

            foreach ($this->graphMaps->getListNameNode() as $nodeNameNew){
                $rangeValue = $this->graphMaps->hasChild($minKey, $nodeNameNew);

                if($visitedNodes[$nodeNameNew]){
                    continue;
                }elseif ($rangeValue == 0){
                    continue;
                }elseif($keysArray[$minKey] == $this->INT_MAX_VALUE){
                    continue;
                }elseif($keysArray[$minKey] + $rangeValue >= $keysArray[$nodeNameNew]){
                    continue;
                }

                $keysArray[$nodeNameNew] = $keysArray[$minKey] + $rangeValue;
            }
        }

        $this->returnArray = $keysArray;
    }
}

class Analyzer{
    /**
     * @var MapsNetwork
     */
    private $MapsNetwork;

    /**
     * @var Dijkstra
     */
    private $Dijkstra;

    /**
     * @var string
     */
    private $startNode;

    /**
     * @var array
     */
    private $returnArray;

    /**
     * Analyzer constructor.
     * @param $MapsNetwork
     */
    public function __construct(MapsNetwork $MapsNetwork)
    {
        $this->MapsNetwork = $MapsNetwork;
        $this->Dijkstra = new Dijkstra($this->MapsNetwork);
        $this->startNode = "Vy";
        $this->returnArray = array();
    }

    /**
     * @return array
     */
    public function getReturnArray()
    {
        return $this->returnArray;
    }

    public function runAnalyze(){
        $this->Dijkstra->runDijkstra($this->startNode);
        $this->sortAndNegatively();
    }

    private function sortAndNegatively(){
        $tmpArray = $this->Dijkstra->getReturnArray();
        ksort($tmpArray);
        asort($tmpArray);

        foreach ($tmpArray as $key => $value){
            $this->returnArray[$key] = -1 * abs($value);
        }
    }
}

class MapsNetwork{
    /**
     * @var array
     */
    private $mapsNetwork;

    /**
     * @var array
     */
    private $listNameNode;

    /**
     * MapsNetwork constructor.
     */
    public function __construct()
    {
        $this->mapsNetwork = [];
        $this->listNameNode = [];
    }

    /**
     * @return array
     */
    public function getMapsNetwork()
    {
        return $this->mapsNetwork;
    }

    /**
     * @param $A
     * @param $B
     * @param $rating
     */
    public function setMapsNetwork($A, $B, $rating)
    {
        $this->mapsNetwork[$A][] = new Node($A, $B, $rating);
        $this->mapsNetwork[$B][] = new Node($B, $A, $rating);

        $this->setListNameNode($A);
        $this->setListNameNode($B);
    }

    /**
     * @param $mapsNetworkNew
     */
    public function setMapsNetworkCompleted($mapsNetworkNew){
        $this->mapsNetwork = $mapsNetworkNew;
    }

    /**
     * @param $nameNode
     */
    public function setListNameNode($nameNode){
        if(!in_array($nameNode, $this->listNameNode)){
            $this->listNameNode[] = $nameNode;
        }
    }

    /**
     * @return array
     */
    public function getListNameNode()
    {
        return $this->listNameNode;
    }

    /**
     * @param $parent
     * @param $childName
     * @return bool
     */
    public function hasChild($parent, $childName){
        if(!isset($this->mapsNetwork[$parent])){
            return 0;
        }

        foreach ($this->mapsNetwork[$parent] as $child){
            if($child->getName() == $childName)
                return $child->getRatingEdge();
        }

        return 0;
    }

    /**
     * @param $parentName
     * @param $childName
     * @return null
     */
    public function getOneNode($parentName, $childName){
        foreach ($this->mapsNetwork[$parentName] as $child){
            if($child->getName() == $childName)
                return $child;
        }

        return null;
    }
}

class Parser{
    /**
     * @var MapsNetwork
     */
    private $MapsNetwork;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->MapsNetwork = new MapsNetwork();
    }

    /**
     * @return MapsNetwork
     */
    public function getMapsNetwork()
    {
        return $this->MapsNetwork;
    }


    public function readLine()
    {
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            $line = $this->removeWhiteSpace($line);

            $this->lineProcessing($line);
        }

        //print_r($this->MapsNetwork->getMapsNetwork());
        //print_r($this->MapsNetwork->getListNameNode());
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function removeWhiteSpace($string){
        //Odeberu bile znaky na zacatku a na konci retezce
        $string = trim($string);

        //Odeberu bile mezery mezi slovem a -
        $string = preg_replace('/\s*-\s*/',"-", $string);

        //Odeberu bile znaky za :
        $string = preg_replace('/\s*:\s*/',":", $string);

        return $string;
    }


    private function lineProcessing($line){
        list($bind, $rating) = explode(":", $line);
        list($A, $B) = explode("-", $bind);

        $this->MapsNetwork->setMapsNetwork($A, $B, $rating);
    }
}
?>