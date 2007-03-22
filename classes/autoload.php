<?php
/*
 * Created on 21.03.2007
 *
  * Autoloader to simplify class loading.
  * Refer to http://php.net/manual/en/language.oop5.autoload.php for details
  * 
  * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
  * 
  */
 function __autoload($class_name) {
      
   // Handle exceptions:   
   if ($class_name == "MainConfig") $class_name = "config/main.conf";
  
  // Load classfile:
   require_once  $class_name . '.php';
}
?>
