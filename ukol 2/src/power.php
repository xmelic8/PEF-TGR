<?php
/**
 * Predmet: TGR
 * Ukol: 2
 * Cast: 1 (power)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getMapsNetwork());
$analyzer->analyzeNetwork();

$printer = new Printer($analyzer);
$printer->printResult();


class Printer{
    /**
     * @var Analyzer
     */
    private $Analyzer;

    /**
     * Printer constructor.
     */
    public function __construct(Analyzer $analyzer)
    {
        $this->Analyzer = $analyzer;
    }

    public function printResult(){
        if($this->Analyzer->isStatusNetwork()){
            fwrite(STDOUT, "Stav site OK\n");
        }else{
            fwrite(STDOUT, "Stav site ERROR\n");
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


}

class BFS{
    public function BFSAnalyzeBool($graph, $start, $end) {
        $queue = new SplQueue(); //Vytvoreni fronty

        $queue->enqueue($start); //Pridani elementu do fronty
        $visited = [$start];

        while ($queue->count()) {
            $node = $queue->dequeue(); //Odeberu element z fronty
            if ($node === $end) { //Našel cestu
                return true;
            }

            foreach ($graph[$node] as $neighbour) { //Prochazim uzly, ktere jsou ve vazbe
                if (!in_array($neighbour->getName(), $visited)) { //Pokud jsem ho jiz navstivil jdu dal
                    $visited[] = $neighbour->getName();
                    $queue->enqueue($neighbour->getName());
                }
            };
        }
        return false;
    }
}

class Analyzer{
    /**
     * @var MapsNetwork
     */
    private $MapsNetwork;

    /**
     * @var bool
     */
    private $statusNetwork;

    /**
     * Analyzer constructor.
     * @param $MapsNetwork
     */
    public function __construct(MapsNetwork $MapsNetwork)
    {
        $this->MapsNetwork = $MapsNetwork;
        $this->statusNetwork = true;
    }

    /**
     * @return bool
     */
    public function isStatusNetwork()
    {
        return $this->statusNetwork;
    }


    public function analyzeNetwork(){
        $BFS = new BFS();
        $listNameNode = $this->MapsNetwork->getListNameNode();
        $limitArray = count($this->MapsNetwork->getListNameNode())-1;

        for ($i = 0; $i < $limitArray; $i++){
            for ($j = $i + 1; $j <= $limitArray; $j++){
                if(!$BFS->BFSAnalyzeBool($this->MapsNetwork->getMapsNetwork(), $listNameNode[$i], $listNameNode[$j])){
                   $this->statusNetwork = false;
                   break;
               }
            }


            if($this->statusNetwork == false){
                break;
            }
        }
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