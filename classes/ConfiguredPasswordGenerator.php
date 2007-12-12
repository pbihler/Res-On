<?php
/*
 * Created on 22.03.2007 by bihler
 *
 * Generates Passwords based on main.conf.php
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class ConfiguredPasswordGenerator extends PasswordGenerator {
  
     private $generator;
     function __construct() {
         $this->generator = new PasswordGenerator();
     }
     
     public function generatePassword(){
 	    return $this->generator->generatePassword(Config::$pwd_gen_params['length'],
 	    		        						  Config::$pwd_gen_params['includeNumbers'],
 	    				       				      Config::$pwd_gen_params['includeLowerLetters'],
 	    								          Config::$pwd_gen_params['includeUpperLetters'],
 	    								          Config::$pwd_gen_params['excludeList']);
 	 }
     
 }
?>
