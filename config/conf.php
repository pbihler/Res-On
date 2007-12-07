<?php
/*
 * The main config holds all parameters for the current instance of Res-On.
 * Do not modify this file directly, but overwrite the configuration values in the file "local.php"
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
 
 class Config {
     
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
          'salt' => '$2$kd(jmlokDK8kl'
        ),
         // The following information is used when crypt_module is set to "gpg"
      	'gpg' => array(
          'program_path' => '', //Set the path to gpg executeable
          'keyring_home' => 'keys/%03d', // the subdir has to be writeable by the webserver
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
          'username' => '',
          'password' => '',
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
     
      public static $default_project_id = 1;
      public static $master_salt = '';
      public static $master_password = ''; // overwrite in conf.local.php! 
      
      public static $default_project_info = array(
      	'info' => '',
      	'access' => true,
      	'introduction'=>"Damit Sie online auf Ihr Klausursergebnis zugreifen k&ouml;nnen, schreiben Sie bitte Ihren pers&ouml;nlichen R-Key <b>%RKEY%</b> auf das Deckblatt Ihrer Klausur. Bitte <b>schreiben Sie das Passwort nicht auf die Klausur!</b>\r\n<br><hr>To enable the possibility to access your results online, please copy your personal R-Key <b>%RKEY%</b> to the cover page of your exam. Please <b>do not write your password anywhere on the exam</b>.\r\n<br>",
      	'hint'=>"<br><i>Zu Ergebnisabfrage besuchen Sie bitte die Seite <b>%URL%</b> und geben Sie dort Ihre Matrikelnummer und das obige Passwort an.</i><br><hr><i>To access your results, please visit <b>%URL%</b> and enter your matriculation number and the password provided above</i>.\r\n<br><br><br><b>Information:</b> Mit dem &Uuml;bertragen des R-Keys auf Ihr Klausurdeckblatt stimmen Sie der verschl&uuml;sselten Speicherung Ihres Ergebnisses in einem EDV-System zu. Dieses Ergebnis kann online von jedem abgefragt werden, der Ihre Matrikelnummer sowie obiges Passwort kennt. Stimmen Sie diesem Vorgehen nicht zu, so ignorieren Sie bitte dieses Schreiben und vermerken Sie keinen R-Key auf Ihrer Klausur.\r\n<br><br><hr>\r\n<br><b>Information:</b> By copying your R-Key to your exam cover sheet, you agree, that your result will be stored encrypted in a database and is accessible online to everyone knowing your matriculation number and the password above. If you do not agree, just ignore this paper and do not copy the R-Key to your exam."
      );
     
      //Available languages:
      public static $languages = array(
        'en' => array('name' => 'English',
                      'icon' => 'english_icon.gif'),
        'de' => array('name' => 'Deutsch',
                      'icon' => 'german_icon.gif')
      );
      
      // This text is rendered on the bottom of each page
      public static $disclaimer = array (
      	'title'=> 'Important remark - Wichtiger Hinweis',
      	'text' => '<b>Disclaimer:</b> All data is provided for informational purposes only and no responsibility is taken for the correctness of the information.<br /><br /><b>Haftungsausschluss:</b> Die hier angezeigten Daten dienen lediglich Informationszwecken. Alle Informationen ohne Gew&auml;hr.'
      );
 }

// Read local configuration if exists: 
@include_once("local.php");
?>
