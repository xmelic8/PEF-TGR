<?php
/**
 * Predmet: TGR
 * Ukol: 4
 * Cast: 1 (evacuation)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->run();

$printer = new Printer($analyzer->getObjMapsNetwork());
$printer->run();

class Printer{
    /**
     * @var MapsNetwork
     */
    private $objMapsNetwork;

    public function __construct(MapsNetwork $mapsNetwork)
    {
        $this->objMapsNetwork = $mapsNetwork;
    }

    public function run(){
        $this->printGouupSize();
        $this->printStatistics();
        $this->printTime();
    }

    private function printGouupSize(){
        fwrite(STDOUT, "Group size: ".$this->objMapsNetwork->getGroupSize()."\n");
    }

    private function printTime(){
        fwrite(STDOUT, "Time: ".intval($this->objMapsNetwork->getSourceCapacity()/ $this->objMapsNetwork->getGroupSize())."\n");
    }

    private function printStatistics(){
        foreach ($this->objMapsNetwork->getVertices() as $vertex) {
            fwrite(STDOUT, $vertex->getName().": ");

            if ($vertex->getFlow() == $vertex->getCapacity()) {
                fwrite(STDOUT, "]");
            }
            fwrite(STDOUT, $vertex->getFlow());
            if ($vertex->getFlow() == $vertex->getCapacity()) {
                fwrite(STDOUT, "[");
            }
            fwrite(STDOUT, "\n");
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
        $i = 0;
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            $line = $this->removeWhiteSpace($line);

            if ($line == "")
                continue;

            if ($i == 0) {
                $this->setAttributeFirstRoom($line);
            } else {
                $this->lineProcessing($line);
            }

            $i++;
        }
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function removeWhiteSpace($string){
        //Odeberu bile znaky na zacatku a na konci retezce
        $string = trim($string);

        //Odeberu bile mezery mezi slovem a >
        $string = preg_replace('/\s*>\s*/',">", $string);

        //Odeberu bile znaky za :
        $string = preg_replace('/\s*:\s*/',":", $string);

        return $string;
    }

    /**
     * @param $line
     */
    private function lineProcessing($line){
        list($name, $residue) = explode(":", $line);
        list($residue, $capacity) = explode(" ", $residue);
        list($from, $to) = explode(">", $residue);

        $this->MapsNetwork->addVertex($this->MapsNetwork->addOrFindReturnNode($from), $this->MapsNetwork->addOrFindReturnNode($to), $capacity, $name);
    }

    /**
     * @param $line
     */
    private function setAttributeFirstRoom($line){
        list($roomName, $capacity) = explode(":", $line);

        $tmpNode = new Node($roomName);
        $this->MapsNetwork->addNode($tmpNode);

        $this->MapsNetwork->setSource($tmpNode);
        $this->MapsNetwork->setSourceCapacity($capacity);
    }
}

class Analyzer{
    /**
     * @var MapsNetwork
     */
    private $objMapsNetwork;

    /**
     * Analyzer constructor.
     * @param MapsNetwork $mapsNetwork
     */
    public function __construct(MapsNetwork $mapsNetwork)
    {
        $this->objMapsNetwork = $mapsNetwork;
    }

    /**
     * @return MapsNetwork
     */
    public function getObjMapsNetwork()
    {
        return $this->objMapsNetwork;
    }

    public function run(){
        $algorithm = new Algorithm($this->objMapsNetwork);
        $this->objMapsNetwork = $algorithm->run();
    }
}

class Algorithm{
    /**
     * @var MapsNetwork
     */
    private $objMapsNetwork;
    
    const EXIT_NODE = "EXIT";

    /**
     * EdmonsKarpAlgorithm constructor.
     * @param MapsNetwork $mapsNetwork
     */
    public function __construct(MapsNetwork $mapsNetwork)
    {
        $this->objMapsNetwork = $mapsNetwork;
    }

    public function run() {
        $this->algorithmEdmonsKarpa();

        return $this->objMapsNetwork;
    }

    private function algorithmEdmonsKarpa(){
        while ($this->algorithmBoyerMoor()) {
            $tmpNode = $this->objMapsNetwork->addOrFindReturnNode(self::EXIT_NODE);
            $min = INF;

            while ($tmpNode->getPreviousNodeVertex()->getNode() !== null) {
                $childVertex = $tmpNode->getPreviousNodeVertex()->getVertex();
                $childNode = $tmpNode->getPreviousNodeVertex()->getNode();
                $tmpDifference = $childVertex->getCapacity() - $childVertex->getFlow();

                //##
                if(!$childVertex->checkVisibility()){
                    continue;
                }

                if($tmpDifference < $min) {
                    $min = $tmpDifference;
                }
                $tmpNode = $childNode;
            }

            $tmpNode = $this->objMapsNetwork->addOrFindReturnNode(self::EXIT_NODE);
            while ($tmpNode->getPreviousNodeVertex()->getNode() !== null) {
                $tmpNode->getPreviousNodeVertex()->getVertex()->setFlow($tmpNode->getPreviousNodeVertex()->getVertex()->getFlow() + $min);
                $tmpNode = $tmpNode->getPreviousNodeVertex()->getNode();
            }
        }
    }

    /**
     * @return bool
     */
    private function algorithmBoyerMoor() {
        $this->initAlgorithmBoyerMoor();

        if($this->objMapsNetwork->getSource() === null){
            return false;
        }

        $this->objMapsNetwork->getSource()->setDistance(0);

        $queue = new SplQueue();
        $queue->enqueue($this->objMapsNetwork->getSource());

        while (count($queue) > 0) {
            $node = $queue->dequeue();

                if ($node === $this->objMapsNetwork->addOrFindReturnNode(self::EXIT_NODE)) {
                    return true;
                }

                foreach ($node->getNeighbours() as $neighbour) {
                    $neighbourVertex = $neighbour->getVertex();
                    $neighbourNode = $neighbour->getNeighbour();

                    //##
                    if(!$neighbourVertex->checkVisibility()){
                        continue;
                    }

                    if(($neighbourVertex->getFlow() < $neighbourVertex->getCapacity()) &&
                        ($neighbourNode->getDistance() > ($node->getDistance() + 1))) {
                        $neighbourNode->setDistance($node->getDistance() + 1);
                        $neighbourNode->setPreviousNodeVertex($node, $neighbourVertex);
                        $queue->enqueue($neighbourNode);
                    }
                }
            }

        return false;
    }

    private function initAlgorithmBoyerMoor(){
        foreach ($this->objMapsNetwork->getNodes() as $node) {
            $node->setDistance(INF);
            $node->setPreviousNodeVertex(null, null);
        }
    }
}

class MapsNetwork {

    /**
     * @var array
     */
    private $nodes;

    /**
     * @var array
     */
    private $vertices;

    /**
     * @var Node
     */
    private $source;

    /**
     * @var int
     */
    private $sourceCapacity;

    const EXIT_NODE = "EXIT";

    /**
     * MapsNetwork constructor.
     * @param $rawData
     */
    public function __construct() {
        $this->nodes = array();
        $this->vertices = array();
        $this->source = null;
        $this->sourceCapacity = 0;
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @return Node
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Node $source
     */
    public function setSource(Node $source)
    {
        $this->source = $source;
    }

    /**
     * @param mixed $sourceCapacity
     */
    public function setSourceCapacity($sourceCapacity)
    {
        $this->sourceCapacity = $sourceCapacity;
    }

    /**
     * @param $fromNode Node
     * @param $toNode Node
     * @param $capacity int
     * @param $name
     */
    public function addVertex($fromNode, $toNode, $capacity, $name) {
        $vertex = new Vertex($fromNode, $toNode, $capacity, $name);

        $this->vertices[] = $vertex;
        $fromNode->addNeighbour($toNode, $vertex);
    }

    public function addNode(Node $node) {
        if(!array_key_exists($node->getName(), $this->nodes)) {
            $this->nodes[$node->getName()] = $node;
        }
    }

    public function addOrFindReturnNode($nodeName) {
        if(!array_key_exists($nodeName, $this->nodes)) {
            $this->nodes[$nodeName] = new Node($nodeName);
        }

        return $this->nodes[$nodeName];
    }

    public function getGroupSize() {
        $size = 0;
        /**
         * @var $resultArray Vertex[]
         */
        $resultArray = Array();

        foreach ($this->vertices as $vertex) {
            if($vertex->getTo()->getName() == self::EXIT_NODE) {
                $resultArray[] = $vertex;
            }
        }

        foreach ($resultArray as $item) {
            $size += $item->getFlow();
        }

        return $size;
    }

    /**
     * @return Vertex[]
     */
    public function getVertices() {
        return $this->vertices;
    }

    /**
     * @return mixed
     */
    public function getSourceCapacity() {
        return $this->sourceCapacity;
    }
}

class NodeItem{
    /**
     * @var
     */
    private $neighbour;

    /**
     * @var
     */
    private $vertex;

    public function __construct($neighbour, $vertex)
    {
        $this->neighbour = $neighbour;
        $this->vertex = $vertex;
    }

    /**
     * @return mixed
     */
    public function getNeighbour()
    {
        return $this->neighbour;
    }

    /**
     * @param mixed neighbour
     */
    public function setNeighbour($neighbour)
    {
        $this->neighbour = $neighbour;
    }

    /**
     * @return mixed
     */
    public function getVertex()
    {
        return $this->vertex;
    }

    /**
     * @param mixed $vertex
     */
    public function setVertex($vertex)
    {
        $this->vertex = $vertex;
    }
}

class NodeVertex{
    /**
     * @var
     */
    private $node;

    /**
     * @var
     */
    private $vertex;

    /**
     * NodeVertex constructor.
     * @param $node
     * @param $vertex
     */
    public function __construct($node, $vertex)
    {
        $this->node = $node;
        $this->vertex = $vertex;
    }

    /**
     * @return mixed
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param mixed $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return mixed
     */
    public function getVertex()
    {
        return $this->vertex;
    }

    /**
     * @param mixed $vertex
     */
    public function setVertex($vertex)
    {
        $this->vertex = $vertex;
    }
}

class Node {
    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $distance;

    /**
     * @var array
     */
    private $neighbours;

    /**
     * @var array
     */
    private $previousNodeVertex;


    /**
     * Node constructor.
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
        $this->distance = INF;
        $this->neighbours = array();
        $this->previousNodeVertex = array();
    }

    /**
     * @param $neighbour Node
     * @param $vertex Vertex
     */
    public function addNeighbour($neighbour, $vertex) {
        $this->neighbours[] = new NodeItem($neighbour, $vertex);
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getNeighbours() {
        return $this->neighbours;
    }

    public function getPreviousNodeVertex() {
        return $this->previousNodeVertex;
    }

    public function setPreviousNodeVertex($node, $vertex) {
        $this->previousNodeVertex = new NodeVertex($node, $vertex);
    }

    /**
     * @return mixed
     */
    public function getDistance() {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     */
    public function setDistance($distance) {
        $this->distance = $distance;
    }
}

class Vertex {
    /**
     * @var Node
     */
    private $from;

    /**
     * @var Node
     */
    private $to;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $capacity;

    /**
     * @var int
     */
    private $flow;

    //##
    private $visibility;

    /**
     * Vertex constructor.
     * @param Node $from
     * @param Node $to
     * @param $capacity
     * @param $name
     */
    public function __construct($from, $to, $capacity, $name) {
        $this->from = $from;
        $this->to = $to;
        $this->capacity = $capacity;
        $this->name = $name;
        $this->flow = 0;
        $this->visibility = true;
    }

    /**
     * @return mixed
     */
    public function getCapacity() {
        return $this->capacity;
    }

    /**
     * @return Node
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * @return Node
     */
    public function getTo() {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFlow() {
        return $this->flow;
    }

    /**
     * @param mixed $flow
     */
    public function setFlow($flow) {
        $this->flow = $flow;
    }

    /**
     * @return bool
     */
    public function isVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param bool $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    //##
    public function checkVisibility(){
        if($this->visibility){
            return true;
        }
        else{
            return false;
        }
    }
}
