<?php
/**
 * Predmet: TGR
 * Ukol: 1
 * Cast: 3 (chemistry)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine(); //Zpracuji data z STDIN

$analyzer = new Analyzer($parser->getCompoundFirst(), $parser->getCompoundTwo());
$analyzer->runTest();

$printer = new Printer($analyzer->getTestResults());
$printer->printAnalysisResult();

return 0;

class Printer{
    /**
     * @var TestResults
     */
    private $objTestResults;

    public function __construct(TestResults $obj)
    {
        $this->objTestResults = $obj;
    }

    public function printAnalysisResult(){
        foreach ($this->objTestResults->getArrayTestResults() as $result){
            fwrite(STDOUT, $result->getNameTest().": ".$result->getValueTest()."\n");
        }
    }
}

class OneTestResult{
    /**
     * @var null
     */
    private $nameTest;

    /**
     * @var null
     */
    private $valueTest;

    public function __construct()
    {
        $this->nameTest = null;
        $this->valueTest = null;
    }

    /**
     * @return null
     */
    public function getNameTest(){
        return $this->nameTest;
    }

    /**
     * @return null
     */
    public function getValueTest(){
        return $this->valueTest;
    }

    /**
     * @param $string
     */
    public function setNameTest($string){
        $this->nameTest = $string;
    }

    /**
     * @param $value
     */
    public function setValueTest($value){
        if($value === true)
            $this->valueTest = "true";
        elseif($value === false)
            $this->valueTest = "false";
        else
            $this->valueTest = $value;
    }
}

class TestResults{
    const NAME_TEST = [
        "test_1" => "* |U1| = |U2|",
        "test_2" => "* |H1| = |H2|",
        "test_3" => "* Jsou-li u, v sousední uzly, pak i (u), (v) jsou sousední uzly",
        "test_4" => "* Grafy mají stejnou posloupnost stupňů uzlů",
        "test_5" => "* Pak pro každý uzel v z U platí\n  – stupeň uzlu v je roven stupni uzlu φ(v)",
        "test_6" => "  – množina stupňů sousedů uzlu v je rovna množině stupňů sousedů",
        "test_7" => "* Pak pro každý sled platí\n  – obraz sledu je opět sled",
        "test_8" => "  – obraz tahu je opět tah",
        "test_9" => "  – obraz cesty je opět cesta",
        "test_10" => "  – délka sledu zůstává zachována",
    ];

    /**
     * @var array
     */
    private $arrayTestResults;

    public function __construct()
    {
        $this->arrayTestResults = array();
    }

    /**
     * @return array
     */
    public function getArrayTestResults(){
        return $this->arrayTestResults;
    }

    /**
     * @param $nameTest
     * @param $value
     */
    public function setArrayTestResult($nameTest, $value){
        $objOneTestResult = new OneTestResult();
        $objOneTestResult->setNameTest(self::NAME_TEST[$nameTest]);
        $objOneTestResult->setValueTest($value);

        $this->arrayTestResults[$nameTest] = $objOneTestResult;
    }

    public function getArrayTestResult($typeTest){
        return $this->arrayTestResults[$typeTest]->getValueTest();
    }
}

class Analyzer{
    /**
     * @var Compound
     */
    private $CompoundFirst;

    /**
     * @var Compound
     */
    private $CompoundTwo;

    /**
     * @var TestResults
     */
    private $TestResults;

    public function __construct(Compound $CompoundFirst, Compound $CompoundTwo)
    {
        $this->CompoundFirst = $CompoundFirst;
        $this->CompoundTwo = $CompoundTwo;
        $this->TestResults = new TestResults();
    }

    /**
     * @return TestResults
     */
    public function getTestResults(){
        return $this->TestResults;
    }

    public function runTest(){
        $this->test1();
        $this->test2();
        $this->test3();
        $this->test4();
        $this->test5();
        $this->test6();
        $this->test7();
        $this->test8();
        $this->test9();
        $this->test10();
    }

    private function test1(){
        if(count($this->CompoundFirst->getArrayElements()) == count($this->CompoundTwo->getArrayElements())){
            $this->TestResults->setArrayTestResult("test_1", true);
        }else{
            $this->TestResults->setArrayTestResult("test_1", false);
        }
    }

    private function test2(){
        if($this->CompoundFirst->countTies() === $this->CompoundTwo->countTies()){
            $this->TestResults->setArrayTestResult("test_2", true);
        }else{
            $this->TestResults->setArrayTestResult("test_2", false);
        }
    }

    private function test3(){
        if($this->checkNeighbors()){
            $this->TestResults->setArrayTestResult("test_3", true);
        }else{
            $this->TestResults->setArrayTestResult("test_3", false);
        }
    }

    private function test4(){
        if($this->compareSequenceTies()){
            $this->TestResults->setArrayTestResult("test_4", true);
        }else{
            $this->TestResults->setArrayTestResult("test_4", false);
        }
    }

    private function test5(){
        if($this->countDegreeElements()){
            $this->TestResults->setArrayTestResult("test_5", true);
        }else{
            $this->TestResults->setArrayTestResult("test_5", false);
        }
    }

    private function test6(){
        if($this->countSetOfNeighborElements()){
            $this->TestResults->setArrayTestResult("test_6", true);
        }else{
            $this->TestResults->setArrayTestResult("test_6", false);
        }
    }

    private function test7(){
        $this->TestResults->setArrayTestResult("test_7", false);
    }

    private function test8(){
        $this->TestResults->setArrayTestResult("test_8", false);
    }

    private function test9(){
        $this->TestResults->setArrayTestResult("test_9", false);
    }

    private function test10(){
        $this->TestResults->setArrayTestResult("test_10", false);
    }

    /**
     * @return bool
     */
    private function checkNeighbors(){
        $value = true;
        $tmpCompoundTies = $this->CompoundTwo->getArrayTies();

        foreach ($this->CompoundFirst->getArrayTies() as $key => $ties){
            foreach ($ties as $tie){
                if((isset($tmpCompoundTies[$key]) && !in_array($tie, $tmpCompoundTies[$key])) &&
                    (isset($tmpCompoundTies[$tie]) && !in_array($key, $tmpCompoundTies[$tie]))){
                    $value = false;
                    break;
                }
            }

            if(!$value)
                break;
        }

        return $value;
    }

    /**
     * @return bool
     */
    private function compareSequenceTies(){
        $compoundSequenceNodesOne = $this->CompoundFirst->countElementTies();
        $compoundSequenceNodesTwo = $this->CompoundTwo->countElementTies();

        sort($compoundSequenceNodesOne);
        sort($compoundSequenceNodesTwo);

        if($compoundSequenceNodesOne == $compoundSequenceNodesTwo){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @return bool
     */
    private function countDegreeElements(){
        if($this->getTestResults()->getArrayTestResult("test_1") != true && $this->getTestResults()->getArrayTestResult("test_2") != true)
            return false;

        foreach ($this->CompoundFirst->getArrayElements() as $nameElement){
            $countFirst = $this->CompoundFirst->countDegreeElement($nameElement);
            $countTwo = $this->CompoundTwo->countDegreeElement($nameElement);

            if($countFirst != $countTwo){
                return false;
            }
        }

        return true;
    }

    private function countSetOfNeighborElements(){
        $returnValue = true;

        foreach ($this->CompoundFirst->getArrayElements() as $nameElement){
            $countFirst = $this->CompoundFirst->countNeighborSetElement($nameElement);
            $countTwo = $this->CompoundTwo->countNeighborSetElement($nameElement);

            if($countFirst != $countTwo){
                return false;
            }
        }

        return $returnValue;
    }
}

class Compound{
    const POCET_VAZEB = [
        "H" => 1,
        "O" => 2,
        "B" => 3,
        "C" => 4,
        "N" => 5,
        "S" => 6,
    ];

    /**
     * @var array
     */
    private $arrayTies; //Jednosmerny seznam

    /**
     * @var array
     */
    private $arrayElements;

    public function __construct()
    {
        $this->arrayTies = array();
        $this->arrayElements = array();
    }

    /**
     * @return array
     */
    public function getArrayTies(){
        return $this->arrayTies;
    }

    /**
     * @return array
     */
    public function getArrayElements(){
        return $this->arrayElements;
    }

    /**
     * @param $user1
     * @param $user2
     */
    public function setArrayTiesOneWay($tie1, $tie2){
        $this->arrayTies[$tie1][] = $tie2;
        //$this->arrayTies[$tie2][] = $tie1;

        $this->setArrayElements($tie1);
        $this->setArrayElements($tie2);
    }

    /**
     * @param $element
     */
    public function setArrayElements($element){
        if(!in_array($element, $this->arrayElements)){
            $this->arrayElements[] = $element;
        }
    }

    /**
     * @return int
     */
    public function countTies(){
        $count = 0;

        foreach ($this->arrayTies as $source) {
            $count += count($source);
        }

        return $count;
    }

    /**
     * @param $string
     * @return string
     */
    public function mappingElement($string){
        $tmpElement = substr($string, 0, 1);
        $tmpIndex = substr($string, 1);

        if(!is_numeric($tmpIndex)){
            return $tmpElement.$this->myMapAplhaToNumber($tmpIndex);
        }

        return $string;
    }

    /**
     * @param $key
     * @return int
     */
    private function myMapAplhaToNumber($key) {
        $arrayMap = [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5,
            "f" => 6,
            "g" => 7,
            "h" => 8,
            "i" => 9,
            "j" => 10,
            "k" => 11,
            "l" => 12,
            "m" => 13,
            "n" => 14,
            "o" => 15,
            "p" => 16,
            "q" => 17,
            "t" => 18,
            "u" => 19,
            "v" => 20,
            "w" => 21,
            "x" => 22,
            "y" => 23,
            "z" => 24,
        ];

        return $arrayMap[$key];
    }


    /**
     * @return array
     */
    public function countElementTies(){
        $returnArray = [];

        foreach ($this->getArrayElements() as $element){
            $element = substr($element, 0, 1);
            switch ($element){
                case "H":
                    $returnArray[] = 1;
                    break;
                case "O":
                    $returnArray[] = 2;
                    break;
                case "B":
                    $returnArray[] = 3;
                    break;
                case "C":
                    $returnArray[] = 4;
                    break;
                case "N":
                    $returnArray[] = 5;
                    break;
                case "S":
                    $returnArray[] = 6;
                    break;
                default:
                    break;
            }
        }

        return $returnArray;
    }

    /**
     * @param $element
     * @return int
     */
    public function countDegreeElement($element){
        $count = 0;

        foreach ($this->arrayTies as $key => $values){
            if($key == $element){
                foreach ($this->arrayTies[$element] as $oneElement){
                    $count += $this->getPocetVazeb(substr($oneElement, 0, 1));
                }
            }elseif(in_array($element, $values)){
                $count += $this->getPocetVazeb(substr($key, 0, 1));
            }
        }

        return $count;
    }

    /**
     * @param $prvek
     * @return mixed
     */
    private function getPocetVazeb($prvek){
        return self::POCET_VAZEB[$prvek];
    }

    /**
     * @param $element
     * @return array
     */
    public function countNeighborSetElement($element){
        $arrayNeighbors = [];

        foreach ($this->arrayTies as $tieKey => $tieValues){
            if($tieKey == $element){
                foreach ($tieValues as $tieValue){
                    $arrayNeighbors[] = $this-> countDegreeElement($tieValue);
                }
            }elseif(in_array($element, $tieValues)){
                $arrayNeighbors[] = $this-> countDegreeElement($tieKey);
            }
        }

        sort($arrayNeighbors);
        return $arrayNeighbors;
    }
}

class Parser{
    /**
     * @var Compound
     */
    private $CompoundFirst;

    /**
     * @var Compound
     */
    private $CompoundTwo;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->CompoundFirst = new Compound();
        $this->CompoundTwo = new Compound();
    }


    /**
     *
     */
    public function readLine()
    {
        $i = 1;
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            $line = $this->removeWhiteSpace($line); //Odeberu bílé znaky

            if($i == 1) {
                $this->getTiesCompound($line, $this->CompoundFirst);
            }else{
                $this->getTiesCompound($line, $this->CompoundTwo);
            }

            $i++;
        }

        //print_r($this->CompoundFirst->getArrayTies());
        //print_r($this->CompoundFirst->getArrayElements());
       //print_r($this->CompoundTwo->getArrayTies());
       // print_r($this->CompoundTwo->getArrayElements());
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

        //Odeberu bile znaky za ,
        $string = preg_replace('/\s*,\s*/',",", $string);

        return $string;
    }

    /**
     * @return Compound
     */
    public function getCompoundFirst(){
        return $this->CompoundFirst;
    }

    /**
     * @return Compound
     */
    public function getCompoundTwo(){
        return $this->CompoundTwo;
    }

    /**
     * @param $string
     * @param Compound $obj
     */
    private function getTiesCompound($string, Compound $obj){
        $arrayTies = explode(",", $string);

        foreach ($arrayTies as $ties){
            list($tie1, $tie2) = explode("-", $ties);

            $tie1 = $obj->mappingElement($tie1);
            $tie2 = $obj->mappingElement($tie2);
            $obj->setArrayTiesOneWay($tie1, $tie2);
        }
    }
}
?>