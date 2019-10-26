<?php
/**
 * Predmet: TGR
 * Ukol: 1
 * Cast: 2 (fusion)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

const NENI_UZEL = 0;
const JE_UZEL = 1;

$parser = new Parser();
$parser->readLine();

$executor = new Executor($parser->getFirmOne(), $parser->getFirmTwo());
$executor->compareMatrix();


$printer = new Printer($executor);
$printer->printPaths();
$printer->printDeletePaths();

return 0;

class Printer{
    /**
     * @var array
     */
    private $arrayOut;

    /**
     * @var array
     */
    private $deletePaths;

    /**
     * Printer constructor.
     * @param Executor $executor
     */
    public function __construct(Executor $executor)
    {
        $this->arrayOut = $executor->getFirmOut();
        $this->deletePaths = $executor->getDeletePaths();
    }

    public function printPaths(){
        foreach ($this->arrayOut as $key => $paths){
            foreach ($paths as $path){
                fwrite(STDOUT, $key." -> ".$path."\n");
            }
        }
    }

    public function printDeletePaths(){
        fwrite(STDOUT, "----\n");
        foreach ($this->deletePaths as $path){
            fwrite(STDOUT, $path."\n");
        }
    }
}

class Executor {
    private $firmOne;
    private $firmTwo;
    private $firmOut;
    private $deletePaths;

    /**
     * Executor constructor.
     * @param Firm $firmOne
     * @param Firm $firmTwo
     */
    public function __construct(Firm $firmOne, Firm $firmTwo){
        $this->firmOne = $firmOne;
        $this->firmTwo = $firmTwo;
        $this->firmOut = array();
        $this->deletePaths = array();
    }

    /**
     * @return array
     */
    public function getFirmOut(){
        return $this->firmOut;
    }

    /**
     * @return array
     */
    public function getDeletePaths(){
        return $this->deletePaths;
    }

    /**
     *
     */
    public function compareMatrix(){
        $this->firmOut = $this->firmOne->getMatrixPaths();

        //print_r($this->firmOut );

        foreach ($this->firmTwo->getMatrixPaths() as $key => $paths){
            foreach ($paths as $path) {
                //Pokud vychozi mesto vubec neexistuje v cestach
                if (!array_key_exists(strtolower($key), $this->firmOut) && !array_key_exists(strtoupper($key), $this->firmOut)) {
                    $this->firmOut[$key][] = $path;
                } elseif (array_key_exists(strtoupper($key), $this->firmOut)) {//Pokud existuje mesto pojmenovane velkymi pismeny
                    $this->saveKeyInArray(strtoupper($key), $path, $key);
                } elseif (array_key_exists(strtolower($key), $this->firmOut)) {//Pokud existuje mesto pojmenovane malymi pismeny
                    $this->saveKeyInArray($path, strtolower($key), $key);
                }
            }
        }

        //print_r($this->firmOut);
        //print_r($this->deletePaths);
    }

    /**
     * @param $key
     * @param $path
     * @param $keyOrigin
     */
    private function saveKeyInArray($key, $path, $keyOrigin){
        if(!in_array(strtolower($path), $this->firmOut[$key]) && !in_array(strtoupper($path), $this->firmOut[$key])){
            $this->firmOut[$key][] = $path;
        }else{
            $this->deletePaths[] = $keyOrigin." -> ".$path;
        }
    }
}

class Firm {
    private $destinations;
    private $matrixPaths;

    public function __construct()
    {
        $this->destinations = array();
        $this->matrixPaths = array();
    }

    /**
     * @param array $destionations
     */
    public function setDestination(array $destionations){
        $this->destinations = $destionations;
    }

    /**
     * @return array
     */
    public function getDestinations(){
        return $this->destinations;
    }

    /**
     * @param array $matrixPaths
     */
    public function setMatrixPaths(array $matrixPaths){
        $this->matrixPaths = $matrixPaths;
    }

    /**
     * @return array
     */
    public function getMatrixPaths(){
        return $this->matrixPaths;
    }

    /**
     * @param $radek
     * @param $sloupec
     */
    public function setOnePath($radek, $sloupec){
        $this->matrixPaths[$radek][] = $sloupec;
    }
}

class Parser{
    /**
     * @var Firm
     */
    private $firmOne;

    /**
     * @var Firm
     */
    private $firmTwo;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->firmOne = new Firm();
        $this->firmTwo = new Firm();
    }

    public function readLine()
    {
        $i = 1;
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            $line = $this->removeWhiteSpace($line);

            if($i == 1) {
                $this->firmOne->setDestination($this->getCities($line));
            }elseif($i == 2){
                $this->firmTwo->setDestination($this->getCities($line));
            }elseif(strlen($line)){
                if(($line[0] >= "A" && $line[0] <= "Z") || ($line[0] >= "Á" && $line[0] <= "Ž"))
                    $this->savePath($line, $this->firmOne);
                else
                    $this->savePath($line, $this->firmTwo);
            }

            $i++;
        }
    }

    /**
     * @return Firm
     */
    public function getFirmOne(){
        return $this->firmOne;
    }

    /**
     * @return Firm
     */
    public function getFirmTwo(){
        return $this->firmTwo;
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function removeWhiteSpace($string){
        //Odeberu bile znaky na zacatku a na konci retezce
        $string = trim($string);

        //Odeberu bile mezery mezi slovem a ->
        $string = preg_replace('/\s*->\s*/',"->", $string);

        //Odeberu bile znaky za ,
        $string = preg_replace('/\s*,\s*/',",", $string);

        return $string;
    }

    /**
     * @param $string
     * @return array
     */
    private function getCities($string){
        return explode(",", $string);
    }

    /**
     * @param $string
     * @param Firm $obj
     */
    private function savePath($string, Firm $obj){
        list($radek, $sloupec) = explode("->", $string);

        $obj->setOnePath($radek, $sloupec);
    }
}
?>