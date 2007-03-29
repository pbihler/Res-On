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
     
     /*
      * Object functions are following
      */
     
     private $db_link = null;
     private $TABLES = array('projects' => 'projects',
     				    	 'results' => 'results');
     
     protected function __construct(){
         //Open database connection
         $this->db_link = mysql_connect(MainConfig::$database['server'], MainConfig::$database['username'], MainConfig::$database['password']);
         if (! $this->db_link || mysql_errno() != 0) 
			throw new DatabaseConnectionException('Could not establish a database connection');
		
		 if (! mysql_select_db(MainConfig::$database['database'],$this->db_link))
		 	throw new DatabaseConnectionException('Could not establish open database');
         
         if (isset(MainConfig::$database['db_prefix']))
         	foreach ($this->TABLES as $table_name => $table)
         		$this->TABLES[$table_name] = MainConfig::$database['db_prefix'] . $table;
     }
     
     /**
      * Checks, whether the credentials for a project are ok
      * 
      * @param string password
      * @param string project_id
      * @return bool true if authentication is valid
      */
     public function checkAuthentication($password,$project_id) {
     	$password = mysql_escape_string($password);
     	$project_id = intval($project_id);
        $res = mysql_query("SELECT project_id" .
     					   " FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id" . 
                           " AND project_pwd = PASSWORD('$password')",$this->db_link); 
        return mysql_num_rows($res) > 0;
     }
     
     public function getProjectInfo($project_id) {
     	$project_id = intval($project_id);
     	$res = mysql_query("SELECT *" .
     					   " FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id",$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     public function setProjectName($project_id,$name) {
     	$project_id = intval($project_id);
     	$name = mysql_escape_string($name);
     	return mysql_query("UPDATE " . $this->TABLES['projects'] . 
     	                   " SET project_name = '$name'" .
     	                   " WHERE project_id = $project_id");
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
