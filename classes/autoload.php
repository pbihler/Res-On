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
     
   $special_classes = array('MainConfig' => "config/main.conf.php",
   						    'FPDF'       => "includes/fpdf/fpdf.php"
   );
      
   // Handle exceptions:   
   	if (isset($special_classes[$class_name]))
   		$class_name = $special_classes[$class_name];
   	else
       	$class_name .= '.php';
  
  // Load classfile:
   require_once  $class_name;
}
?>
