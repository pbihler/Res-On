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
    
     public function getProjectInfo($project_id,$password) {
     	$project_id = intval($project_id);
     	$res = mysql_query("SELECT *" .
     					   " FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id" . 
                           " AND project_pwd = PASSWORD('$password')",$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     public function setProjectName($project_id,$name) {
     	$project_id = intval($project_id);
     	$name = mysql_escape_string($name);
     	return mysql_query("UPDATE " . $this->TABLES['projects'] . 
     	                   " SET project_name = '$name'" .
     	                   " WHERE project_id = $project_id");
     }
     
     public function getNextMemberId($project_id) {
     	$project_id = intval($project_id);
     	$resource = mysql_query("SELECT MAX(member_id) AS id" .
     			           " FROM " . $this->TABLES['results'] . 
     	                   " WHERE project_id = $project_id");
     	$result = mysql_fetch_assoc($resource);
     	return $result['id'] + 1;
     }
     
     public function getMemberIdCount($project_id) {
     	$project_id = intval($project_id);
     	$resource = mysql_query("SELECT COUNT(member_id) AS count" .
     			           " FROM " . $this->TABLES['results'] . 
     	                   " WHERE project_id = $project_id");
     	$result = mysql_fetch_assoc($resource);
     	return $result['count'];
     }
     
     public function createRkey($project_id,$member_id,$crypt_module,$crypt_data) {
     	$project_id = intval($project_id);
     	$member_id = intval($member_id);
     	$crypt_module = mysql_escape_string($crypt_module);
     	$crypt_data = mysql_escape_string($crypt_data);
     	return mysql_query("INSERT INTO " . $this->TABLES['results'] . 
     	                   " SET project_id = $project_id," .
     	                   "     member_id = $member_id," .
     	                   "     crypt_module = '$crypt_module'," .
     	                   "     crypt_data = '$crypt_data'" );
     }
     
     public function getProjectPdfTexts($project_id) {
     	$project_id = intval($project_id);
         $res = mysql_query("SELECT project_pdf_introduction as introduction, project_pdf_hint as hint" .
     					   " FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id"); 
        return mysql_fetch_assoc($res);
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
