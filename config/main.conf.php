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
      * Possible values for $default_crypt_module are "none", "hash", "gpg"
      *  !! Never use "none" on a productive system !!
      *  !!   It means, what it says, your data is  !!
      *  !!   not at all encrypted in the database  !!
      * 
      * "none": Password and result are stored in plain text in the database
      * "hash": Password is hashed, Results are obfuscated (no encryption)
      * "gpg":  
      *  
      */
      public static $default_crypt_module = "hash";
      
      /* 
       * The keys in $crypt_info define the choice when generating new Reson-IDs
       * Remove "'none'' => ''," in productive systems  
       */
      
      public static $crypt_info = array(
        'none' => '',
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
        ),
         // The following information is used when crypt_module is set to "gpg"
      	'gpg' => array(
          'program_path' => 'c:\Program Files\GNU\GnuPG\gpg.exe', //Set the path to gpg executeable
          'keyring_home' => 'keys/%03d', // the subdir has to writeable by the webserver
          'key_type'     => 'DSA', //  type of the key, the allowed values are DSA and RSA
          'key_length'   => 512, // Length of the key in bits
          'subkey_length'   => 512 // Length of the subkey in bits
        )
      );
      
      /*
       * password generation parameters
       */
      public static $pwd_gen_params = array(
      	'length' => 8,
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
          'table_prefix' => ''
      );
      
      // contact e-mail:
      public static $contact_url = "mailto:info@res-on.org";
     
     /*
      * PDF-Generation
      */
      public static $pdf_settings = array(
      	'orientation' => 'P', // 'P' or 'Portrait' /  'L' or 'Landscape'
      	'format' => 'A5',	  // A3, A4, A5, Letter, Legal      					
      );
     
     // The number of results which can be entered simultaniously
     public static $numberOfDataSetsToEnter = 15;
     
     
      //As long as Res-on doesn't support multi-projects, this number needs to be set:
      public static $default_project_id = 1;
     
      //Available languages:
      public static $languages = array(
        'en' => array('name' => 'English',
                      'icon' => 'english_icon.gif'),
        'de' => array('name' => 'Deutsch',
                      'icon' => 'german_icon.gif')
      );
 }
?>
