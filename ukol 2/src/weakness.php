<?php
/**
 * Predmet: TGR
 * Ukol: 2
 * Cast: 3 (weakness)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->analyzeNetworkPowerCut();

$printer = new Printer($analyzer->getCriticalTies(), $analyzer->getCriticalNode());
$printer->printResult();


class Printer{
    /**
     * @var array
     */
    private $criticalTies;

    /**
     * @var array
     */
    private $criticalNode;

    /**
     * Printer constructor.
     */
    public function __construct(array $criticalTies, array $criticalNode)
    {
        $this->criticalTies = $criticalTies;
        $this->criticalNode = $criticalNode;
    }

    public function printResult(){
        foreach ($this->criticalTies as $values){
            fwrite(STDOUT, $values["parentName"]." - ".$values["name"]."\n");
        }

        foreach ($this->criticalNode as $value){
            fwrite(STDOUT, $value."\n");
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

    /**
     * @param $nameParent
     * @param $nameChild
     */
    public function deleteTiesMapsNetwork($nameParent, $nameChild){
        foreach ($this->mapsNetwork[$nameParent] as $key => $value){
            if($value->getName() == $nameChild) {
                unset($this->mapsNetwork[$nameParent][$key]);

                if(count($this->mapsNetwork[$nameParent]) == 0){
                    unset($this->mapsNetwork[$nameParent]);
                }
            }
        }

        foreach ($this->mapsNetwork[$nameChild] as $key => $value){
            if($value->getName() == $nameParent) {
                unset($this->mapsNetwork[$nameChild][$key]);

                if(count($this->mapsNetwork[$nameChild]) == 0){
                    unset($this->mapsNetwork[$nameChild]);
                }
            }
        }
    }

    /**
     * @param $nameNode
     */
    public function deleteNodesMapsNetwork($nameNode){
        $position = array_search($nameNode, $this->listNameNode);
        unset($this->listNameNode[$position]);

        unset($this->mapsNetwork[$nameNode]);

        foreach ($this->mapsNetwork as $keyNode => $nodes){
            foreach ($nodes as $key => $node){
                if($node->getName() == $nameNode){
                    unset($this->mapsNetwork[$keyNode][$key]);
                }
            }
        }
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

        reset($this->graphMaps->getListNameNode());
        $firstKeyGraph = key($this->graphMaps->getListNameNode());

        $keysArray[$firstKeyGraph] = 0;

        foreach ($this->graphMaps->getListNameNode() as $nodeName){
            //if($nodeName == $lastKeyLists)
                //continue;

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

                if(!in_array($nodeNameNew, $this->returnArray)){
                    $this->returnArray[] = $nodeNameNew;
                }
                if(!in_array($minKey, $this->returnArray)){
                    $this->returnArray[] = $minKey;
                }

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
     * @var MapsNetwork
     */
    private $MapsNetworkNew;

    /**
     * @var array
     */
    private $criticalTies;

    /**
     * @var array
     */
    private $criticalNode;

    private $MapsNetworkSource;

    /**
     * Analyzer constructor.
     * @param $MapsNetwork
     */
    public function __construct(MapsNetwork $MapsNetwork)
    {
        $this->MapsNetworkSource = $MapsNetwork;
        $DFSAnalyzer = $this->getComponentGraph();
        $this->MapsNetwork = $DFSAnalyzer;
        $this->criticalNode = array();
        $this->criticalTies = array();
    }

    /**
     * @return array
     */
    public function getCriticalTies()
    {
        return $this->criticalTies;
    }

    /**
     * @return array
     */
    public function getCriticalNode()
    {
        return $this->criticalNode;
    }


    public function analyzeNetworkPowerCut(){
        $this->analyzeRemoveTies();
        $this->analyzeRemoveNodes();
    }

    private function analyzeRemoveNodes(){
        foreach ($this->MapsNetwork->getMapsNetworkComponent() as $key => $components){
            $this->MapsNetworkNew = clone $components;

            foreach ($components->getListNameNode() as $nameNode){
                $this->MapsNetworkNew->deleteNodesMapsNetwork($nameNode);

                $analyzerPrim = new PrimAnalyzer($this->MapsNetworkNew);
                $analyzerPrim->runPrim();

                if($this->isInArray($this->MapsNetworkNew->getListNameNode(), $analyzerPrim->getReturnArray())){
                    $this->criticalNode[] = $nameNode;
                }

                $this->MapsNetworkNew = clone $components;
            }
        }
    }

    private function analyzeRemoveTies(){
        foreach ($this->MapsNetwork->getMapsNetworkComponent() as $key => $components){
            $visitedNodes = array();

            $this->MapsNetworkNew = clone $components;

            foreach ($components->getMapsNetwork() as $keyNode => $valuesNodes) {
                foreach ($valuesNodes as $key => $valueNode) {
                    if (in_array($valueNode->getName() . "," . $keyNode, $visitedNodes) ||
                        in_array($keyNode . "," . $valueNode->getName(), $visitedNodes)) {
                        $visitedNodes[] = $keyNode . "," . $valueNode->getName();
                        continue;
                    } else {
                        $visitedNodes[] = $keyNode . "," . $valueNode->getName();
                    }

                    $this->MapsNetworkNew->deleteTiesMapsNetwork($keyNode, $valueNode->getName());

                    $analyzerPrim = new PrimAnalyzer($this->MapsNetworkNew);
                    $analyzerPrim->runPrim();

                    if($this->isInArray( $this->MapsNetworkNew->getListNameNode(), $analyzerPrim->getReturnArray())){
                        $this->criticalTies[] = [
                            "parentName" => $keyNode,
                            "name" => $valueNode->getName()
                        ];
                    }

                    $this->MapsNetworkNew = clone $components;
                }
            }
        }
    }

    private function isInArray($listNameNodesOriginal, $listNameNodesNew){
       foreach ($listNameNodesOriginal as $name)
            if(!in_array($name, $listNameNodesNew))
                return true;

        return false;
    }

    /**
     * @return $DFSAnalyzer
     */
    private function getComponentGraph(){
        $DFSAnalyzer = new DFS($this->MapsNetworkSource);
        $DFSAnalyzer->searchComponent();

        return $DFSAnalyzer;
    }
}

class DFS{
    /**
     * @var array
     */
    private $listNameNodes;

    /**
     * @var array
     */
    private $mapsNetwork;

    /**
     * @var int
     */
    private $FRESH;

    /**
     * @var int
     */
    private $OPENED;

    /**
     * @var int
     */
    private $CLOSED;

    /**
     * @var
     */
    private $state;

    /**
     * @var array
     */
    private $mapsNetworkComponent;

    /**
     * @var array
     */
    private $listNameNodesComponent;

    public function __construct(MapsNetwork $objMapsNetwork)
    {
        $this->mapsNetworkComponent = array();
        $this->listNameNodesComponent = array();

        $this->listNameNodes = $objMapsNetwork->getListNameNode();
        $this->mapsNetwork = $objMapsNetwork->getMapsNetwork();

        $this->FRESH = 0;
        $this->OPENED = 1;
        $this->CLOSED = 2;

        $this->createState();
    }

    /**
     * @return array
     */
    public function getMapsNetworkComponent()
    {
        return $this->mapsNetworkComponent;
    }

    public function searchComponent(){
        $counter = 0;
        foreach ($this->listNameNodes as $nameNode){
            if($this->state[$nameNode] == $this->FRESH){
                $this->doDFS($nameNode, $counter);
                $counter++;
            }
        }

        $this->generateTiesForComponent();
    }

    private function createState(){
        foreach ($this->listNameNodes as $node){
            $this->state[$node] = $this->FRESH;
        }
    }

    /**
     * @param $nameNode
     */
    private function doDFS($nameNode, $counter){
        $this->state[$nameNode] = $this->OPENED;
        $this->listNameNodesComponent[$counter][] = $nameNode;
        $mapsNetwork = $this->mapsNetwork;
        $parentChilden = $mapsNetwork[$nameNode];

        foreach ($parentChilden as $node){
            if($this->state[$node->getName()] == $this->FRESH){
                $this->doDFS($node->getName(), $counter);
            }
        }

        $this->state[$nameNode] = $this->CLOSED;
    }

    private function generateTiesForComponent(){
        foreach ($this->listNameNodesComponent as $key => $nodes){
            $tmpObj = new MapsNetwork();
            $tmpArray = [];

            foreach ($nodes as $node){
                $tmpArray[$node] = $this->mapsNetwork[$node];
                $tmpObj->setListNameNode($node);
            }

            $tmpObj->setMapsNetworkCompleted($tmpArray);
            $this->mapsNetworkComponent[] = $tmpObj;
        }

        //print_r($this->mapsNetworkComponent);
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