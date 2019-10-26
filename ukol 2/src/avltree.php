<?php
/**
 * Predmet: TGR
 * Ukol: 2
 * Cast: 4 (avltree)
 * Datum: 2019
 * Autor: Michal Melichar
 * email: xmelich8@mendelu.cz
 */

error_reporting(0);

$parser = new Parser();
$parser->readLine();

$printer = new Printer($parser->getResultArray(), $parser->getAVLTReeMaxRating());
$printer->printResult();


class Printer{
    /**
     * @var array
     */
    private $levelOrderTraversalArray;

    /**
     * @var
     */
    private $maxRating;

    /**
     * @var array
     */
    private $resultArray;

    public function __construct(array $levelOrderTraversalArray, $maxRating)
    {
        $this->levelOrderTraversalArray = $levelOrderTraversalArray;
        $this->maxRating = $maxRating;
        $this->resultArray = array();

        $this->processResult();
    }

    private function processResult(){
        foreach ($this->levelOrderTraversalArray as $key => $values) {
            $lenghtLine = count($values);
            $powNumber = 0;
            $startPosition = 0;

            while (($startPosition+pow(2, $powNumber)) < $lenghtLine){
                $this->resultArray[$key][] = array_slice($values, $startPosition, pow(2, $powNumber));

                $startPosition += pow(2, $powNumber);

                $powNumber++;
            }
        }
    }

    public function printResult(){
        for ($i = 0; $i < count($this->resultArray); $i++){
            $countNodes = count($this->resultArray[$i]);

            for ($j = 0; $j < $countNodes; $j++){
                $countChildren = count($this->resultArray[$i][$j]);

                for ($k = 0; $k < $countChildren; $k++){
                    fwrite(STDOUT, $this->resultArray[$i][$j][$k]);

                    if($k < $countChildren-1){
                        fwrite(STDOUT, ",");
                    }
                }

                if($j < $countNodes-1){
                    fwrite(STDOUT, "|");
                }
            }

            fwrite(STDOUT, "\n");
        }
    }
}

class Node {
    /**
     * @var
     */
    private $value;

    /**
     * @var int
     */
    private $rating;

    /**
     * @var Node
     */
    private $left;

    /**
     * @var Node
     */
    private $right;

    /**
     * Node constructor.
     * @param $value
     * @param Node|null $left
     * @param Node|null $right
     * @param int $height
     */
    function __construct($value, $rating = null, Node $left = null, Node $right = null) {
        $this->value = $value;
        $this->left = $left;
        $this->right = $right;

        if($rating == null){
            $this->rating = 1;
        }else{
            $this->rating = $rating;
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
     * @return Node
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param Node $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * @return Node
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param Node $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * @return int
     */
    public function getRatingLeftChild(){
        if($this->left !== null){
            return $this->left->getRating();
        }else{
            return 0;
        }
    }

    /**
     * @return int
     */
    public function getRatingRightChild(){
        if($this->right !== null){
            return $this->right->getRating();
        }else{
            return 0;
        }
    }

    /**
     * @return int
     */
    public function balancIndex(){
        $index = $this->getRatingLeftChild() - $this->getRatingRightChild();

        return $index;
    }

    /**
     * @return int
     */
    public function balancIndexLeftChild(){
        if($this->left !== null){
            return $this->left->balancIndex();
        }else{
            return 0;
        }
    }

    /**
     * @return int
     */
    public function balancIndexRightChild(){
        if($this->right !== null){
            return $this->right->balancIndex();
        }else{
            return 0;
        }
    }

    /**
     * @return Node
     */
    public function rotationNodeRight(){
        $maxRating = max($this->checkAndGetRating($this->left->right), $this->checkAndGetRating($this->right)) + 1;
        $newNode = new Node($this->value, $maxRating, $this->left->right, $this->right);

        $newObjNode = new Node($this->left->value, max($maxRating, $this->checkAndGetRating($this->left->left)) + 1, $this->left->left, $newNode);

        return $newObjNode;
    }

    /**
     * @return Node
     */
    public function rotationNodeLeft(){
        $maxRating = max($this->checkAndGetRating($this->left), $this->checkAndGetRating($this->right->left)) + 1;
        $newNode = new Node($this->value, $maxRating, $this->left, $this->right->left);

        $newObjNode = new Node($this->right->value, max($maxRating, $this->checkAndGetRating($this->right->right)) + 1, $newNode, $this->right->right);

        return $newObjNode;
    }

    /**
     * @param Node $node
     * @return int
     */
    public function checkAndGetRating($node){
        if($node === null){
            return 0;
        }else{
            $node->getRating();
        }
    }
}

class AVLTree {
    /**
     * @var Node
     */
    private $objNodeTree;

    /**
     * AVLTree constructor.
     */
    public function __construct()
    {
        $this->objNodeTree = null;
    }

    /**
     * @param $valueNode
     */
    public function addNode($valueNode){
        $this->objNodeTree = $this->insertRecursion($this->objNodeTree, $valueNode);
    }

    /**
     *
     * @return Node
     */
    public function getObjNode()
    {
        return $this->objNodeTree;
    }

    /**
     * Rekurzivne vlozime prvek na prazdne misto v leve nebo v prave casti rodice.
     * @param $parent
     * @param $value
     * @return Node
     */
    private function insertRecursion($parent, $valueNode) {
        if ($parent === null) {
            $objNew = new Node($valueNode);
        }elseif ($valueNode >= $parent->getValue()){
            $objNew = $this->createNewRightNode($parent, $valueNode);
        }else{
            $objNew = $this->createNewLeftNode($parent, $valueNode);
        }

        return $objNew;
    }

    /**
     * @param Node $parent
     * @param $valueNode
     * @return Node
     */
    private function createNewLeftNode(Node $parent, $valueNode){
        $leftElement = $this->insertRecursion($parent->getLeft(), $valueNode);
        $maxRating = max($leftElement->getRating(), $parent->getRatingRightChild());

        $objNew = $this->balancingTree($this->replaceLeft($parent, $leftElement, ++$maxRating));

        return $objNew;
    }

    /**
     * @param Node $parent
     * @param $valueNode
     * @return Node
     */
    private function createNewRightNode(Node $parent, $valueNode){
        $rightElement = $this->insertRecursion($parent->getRight(), $valueNode);
        $maxRating = max($parent->getRatingLeftChild(), $rightElement->getRating());

        $objNew = $this->balancingTree($this->replaceRight($parent, $rightElement, ++$maxRating));

        return $objNew;
    }

    /**
     * @param Node $node
     * @param Node $new_right
     * @param $height
     * @return Node
     */
    private function replaceRight(Node $node, Node $newRightNode, $rating) {
        if($rating === null){
            $rating = $node->getRating();
        }

        $newNode = new Node($node->getValue(), $rating, $node->getLeft(), $newRightNode);

        return $newNode;
    }

    /**
     * @param Node $node
     * @param Node $newLeftNode
     * @param $rating
     * @return Node
     */
    private function replaceLeft(Node $node, Node $newLeftNode, $rating) {
        if($rating === null){
            $rating = $node->getRating();
        }

        $newNode = new Node($node->getValue(), $rating, $newLeftNode, $node->getRight());

        return $newNode;
    }

    /**
     * @param Node $node
     * @return Node
     */
    private function balancingTree(Node $node){
        $balancIndex = $node->balancIndex();
        $returnObjNodes = null;

        if($node->getLeft() !== null){
            $balancIndexLeft = $node->balancIndexLeftChild();
        }else{
            $balancIndexLeft = 0;
        }
        if($node->getRight() !== null){
            $balancIndexRight = $node->balancIndexRightChild();
        }else{
            $balancIndexRight = 0;
        }

        if($balancIndex === -2) {
            if ($balancIndexRight === 1) {
                $tmpNode = $node->getRight()->rotationNodeRight();
                $tmpNode = $this->replaceRight($node, $tmpNode, null);
                $tmpNode = $tmpNode->rotationNodeLeft();
                return $tmpNode;
            }elseif($balancIndexRight === -1) {
                return $node->rotationNodeLeft();
            }
        }elseif($balancIndex === 2) {
            if ($balancIndexLeft === -1) {
                $tmpNode = $node->getLeft()->rotationNodeLeft();
                $tmpNode = $this->replaceLeft($node, $tmpNode,  null);
                $tmpNode = $tmpNode->rotationNodeRight();
                return $tmpNode;
            }elseif($balancIndexLeft === 1) {
                return $node->rotationNodeRight();
            }
        }else{
            $returnObjNodes = $node;
        }

        return $returnObjNodes;
    }

    /**
     * @return int
     */
    public function getMaxRating(){
        if($this->objNodeTree === null){
            return 0;
        }else{
            return $this->objNodeTree->getRating();
        }
    }
}

class Parser{
    /**
     * @var AVLTree
     */
    private $AVLTree;

    /**
     * @var array
     */
    private $resultArray;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->AVLTree = new AVLTree();
        $this->resultArray = array();
    }

    /**
     * @return AVLTree
     */
    public function getAVLTree()
    {
        return $this->AVLTree;
    }

    /**
     * @return array
     */
    public function getResultArray()
    {
        return $this->resultArray;
    }

    /**
     * @return int
     */
    public function getAVLTReeMaxRating(){
        return $this->AVLTree->getMaxRating();
    }

    public function readLine()
    {
        $lineIndex = 0;
        while (!feof(STDIN)) {
            $line = fgets(STDIN);
            $line = $this->removeWhiteSpace($line);

            $this->lineProcessing($line);
            $this->createResultAnalaysis($this->AVLTree->getObjNode(), $lineIndex++);
        }

        //print_r($this->AVLTree->getObjNode());
        //print_r($this->resultArray);
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    private function removeWhiteSpace($string){
        //Odeberu bile znaky na zacatku a na konci retezce
        $string = trim($string);

        return $string;
    }

    /**
     * @param $line
     */
    private function lineProcessing($line){
        $this->AVLTree->addNode($line);
    }

    private function createResultAnalaysis($AVLTree, $lineIndex){
        if($AVLTree === null){
            return;
        }

        $queueNodes = new SplQueue();
        $queueNodes->enqueue($AVLTree);

        $this->resultArray[$lineIndex][] = $AVLTree->getValue();

        while ($queueNodes->count()) {
            $tmpNode = $queueNodes->dequeue();

            if ($tmpNode->getLeft() !== null) {
                $this->resultArray[$lineIndex][] = $tmpNode->getLeft()->getValue();
                $queueNodes->enqueue($tmpNode->getLeft());
            }else{
                $this->resultArray[$lineIndex][] = "_";
            }

            if ($tmpNode->getRight() !== null) {
                $this->resultArray[$lineIndex][] = $tmpNode->getRight()->getValue();
                $queueNodes->enqueue($tmpNode->getRight());
            }else{
                $this->resultArray[$lineIndex][] = "_";
            }
        }
    }
}