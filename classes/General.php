<?php 
/*
 * Created on 26.01.2009
 *
 * Used for general help functions
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class General {
  	 /**
      * Checks, whether a master-passwort (transmitted via POST) ist correct or not (null, if not transmitted)
      */
     public function CheckPostMasterPassword($master_pwd = '') {
        if ($master_pwd == '') {
     	  if (! isset($_POST['master_pwd'])) 
     	    return null;
     	  $master_pwd = $_POST['master_pwd'];
        }
     	if (Config::$master_salt)
     		return crypt($master_pwd,Config::$master_salt) == Config::$master_password;
     	else
     		return $master_pwd == Config::$master_password;
     	  
     } 
 }

?>