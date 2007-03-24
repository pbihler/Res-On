<?php
/*
 * Extended on 24.03.2007 by bihler
 *
 * Based on code from http://php.net/manual/en/language.oop5.patterns.php by  eyvindh79 at gmail dot com
 * 
 */
 
class Singleton {

   /***********************
     * HOW TO USE
     *
     * Inherit(extend) from Singleton and add getter:
     *
     *  //public getter for singleton instance
     *    public static function getInstance(){
     *        return Singleton::getSingleton(get_class());
     *    }
     *
     */
  
   private static $instanceMap = array();

   /**
    * protected getter for singleton instances
    * @param The name of the class to create
    * @param a default instanceof the class
    */
   protected static function getSingleton($className,$object = null){
       if(!isset(self::$instanceMap[$className])){
          
           if (! isset($object)) 
           		$object = new $className;
           elseif (! $object instanceof $className)
           	   throw SingletonException("Object is not an instance of '$className'");
         
           //Make sure this object inherit from Singleton
           if($object instanceof Singleton){   
               self::$instanceMap[$className] = $object;
           }
           else{
               throw SingletonException("Class '$className' do not inherit from Singleton!");
           }
       }
      
       return self::$instanceMap[$className];
   }   
   
   //protected constructor to prevent outside instantiation
   protected function __construct(){
   }
  
   //denie cloning of singleton objects
   public final function __clone(){
       trigger_error('It is impossible to clone singleton', E_USER_ERROR);
   }   
}
?>
