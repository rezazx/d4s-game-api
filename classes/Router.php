<?php

namespace MRZX;

class Router {

    private static $instance = null;
    private $adminRoutes=array();
    private $publicRoutes=array();

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct()
    {
      // The expensive process (e.g.,db connection) goes here.
    }
   
    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
      if (self::$instance == null)
      {
        self::$instance = new Router();
      }
   
      return self::$instance;
    }

    public function addAdminRoute($route)
    {
        if(gettype($route)!=='string')
            throw new Exception('addAdminRoute: Invalid data type.');
        
        if(in_array($route,$this->adminRoutes))
            throw new Exception('addAdminRoute: This route has already been added.');

        $this->adminRoutes[]=$route;
    }

    public function addPublicRoute($route)
    {
        if(gettype($route)!=='string')
            throw new Exception('addPublicRoute: Invalid data type.');
        
        if(in_array($route,$this->publicRoutes))
            throw new Exception('addPublicRoute: This route has already been added.');

        $this->publicRoutes[]=$route;
    }
    
    public function test()
    {
        echo 'Router is ok';
    }
}