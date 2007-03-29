<?php
/*
 * The main config holds all parameters for the current instance of Res-On
 * 
 * WARNING:
 * 
 * Please make sure, that this file can only be read by the webserver,
 * and that the directory containing this file contains a .htaccess
 * denying web-access to it!
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 final class MainConfig {
     
     //This should be set to "true", if possible:
     public static $require_ssl = false;
     
     /*
      * Configure the level of security, how secure data is stored
      * in the database (level of encryption)
      * 
      * Possible values for $cryptModule are "none", "hash", "gpg"
      *  !! Never use "none" on a productive system !!
      *  !!   It means, what it says, your data is  !!
      *  !!   not at all encrypted in the database  !!
      *  
      */
      public static $crypt_module = "none";
      
      public static $crypt_info = array(
      
         // The following information is used when crypt_module is set to "hash"
      	'hash' => array(
          /*
           * The salt determines the hash function to use:
           * 
           * Function        Salt
           * CRYPT_STD_DES 	 2-character (Default)
           * CRYPT_EXT_DES 	 9-character
           * CRYPT_MD5 	     12-character beginning with $1$
           * CRYPT_BLOWFISH  16-character beginning with $2$
           */
          'salt' => '$2$kd(jmlokDK8kl' //change this!
        )
      );
      
      /*
       * password generation parameters
       */
      public static $pwd_gen_params = array(
      	'length' => 12,
      	'includeNumbers' => 1,
      	'includeLowerLetters' => 1,      	
      	'includeUpperLetters' => 1
      );
      
      /*
       * Configure the database connection:
       */
      public static $database = array(
          'server' => 'localhost',
          'username' => 'reson_access',
          'password' => 'j/fsOv2,',
          'database' => 'reson',
          'db_prefix' => ''
      );
      
      // contact e-mail:
      public static $contact_url = "mailto:info@res-on.org";
     
      //As long as Res-on doesn't support multi-project, this number needs to be set:
      public static $default_project_id = 1;
     
        
 }
?>
