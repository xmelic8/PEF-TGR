<?php
/**
 * Predmet: TGR
 * Ukol: 2
 * Cast: 2 (reset)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->resetNetworks();

$printer = new Printer($analyzer->getPrimAnalyzer());
$printer->printResult();


class Printer{
    /**
     * @var PrimAnalyzer
     */
    private $PrimAnalyzer;

    /**
     * @var MapsNetwork
     */
    private $graphMaps;

    /**
     * Printer constructor.
     */
    public function __construct(PrimAnalyzer $primAnalyzer)
    {
        $this->PrimAnalyzer = $primAnalyzer;
        $this->graphMaps = $this->PrimAnalyzer->getGraphMaps();
    }

    public function printResult(){
        $totalValue = 0;
        foreach ($this->PrimAnalyzer->getReturnArray() as $keyNode => $nodeName){
            fwrite(STDOUT, $nodeName . " - " . $keyNode . ": " . $this->graphMaps->getOneNode($nodeName, $keyNode)->getRatingEdge() . "\n");

            $totalValue += $this->graphMaps->getOneNode($nodeName, $keyNode)->getRatingEdge();
        }

        fwrite(STDOUT, "Hodnoceni: ".$totalValue . "\n");
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

class PrimAnalyzer {
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
     * @return MapsNetwork
     */
    public function getGraphMaps()
    {
        return $this->graphMaps;
    }


    function runPrim(){
        $keysArray = array();
        $visitedNodes = array();

        foreach ($this->graphMaps->getListNameNode() as $value){
            $keysArray[$value] = $this->INT_MAX_VALUE;
            $visitedNodes[$value] = false;
        }

        reset($this->graphMaps);
        $firstKeyGraph = key($this->graphMaps);
        $lastKeyLists = end($this->graphMaps->getListNameNode());

        $keysArray[$firstKeyGraph] = 0;

        foreach ($this->graphMaps->getListNameNode() as $nodeName){
            //if($nodeName == $lastKeyLists)
              //  continue;

            $minKey = $this->searchMinKey($keysArray, $visitedNodes);
            $visitedNodes[$minKey] = true;

            foreach ($this->graphMaps->getListNameNode() as $nodeNameNew){
                $rangeValue = $this->graphMaps->hasChild($minKey, $nodeNameNew);
                if($visitedNodes[$nodeNameNew]){
                    continue;
                }elseif (!$rangeValue){
                    continue;
                }elseif ($rangeValue > $keysArray[$nodeNameNew]){
                    continue;
                }

                $this->returnArray[$nodeNameNew] = $minKey;
                $keysArray[$nodeNameNew] = $rangeValue;
            }
        }
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
            if($visitedNodes[$key] == false && $value < $min){
                $min = $value;
                $minIndex = $key;
            }
        }

        return $minIndex;
    }
}

class Analyzer{
    /**
     * @var MapsNetwork
     */
    private $MapsNetwork;

    /**
     * @var PrimAnalyzer
     */
    private $PrimAnalyzer;

    /**
     * Analyzer constructor.
     * @param $MapsNetwork
     */
    public function __construct(MapsNetwork $MapsNetwork)
    {
        $this->MapsNetwork = $MapsNetwork;
        $this->PrimAnalyzer = new PrimAnalyzer($this->MapsNetwork);
    }

    /**
     * @return PrimAnalyzer
     */
    public function getPrimAnalyzer()
    {
        return $this->PrimAnalyzer;
    }

    public function resetNetworks(){
        $this->PrimAnalyzer->runPrim();
    }
}

class Parser{
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