<?php
declare(strict_types = 1);

class Database
{
    protected $_connection;

    private static $_instance = null;

    /** @TODO set DB name */
    const DBNAME = '_';

    /** @TODO set user name */
    const USER = '_';

    /** @TODO set password */
    const PASSWORD = '_';


    private function __construct()
    {
        try {
            $this->_connection = new PDO('mysql:host=localhost;dbname=' . self::DBNAME, self::USER, self::PASSWORD);
        } catch (PDOException $e) {
            print "Ошибка подключения к базе данных: " . $e->getMessage();
        }
    }

    /*
    * получаем экземпляр базы данных
    */
    public static function getInstance()
    {
        if(!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /*
    * запрещаем копирование объекта
    */
    private function __clone() {}

    public function getConnection()
    {
        return $this->_connection;
    }
}

/**
 * Class BinaryNode
 */
class BinaryNode
{
    const LEFT = 1;

    const RIGHT = 2;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var BinaryNode
     */
    public $left = null;

    /**
     * @var BinaryNode
     */
    public $right = null;

    /**
     * @var Database::getInstance();
     */
    private $database;

    /**
     * @param mixed $value
     * @param Database $database
     */
    public function __construct ($value, Database $database)
    {
        $this->value = $value;
        $this->database = $database;
    }

    public function putLeft(int $parent, int $value)
    {
        $this->put($parent, $value, self::LEFT);
    }

    public function putRight(int $parent, int $value)
    {
        $this->put($parent, $value, self::RIGHT);
    }

    private function put(int $parent, int $value, int $position)
    {
        $connection = $this->database->getConnection();
        $sql = "INSERT INTO tree VALUES(parent = :parent, value = :value, position = :position)";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':parent', $parent, PDO::PARAM_INT);
        $stmt->bindValue(':value', $value, PDO::PARAM_INT);
        $stmt->bindValue(':position', $position, PDO::PARAM_INT);
        $stmt->execute();
    }
}

/**
 * Class BinaryTree
 */
class BinaryTree
{
    /**
     * @var BinaryNode
     */
    protected $root = NULL;

    /**
     * @return boolean
     */
    public function isEmpty ()
    {
        return is_null($this->root);
    }

    /**
     * @param mixed $value
     */
    public function insert ($value)
    {
        $node = new BinaryNode($value, Database::getInstance());
        $this->insertNode($node, $this->root);
    }

    /**
     * @param BinaryNode $node
     * @param BinaryNode $subtree
     *
     * @return $this
     */
    protected function insertNode (BinaryNode $node, &$subtree)
    {
        if (is_null($subtree)) {
            $subtree = $node;
        } else {
            if ($node->value < $subtree->value) {
                $this->insertNode($node, $subtree->left);
                $subtree->putLeft($node->value, $subtree->value);
            } elseif ($node->value > $subtree->value) {
                $this->insertNode($node, $subtree->right);
                $subtree->putRight($node->value, $subtree->value);
            }
        }

        return $this;
    }
}
