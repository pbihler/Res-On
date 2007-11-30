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
     
   $class_mapping = array('MainConfig' => "config/main.conf.php",
   						    'FPDF'       => "includes/fpdf/fpdf.php",
   						    'gnuPG'		 => "includes/gnuPG_class.inc",
   						    'Properties' => "includes/Properties.php"
   );
      
   // Handle exceptions:   
   	if (isset($class_mapping[$class_name]))
   		$class_name = $class_mapping[$class_name];
   	else
       	$class_name .= '.php';
  
  // Load classfile:
   require_once  $class_name;
}
?>
