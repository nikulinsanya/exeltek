<?php defined('SYSPATH') or die('No direct script access.');

class Database_Mongo {
    
    /**
    * @var MongoDB
    */
    static private $db = false;

    /**
    * @var MongoClient
    */
    static private $client = false;
    
    static private function init() {
        if (self::$db) return;
        
        $config = Kohana::$config->load('database')->get('mongo');
        
        self::$client = new MongoClient($config['host']);
        self::$db = self::$client->selectDB($config['database']);
    }
    
    static public function collection($name) {
        self::init();
        
        return self::$db->selectCollection($name);
    }
}
