<?php
/**
 * Predmet: TGR
 * Ukol: 4
 * Cast: 3 (parking)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->run();

$printer = new Printer($analyzer->getObjFinalRound());
$printer->run();

class Printer{
    /**
     * @var Round
     */
    private $objFinalRound;

    /**
     * Printer constructor.
     * @param Round $objFinalRound
     */
    public function __construct(Round $objFinalRound)
    {
        $this->objFinalRound = $objFinalRound;
    }

    public function run(){
        $ratingPairs = $this->objFinalRound->getPairsRating();

        foreach ($this->objFinalRound->getPairsNodes() as $key => $pair){
            fwrite(STDOUT, $pair[0]." ".$pair[1]." ".$ratingPairs[$key]."\n");
        }

        fwrite(STDOUT, "Celkem: ".$this->objFinalRound->getRating()."\n");
    }
}

class Analyzer{

    /**
     * @var MapsNetwork
     */
    private $objMapsNetwork;

    /**
     * @var array
     */
    private $mapsNetwork;

    private $objFinalRound;

    /**
     * Analyzer constructor.
     * @param MapsNetwork $mapsNetwork
     */
    public function __construct(MapsNetwork $mapsNetwork)
    {
        $this->objMapsNetwork = $mapsNetwork;
        $this->mapsNetwork = $mapsNetwork->getMapsNetwork();
        $this->objFinalRound = null;
    }

    public function run(){
        $searchEngine = new SearchEngine($this->objMapsNetwork);

       // $searchEngine->start("B01_1", "P01_1");
        //$searchEngine->start("B01", "P01");

       foreach ($this->objMapsNetwork->getListBuildings() as $building){
            $tmpBuildings = $this->mapsNetwork[$building->getName()];
            foreach ($tmpBuildings as $tmpBuilding){
                $searchEngine->start($tmpBuilding->getParentName(), $tmpBuilding->getName());
            }
        }

       $this->objFinalRound = $searchEngine->getObjFinalRound();
       //print_r($searchEngine->getObjFinalRound());
    }

    /**
     * @return null
     */
    public function getObjFinalRound()
    {
        return $this->objFinalRound;
    }
}

class Round{
    /**
     * @var
     */
    private $pairsNodes;

    /**
     * @var
     */
    private $round;

    /**
     * @var int
     */
    private $rating;

    /**
     * @var array
     */
    private $pairsRating;

    /**
     * Round constructor.
     * @param $pairsNodes
     * @param $round
     */
    public function __construct($pairsNodes, $round)
    {
        $this->pairsNodes = $pairsNodes;
        $this->round = $round;
        $this->rating = 0;
        $this->pairsRating = array();
    }

    /**
     * @return mixed
     */
    public function getPairsNodes()
    {
        return $this->pairsNodes;
    }

    /**
     * @return mixed
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return array
     */
    public function getPairsRating()
    {
        return $this->pairsRating;
    }

    /**
     * @param $pairsRating
     * @param $i
     */
    public function setPairsRating($pairsRating, $i)
    {
        $this->pairsRating[$i] = $pairsRating;
    }
}

class SearchEngine{
    /**
     * @var array
     */
    private $mapsNetwork;

    /**
     * @var MapsNetwork
     */
    private $objMapsNetwork;

    /**
     * @var array
     */
    private $tmpContainer;

    /**
     * @var array
     */
    private $pairsOneIteration;

    private $objFinalRound;

    /**
     * @var int
     */
    private $limitations;

    /**
     * @var int
     */
    private $counterLimitations;

    /**
     * SearchEngine constructor.
     * @param MapsNetwork $objMapsNetwork
     */
    public function __construct(MapsNetwork $objMapsNetwork)
    {
        $this->mapsNetwork = $objMapsNetwork->getMapsNetwork();
        $this->objMapsNetwork = $objMapsNetwork;

        $this->tmpContainer = array();
        $this->pairsOneIteration = array();
        $this->objFinalRound = null;
        $this->limitations = $this->countMaxTies();
        $this->counterLimitations = 0;
    }

    /**
     * @return null
     */
    public function getObjFinalRound()
    {
        return $this->objFinalRound;
    }

    /**
     * @param $node1
     * @param $node2
     */
    public function start($node1, $node2)
    {
        $pairs[] = new Round(array(array($node1, $node2)), null);

        $this->startOne($pairs);
    }

    /**
     * @param $arrayRounds
     */
    private function startOne($arrayRounds){
       if($this->counterLimitations >  $this->limitations)
            return;

       $this->counterLimitations++;

       foreach ($arrayRounds as $pair){
           $this->tmpContainer = array();
           $this->tmpContainer = $pair->getPairsNodes();

           if(count($pair->getPairsNodes()) > $this->countMaxTies()-1){
               continue;
           }

           foreach ($this->getFreeNodes($this->tmpContainer, $this->objMapsNetwork->getListBuildings()) as $freeNode){
               $startNew = true;

               $tmp = $this->DFS($freeNode, 0, array());

               $tmp = $this->checkRoads($tmp);
               $tmp = $this->findMaxItemRound($tmp);

               if(count($tmp) == 0){
                   continue;
               }

               $tmp = $this->xorFunction($tmp);
               //print_r($tmp);

               foreach ($tmp as $key => $cesta){
                   if($this->countFinal($cesta)){
                       $tmp = $this->calculationRating($cesta);
                       $this->comparePairRound($tmp);
                       $startNew = false;
                   }
               }

               if($startNew === true) {
                   $this->startOne($tmp);
               }
            }
        }
    }

    /**
     * @param Round $round
     * @return Round
     */
    private function calculationRating(Round $round){
        $roundOneNode = $round->getPairsNodes();
        $count = 0;
        foreach ($roundOneNode as $key => $pair){
            list($parent, $name) = $pair;
            $tmpNode = $this->objMapsNetwork->getNode($parent, $name);
            $round->setPairsRating($tmpNode->getRatingEdge(), $key);
            $count += $tmpNode->getRatingEdge();
        }

        $round->setRating($count);

        return $round;
    }

    /**
     * @param Round $round
     */
    private function comparePairRound(Round $round){
        if(($this->objFinalRound === null) ||
            ($round->getRating() < $this->objFinalRound->getRating())){
            $this->objFinalRound = $round;
        }
    }

    /**
     * @param $tmp
     * @return array
     */
    public function findMaxItemRound($tmp){
        $countMax = 0;
        $returnArray = array();
        foreach ($tmp as $item){
            $tmpCount = count($item);
            if($countMax < $tmpCount){
                $countMax = $tmpCount;
            }
        }

        foreach ($tmp as $item){
            if(count($item) == $countMax){
                $returnArray[] = $item;
            }
        }

        return $returnArray;
    }

    /**
     * @param $nameNode
     * @param $counter
     * @param $parent
     * @return array
     */
    private function DFS($nameNode, $counter, $parent){
        $parent[] = $nameNode;
        $returnArray[] = $parent;
        $mapsNetwork = $this->mapsNetwork;
        $parentChilden = $mapsNetwork[$nameNode];
        $counter++;

        if($counter == 2){
            $this->pairsOneIteration = array();
        }

        foreach ($parentChilden as $node){
            if($this->inActualTwoNodes($node->getName(), $nameNode)){
                continue;
            }

            if(($counter % 2) && !$this->inPairTwoNodes($nameNode, $node->getName())){ //hledam lichy, obsahuje hranu
                $this->pairsOneIteration[] = array($nameNode, $node->getName());
                $returnArray = array_merge($this->DFS($node->getName(), $counter, $parent), $returnArray);
            }elseif(!($counter % 2) && $this->inPairTwoNodes($nameNode, $node->getName())){//hledam sudy, neobsahuje hranu
                $this->pairsOneIteration[] = array($nameNode, $node->getName());
                $returnArray = array_merge($this->DFS($node->getName(), $counter, $parent), $returnArray);
            }
        }

        return $returnArray;
    }

    /**
     * @param $pairs
     * @param $buildings
     * @return array
     */
    private function getFreeNodes($pairs, $buildings){
        $returnArray = array();

        foreach ($buildings as $building){
            $exists = true;
            foreach ($pairs as $pair){
                if(in_array($building->getName(), $pair)){
                    $exists = false;
                }
            }

            if($exists){
                $returnArray[] = $building->getName();
            }
        }

        return $returnArray;
    }

    /**
     * @param Round $round
     * @return bool
     */
    private function countFinal(Round $round){
        if(count($round->getPairsNodes()) == $this->countMaxTies()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return float|int
     */
    private function countMaxTies(){
        return (count($this->objMapsNetwork->getListParking()) + count($this->objMapsNetwork->getListBuildings())) / 2;
    }

    /**
     * @param $arrayRoads
     * @return array
     */
    private function checkRoads($arrayRoads){
        $returnArray = array();
        foreach ($arrayRoads as $road){
            if(count($road) < 4){
                continue;
            }elseif(count($road) == 0){
                continue;
            }

            $tmp = array();
            $add = true;

            foreach ($road as $node){
                if(in_array($node, $tmp)){
                    $add = false;
                    break;
                }else{
                    $tmp[] = $node;
                }
            }

            if($add) {
                $returnArray[] = $road;
            }
        }

        return $returnArray;
    }

    /**
     * @param $node1
     * @param $node2
     * @return bool
     */
    private function inPairTwoNodes($node1, $node2){
        foreach ($this->tmpContainer as $par){
            if($par[0] == $node1 && $par[1] == $node2){
                return true;
            }elseif($par[0] == $node2 && $par[1] == $node1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $node1
     * @param $node2
     * @return bool
     */
    private function inActualTwoNodes( $node1, $node2){
        foreach ($this->pairsOneIteration as $par){
            if($par[0] == $node1 && $par[1] == $node2){
                return true;
            }elseif($par[0] == $node2 && $par[1] == $node1){
                return true;
            }
        }

        return false;
    }

    /**
     * @param $roads
     * @return array
     */
    private function xorFunction($roads){
        $returnArray = array();
        foreach ($roads as $key => $road){
            $tmpArray = array();
            for($i = 0; $i < count($road)-1; $i++){
                if(!$this->existsPair($this->tmpContainer, $road[$i], $road[$i+1])){
                    $tmpArray[] = array($road[$i], $road[$i+1]);
                }
            }

            $returnArray[] = new Round($tmpArray, null);
        }

        return $returnArray;
    }

    /**
     * @param $arrayPairs
     * @param $node1
     * @param $node2
     * @return bool
     */
    private function existsPair($arrayPairs, $node1, $node2){
        foreach ($arrayPairs as $pairs){
            if(in_array($node1, $pairs) && in_array($node2, $pairs)){
                return true;
            }
        }

        return false;
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

class NodeList{
    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $source_x;

    /**
     * @var
     */
    private $source_y;

    /**
     * @var
     */
    private $ratio;

    /**
     * NodeList constructor.
     * @param $name
     * @param $source_x
     * @param $source_y
     * @param $ratio
     */
    public function __construct($name, $source_x, $source_y, $ratio)
    {
        $this->name = $name;
        $this->source_x = $source_x;
        $this->source_y = $source_y;
        $this->ratio = $ratio;
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
    public function getSourceX()
    {
        return $this->source_x;
    }

    /**
     * @param mixed $source_x
     */
    public function setSourceX($source_x)
    {
        $this->source_x = $source_x;
    }

    /**
     * @return mixed
     */
    public function getSourceY()
    {
        return $this->source_y;
    }

    /**
     * @param mixed $source_y
     */
    public function setSourceY($source_y)
    {
        $this->source_y = $source_y;
    }

    /**
     * @return mixed
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param mixed $ratio
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
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
    private $listBuildings;

    /**
     * @var array
     */
    private $listParking;

    /**
     * MapsNetwork constructor.
     */
    public function __construct()
    {
        $this->mapsNetwork = [];
        $this->listBuildings = [];
        $this->listParking = [];
    }

    /**
     * @return array
     */
    public function getMapsNetwork()
    {
        return $this->mapsNetwork;
    }

    /**
     * @return array
     */
    public function getListBuildings()
    {
        return $this->listBuildings;
    }

    /**
     * @return array
     */
    public function getListParking()
    {
        return $this->listParking;
    }

    public function setListElement($name, $source, $rate){
        list($source_x, $source_y) = explode(",", $source);
        $firstCharName = substr($name, 0, 1);

        for($i = 1; $i <= $rate; $i++){
            $newName = $name."_".$i;
       // $newName = $name;
            $node = new NodeList($newName, $source_x, $source_y, $rate);

            if($firstCharName == "B"){
                $this->listBuildings[$newName] = $node;
            }elseif($firstCharName == "P"){
                $this->listParking[$newName] = $node;
            }
        }
    }

    public function createMapsNetwork(){
        foreach ($this->listBuildings as $building){
            foreach ($this->listParking as $parking){
                $ratio = abs($building->getSourceX() - $parking->getSourceX()) + abs($building->getSourceY() - $parking->getSourceY());
                $this->mapsNetwork[$building->getName()][$parking->getName()] = new Node($building->getName(), $parking->getName(), $ratio);
                $this->mapsNetwork[$parking->getName()][$building->getName()] = new Node($parking->getName(), $building->getName(), $ratio);
            }
        }
    }

    /**
     * @param $parent
     * @param $name
     * @return mixed
     */
    public function getNode($parent, $name){
        return $this->mapsNetwork[$parent][$name];
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

            $this->lineProcessing($line);
        }

        //print_r($this->MapsNetwork->getListBuildings());
        //print_r($this->MapsNetwork->getListParking());

        $this->MapsNetwork->createMapsNetwork();
        //print_r($this->MapsNetwork->getMapsNetwork());
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function removeWhiteSpace($string){
        //Odeberu bile znaky na zacatku a na konci retezce
        $string = trim($string);

        //Odeberu bile znaky za :
        $string = preg_replace('/\s*:\s*/',":", $string);

        return $string;
    }


    private function lineProcessing($line){
        list($bind, $rating) = explode(":", $line);
        list($name, $source) = explode(" ", $bind);

        $this->MapsNetwork->setListElement($name, $source, $rating);
    }
}
?>