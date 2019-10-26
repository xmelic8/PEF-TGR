<?php
/**
 * Predmet: TGR
 * Ukol: 1
 * Cast: 1 (information)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$analyzer = new Analyzer($parser->getFriends());
$analyzer->countFriendsForUsers();
$analyzer->getLargestRangeOfThreeUsers();

$printer = new Printer();
$printer->printStatisticsFriendsUsers($analyzer->getArrayCountFriendsForUsers());
$printer->printThreeInsurens($analyzer->getNameTop3Users(), $analyzer->getCountRangeUsers());

return 0;

class Printer{

    public function printStatisticsFriendsUsers(array $data){
        fwrite(STDOUT, "Task 1:\n");
        foreach ($data as $name => $countFriends){
            fwrite(STDOUT, $name." (".$countFriends.")"."\n");
        }
    }

    public function printThreeInsurens($names, $count){
        fwrite(STDOUT, "\nTask 2:\n");

        fwrite(STDOUT, $names." (".$count.")\n");
    }
}

class Analyzer{

    /**
     * @var Friends
     */
    private $objFriends;

    /**
     * @var array
     */
    private $arrayCountFriendsForUsers;

    /**
     * @var int
     */
    private $countRangeUsers;

    /**
     * @var string
     */
    private $nameTop3Users;

    /**
     * Analyzer constructor.
     * @param Friends $objFriends
     */
    public function __construct(Friends $objFriends)
    {
        $this->objFriends = $objFriends;
        $this->arrayCountFriendsForUsers = array();
        $this->countRangeUsers = 0;
        $this->nameTop3Users = "";
    }

    /**
     * @return int
     */
    public function getCountRangeUsers()
    {
        return $this->countRangeUsers;
    }

    /**
     * @return mixed
     */
    public function getNameTop3Users()
    {
        return $this->nameTop3Users;
    }

    /**
     * @return array
     */
    public function getArrayCountFriendsForUsers(){
        return $this->arrayCountFriendsForUsers;
    }

    public function countFriendsForUsers(){
        foreach ($this->objFriends->getArrayFriends() as $name => $friends){
           $this->arrayCountFriendsForUsers[$name] = count($friends);
        }

        arsort($this->arrayCountFriendsForUsers);
    }

    private function sortArrayRangeUsers($data) {
        uasort($data, function ($a, $b) {
            return count($a) < count($b);
        });

        return $data;
    }

    public function getLargestRangeOfThreeUsers(){
        $tmpData = $this->sortArrayRangeUsers($this->objFriends->getArrayFriends());

        $tmpTop3Users = array_slice($tmpData, 0, 3);
        list($userOne, $userTwo, $userThree) = array_keys($tmpTop3Users);
        $this->nameTop3Users = $userOne.", ".$userTwo.", ".$userThree;

        $top3Users = $tmpTop3Users[$userOne];
        $tmpTop3Users = array_merge($tmpTop3Users[$userTwo], $tmpTop3Users[$userThree]);
        $top3Users = array_unique(array_merge($top3Users, $tmpTop3Users));

        foreach ($top3Users as $key => $value){
            if($value == $userOne || $value == $userTwo || $value == $userThree){
                unset($top3Users[$key]);
            }
        }

        $this->countRangeUsers = count($top3Users);
    }
}


class Friends {
    /**
     * @var array
     */
    private $arrayFriends;

    /**
     * @var array
     */
    private $nameUsers;

    public function __construct()
    {
        $this->arrayFriends = array();
        $this->nameUsers = array();
    }

    /**
     * @param $user1
     * @param $user2
     */
    public function setArrayFriends($user1, $user2){
        if(in_array($user1, $this->nameUsers)) {
            $this->arrayFriends[$user1][] = $user2;
        }
        if(in_array($user2, $this->nameUsers)) {
            $this->arrayFriends[$user2][] = $user1;
        }
    }

    /**
     * @return array
     */
    public function getArrayFriends(){
        return $this->arrayFriends;
    }

    /**
     * @return mixed
     */
    public function getNameUsers(){
        return $this->nameUsers;
    }

    /**
     * @param array $data
     */
    public function setNameUsers(array $data){
        $this->nameUsers = $data;
    }
}

class Parser{
    /**
     * @var Friends
     */
    private $objFriends;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->objFriends = new Friends();
    }

    public function readLine()
    {
        $i = 1;
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            $line = $this->removeWhiteSpace($line);

            if($i == 1){
                $this->objFriends->setNameUsers($this->getUsers($line));
            }else{
                $this->getRelationshipUsers($line, $this->objFriends);
            }

            $i++;
        }
    }

    /**
     * @return Friends
     */
    public function getFriends(){
        return $this->objFriends;
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
     * @param $string
     * @return array
     */
    private function getUsers($string){
        return explode(",", $string);
    }

    /**
     * @param $string
     * @param Friends $obj
     */
    private function getRelationshipUsers($string, Friends $obj){
        list($user1, $user2) = explode("-", $string);

        $obj->setArrayFriends($user1, $user2);
    }
}
?>