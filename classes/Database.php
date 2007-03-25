<?php
/*
 * Created on 25.03.2007 by bihler
 *
 * Realizes the Database access
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class Database extends Singleton {
     //public getter for singleton instance
     public static function getInstance(){
     	return Singleton::getSingleton(get_class());
     }
     
     private $db_link = null;
     
     protected function __construct(){
         //Open database connection
         $db_link = mysql_connect(MainConfig::$database['server'], MainConfig::$database['username'], MainConfig::$database['server']);
         if (! $db_link || mysql_errno() != 0) 
			throw new DatabaseConnectionException('Could not establish a database connection');
		
		 if (! mysql_select_db(MainConfig::$database['database'],$db_link))
		 	throw new DatabaseConnectionException('Could not establish open database');
     }
 }
 
 /**
  * An exception to throw if there is a database error
  */
 class DatabaseException extends Exception {
   public function __toString() {
       return __CLASS__ . ": [{$this->code}]: {$this->message} - MySQL Error # " . mysql_errno() . ": " . mysql_error() . "\n";
   }
 }
 
 /**
  * An exception to throw if there could no dataase connection be established
  */
 class DatabaseConnectionException extends DatabaseException {
     //
 }
?>
