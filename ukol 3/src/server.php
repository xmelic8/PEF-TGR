<?php
/**
 * Predmet: TGR
 * Ukol: 3
 * Cast: 2 (server)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->runAnalyze();

$printer = new Printer($parser->getMapsNetwork(), $analyzer->getReturnArrayNodes());
$printer->runPrint();


class Printer{
    /**
     * @var array
     */
    private $mapsNetwork;

    /**
     * @var
     */
    private $resultList;

    /**
     * @var MapsNetwork
     */
    private $graph;

    /**
     * Printer constructor.
     * @param MapsNetwork $mapsNetwork
     * @param $resultList
     */
    public function __construct(MapsNetwork $mapsNetwork, $resultList)
    {
        $this->graph = $mapsNetwork;
        $this->mapsNetwork = $mapsNetwork->getMapsNetwork();
        $this->resultList = $resultList;
    }

    public function runPrint(){
        $parent = null;
        $count = 0;

        foreach ($this->resultList as $node){
            if($parent !== null){
                fwrite(STDOUT, " - ");
            }

            fwrite(STDOUT, $node);

            $tmpCount = $this->graph->hasChild($parent, $node);
            if($tmpCount){
                $count += $tmpCount;
            }

            $parent = $node;
        }

        fwrite(STDOUT, ": " . $count . "\n");
    }
}

class Analyzer
{
    /**
     * @var array
     */
    private $mapsNetwork;

    /**
     * @var array 
     */
    private $multiGraphMapsNetwork;

    /**
     * @var array
     */
    private $multiGraphListNodes;

    /**
     * @var MapsNetworkExtends
     */
    private $objMapsNetworkExtends;

    /**
     * @var array
     */
    private $returnArrayNodes;

    /**
     * Analyzer constructor.
     * @param MapsNetwork $mapsNetwork
     */
    public function __construct(MapsNetwork $mapsNetwork)
    {
        $this->mapsNetwork = $mapsNetwork->getMapsNetwork();
        $this->objMapsNetworkExtends = new MapsNetwork();
        $this->multiGraphMapsNetwork = array();
        $this->multiGraphListNodes = array();
        $this->returnArrayNodes = array();
    }

    /**
     * @return array
     */
    public function getReturnArrayNodes()
    {
        return $this->returnArrayNodes;
    }


    public function runAnalyze()
    {
        reset($this->mapsNetwork);
        $firstKey = key($this->mapsNetwork);

        $this->searchMultiGraph($firstKey);
        $this->setMapsNetwork();

        $dfs = new DFS($this->objMapsNetworkExtends);
        $this->returnArrayNodes = $dfs->getListNameNodesComponent();
    }

    /**
     * @param $startNode
     */
    private function searchMultiGraph($startNode)
    {
        $this->multiGraphMapsNetwork[$startNode] = $this->mapsNetwork[$startNode];

        while ($this->searchMinimumChildern()) {
            continue;
        }

        if (sizeof($this->mapsNetwork) > sizeof($this->multiGraphMapsNetwork)) {
            $tmpNodes = array_diff_key($this->mapsNetwork, $this->multiGraphMapsNetwork);
            if (sizeof($tmpNodes)) {
                reset($tmpNodes);
                $this->searchMultiGraph(key($tmpNodes));
            }
        }
    }

    /**
     * @return bool
     */
    private function searchMinimumChildern()
    {
        $minimumArray = Array();

        foreach ($this->multiGraphMapsNetwork as $node => $values) {
            foreach ($this->mapsNetwork[$node] as $neighbour) {
                if (!array_key_exists($neighbour->getName(), $this->multiGraphMapsNetwork)) {
                    $minimumArray[] = $neighbour;
                }
            }
        }

        if (count($minimumArray)) {
            usort($minimumArray, function ($first, $two) {
                return $first->getRatingEdge() > $two->getRatingEdge();
            });
            $this->multiGraphListNodes[] = $minimumArray[0];

            if (array_key_exists($minimumArray[0]->getParentName(), $this->multiGraphMapsNetwork)) {
                $this->multiGraphMapsNetwork[$minimumArray[0]->getName()] = $this->mapsNetwork[$minimumArray[0]->getName()];
            } else {
                $this->multiGraphMapsNetwork[$minimumArray[0]->getParentName()] = $this->mapsNetwork[$minimumArray[0]->getParentName()];
            }

            return true;
        }

        return false;
    }

    private function setMapsNetwork(){
        foreach ($this->multiGraphMapsNetwork as $key => $nodes){
            foreach ($nodes as $node){
                $this->objMapsNetworkExtends->setMapsNetwork($key, $node->getName(), $node->getRatingEdge());
            }
        }
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
    private $listNameNodesComponent;

    public function __construct(MapsNetwork $objMapsNetworkExtends)
    {
        $this->listNameNodesComponent = array();

        $this->listNameNodes = $objMapsNetworkExtends->getListNameNode();
        $this->mapsNetwork = $objMapsNetworkExtends->getMapsNetwork();

        $this->FRESH = 0;
        $this->OPENED = 1;
        $this->CLOSED = 2;

        $this->createState();
        $this->doDFS("A");
    }

    private function createState(){
        foreach ($this->listNameNodes as $node){
            $this->state[$node] = $this->FRESH;
        }
    }

    /**
     * @return array
     */
    public function getListNameNodesComponent()
    {
        return $this->listNameNodesComponent;
    }


    /**
     * @param $nameNode
     */
    private function doDFS($nameNode){
        $this->state[$nameNode] = $this->OPENED;
        $this->listNameNodesComponent[] = $nameNode;
        $mapsNetwork = $this->mapsNetwork;
        $parentChilden = $mapsNetwork[$nameNode];

        foreach ($parentChilden as $node){
            if($this->state[$node->getName()] == $this->FRESH){
                $this->doDFS($node->getName());
            }
        }

        $this->state[$nameNode] = $this->CLOSED;
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
    protected $mapsNetwork;

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