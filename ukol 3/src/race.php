<?php
/**
 * Predmet: TGR
 * Ukol: 3
 * Cast: 3 (race)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new BellmanFordAnalyzer($parser->getMapsNetwork());
$analyzer->run();

$printer = new Printer($analyzer->getListNodes());
$printer->printRun();



class Printer{
    private $listNodes;

    public function __construct(array $listNodes)
    {
        $this->listNodes = $listNodes;
    }

    public function printRun(){
        $count = count($this->listNodes);
        $i = 1;

        foreach ($this->listNodes as $key => $value){
            fwrite(STDOUT, $key);

            if($i != $count){
                fwrite(STDOUT, " - ");
            }else{
                fwrite(STDOUT, ": ".$value->getCountRoad()."\n");
            }

            $i++;
        }
    }
}

class RoadNode{
    /**
     * @var int
     */
    private $countRoad;

    /**
     * @var int
     */
    private $lenghtRoad;

    /**
     * @var
     */
    private $prevNode;

    /**
     * @var
     */
    private $nameNode;

    /**
     * @var
     */
    private $bonus;

    /**
     * RoadNode constructor.
     * @param $name
     * @param $bonus
     */
    public function __construct($name, $bonus)
    {
        $this->nameNode = $name;
        $this->countRoad = 0;
        $this->lenghtRoad = -INF;
        $this->prevNode = null;
        $this->bonus = $bonus;
    }

    /**
     * @return int
     */
    public function getCountRoad()
    {
        return $this->countRoad;
    }

    /**
     * @param int $countRoad
     */
    public function setCountRoad($countRoad)
    {
        $this->countRoad = $countRoad;
    }

    /**
     * @return int
     */
    public function getLenghtRoad()
    {
        return $this->lenghtRoad;
    }

    /**
     * @param int $lenghtRoad
     */
    public function setLenghtRoad($lenghtRoad)
    {
        $this->lenghtRoad = $lenghtRoad;
    }

    /**
     * @return null
     */
    public function getPrevNode()
    {
        return $this->prevNode;
    }

    /**
     * @param null $prevNode
     */
    public function setPrevNode($prevNode)
    {
        $this->prevNode = $prevNode;
    }

    /**
     * @return mixed
     */
    public function getNameNode()
    {
        return $this->nameNode;
    }

    /**
     * @param mixed $nameNode
     */
    public function setNameNode($nameNode)
    {
        $this->nameNode = $nameNode;
    }

    /**
     * @return bool
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * @param bool $bonus
     */
    public function setBonus($bonus)
    {
        $this->bonus = $bonus;
    }
}

class BellmanFordAnalyzer{
    /**
     * @var MapsNetwork
     */
    private $MapsNetworks;

    /**
     * @var array
     */
    private $graph;

    /**
     * @var array
     */
    private $listNodes;

    /**
     * @var int|string|null
     */
    private $startNode;

    /**
     * BellmanFordAnalyzer constructor.
     * @param MapsNetwork $mapsNetwork
     */
    public function __construct(MapsNetwork $mapsNetwork)
    {
        $this->MapsNetworks = $mapsNetwork;
        $this->graph = $mapsNetwork->getMapsNetwork();
        $this->listNodes = $mapsNetwork->getListNameNode();
        reset($this->listNodes);
        $this->startNode = key($this->listNodes);

    }

    /**
     * @return array
     */
    public function getListNodes()
    {
        return $this->listNodes;
    }

    public function run(){
        $tmp = $this->listNodes[$this->startNode];
        $tmp->setLenghtRoad(0);

        for ($i = 0; $i < count($this->listNodes); $i++){
            $tmpNodes = $this->searchNodeCountRoad($i);

            foreach ($tmpNodes as $node){
                foreach ($this->graph[$node] as $child){
                    $tmpNode = $this->MapsNetworks->getNodeListNameNode($node);
                    $bonus = intval($tmpNode->getBonus());

                    $follower = $this->MapsNetworks->getNodeListNameNode($child->getName());

                    if($follower->getLenghtRoad() < ($tmpNode->getLenghtRoad() + $bonus + $child->getRatingEdge())){
                        $follower->setCountRoad($tmpNode->getCountRoad() + 1);
                        $follower->setLenghtRoad($tmpNode->getLenghtRoad() + $bonus + $child->getRatingEdge());
                        $follower->setPrevNode($node);
                    }
                }
            }
        }
    }

    /**
     * @param $count
     * @return array
     */
    public function searchNodeCountRoad($count){
        $returnNodes = Array();
        foreach ($this->listNodes as $node) {
            if ($node->getCountRoad() == $count) {
                $returnNodes[] = $node->getNameNode();
            }
        }

        return $returnNodes;
    }
}

class Node{
    /**
     * @var
     */
    private $targetName;

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
     * @param $targetName
     * @param $name
     * @param $ratingEdge
     */
    public function __construct($targetName, $name, $ratingEdge)
    {
        $this->targetName = $targetName;
        $this->name = $name;
        $this->ratingEdge = $ratingEdge;
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

    /**
     * @return mixed
     */
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * @param mixed $targetName
     */
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;
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
    }

    /**
     * @param $nameNode
     * @param $bonus
     */
    public function setListNameNode($nameNode, $bonus){
        if(!array_key_exists($nameNode, $this->listNameNode)){
            $tmpObj = new RoadNode($nameNode, $bonus);
            $this->listNameNode[$nameNode] = $tmpObj;
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
     * @param $name
     * @return null
     */
    public function getNodeListNameNode($name){
        if(array_key_exists($name, $this->listNameNode)){
            return $this->listNameNode[$name];
        }else{
            return null;
        }
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

        //Odeberu bile mezery mezi slovem a ,
        $string = preg_replace('/\s*,\s*/',",", $string);

        //Odeberu bile znaky za :
        $string = preg_replace('/\s*:\s*/',":", $string);

        return $string;
    }


    private function lineProcessing($line){
        list($name, $roads) = explode(":", $line);
        if(substr($name, -1) == '+') {
            $hasBonus = true;
            $name = substr($name, 0, -1);
        }else{
            $hasBonus = false;
        }

        $this->MapsNetwork->setListNameNode($name, $hasBonus);

        $roads = explode(',', $roads);
        foreach ($roads as $road){
            list($nameSource, $price) = explode("(", $road);
            $price = str_replace(')', '', $price);

            $this->MapsNetwork->setMapsNetwork($name, $nameSource, $price);
        }
    }
}
?>