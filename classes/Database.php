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
         $this->db_link = mysql_connect(Config::$database['server'], Config::$database['username'], Config::$database['password']);
         if (! $this->db_link || mysql_errno() != 0) 
			throw new DatabaseConnectionException(Messages::getString('Database.NoConnection'));
		
		 if (! mysql_select_db(Config::$database['database'],$this->db_link))
		 	throw new DatabaseConnectionException(Messages::getString('Database.NoOpen'));
         
         if (isset(Config::$database['db_prefix']))
         	foreach ($this->TABLES as $table_name => $table)
         		$this->TABLES[$table_name] = Config::$database['db_prefix'] . $table;
     }
    
    public function startTransaction() {
        return mysql_query("START TRANSACTION",$this->db_link);
    }
    public function rollback() {
        return mysql_query("ROLLBACK",$this->db_link);
    }
    public function commit() {
        return mysql_query("COMMIT",$this->db_link);
    }
    public function affectedRows() {
        return mysql_affected_rows($this->db_link);
    }    
    public function lastError() {
    	return mysql_error($this->db_link);
    }
    
    /**
     * Project-Related functions
     */
    
     public function getProjectInfo($project_id,$password = '') {
     	$project_id = intval($project_id);
     	$res = mysql_query("SELECT *" .
     					   " FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id" . 
                           ($password ? " AND project_pwd = PASSWORD('$password')" : ""),$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     public function setProjectString($project_id,$name,$value) {
     	$project_id = intval($project_id);
     	$name = mysql_escape_string($name);
     	$value = mysql_escape_string($value);
     	mysql_query("UPDATE " . $this->TABLES['projects'] . 
     	                   " SET $name = '$value'" .
     	                   " WHERE project_id = $project_id",$this->db_link);
     	                   
     	if (mysql_errno($this->db_link) != 0) 
     	  throw new DatabaseException(mysql_error($this->db_link));
     	  
     	return true;
     }
     public function setProjectPassword($project_id,$password) {
     	$project_id = intval($project_id);
     	$password = mysql_escape_string($password);
     	mysql_query("UPDATE " . $this->TABLES['projects'] . 
     	                   " SET project_pwd = PASSWORD('$password')" .
     	                   " WHERE project_id = $project_id",$this->db_link);
     	                   
     	if (mysql_errno($this->db_link) != 0) 
     	  throw new DatabaseException(mysql_error($this->db_link));
     	return true;
     }
     
     /*
      * Returns the project info if only if the access is open
      */
     public function accessOpen($project_id) {
     	$project_id = intval($project_id);
        $res = mysql_query("SELECT * FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id" .
                           "   AND access_open = 'yes'",$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     /** 
      * returns project information to render the frontpage
      */
     public function getFrontpageInfo() {
         $res = mysql_query("SELECT project_id as id, project_name as name, frontpage_info as info, access_open as access" .
     					   " FROM " . $this->TABLES['projects']);
     					   
        $result = array();
        while($project = mysql_fetch_object($res)) {
        	$result[$project->id] = $project;
        }
        return $result;
     }
     
     /**
      * inserts a new project
      * @return int id of affected project (false if error occured)
      */
     public function insertProject($project_info) {
         $settings = array();
         if (isset($project_info['pwd'])) 
         	$settings[] = sprintf("project_pwd=PASSWORD('%s')",mysql_escape_string($project_info['pwd']));
         
         foreach (array("name"=>"project_name","info"=>"frontpage_info","introduction"=>"project_pdf_introduction","hint"=>"project_pdf_hint") as $id => $sql_id) {        	
	         if (isset($project_info[$id])) 
	         	$settings[] = sprintf("%s='%s'",$sql_id,mysql_escape_string($project_info[$id]));
         }
         
         if (isset($project_info['access'])) 
         	$settings[] = sprintf("access_open='%s'", $project_info['access'] ? 'yes' : 'no');
         
         $query =  sprintf("INSERT INTO %s SET %s",$this->TABLES['projects'],join(",",$settings));
       
         if (mysql_query($query,$this->db_link)) {
         	return mysql_insert_id($this->db_link);
         } else
         	return false;
     }
     
     /**
      * Management of Results
      */
      
    /*
     * Returns a currently unused member ID - hopefully terminates once :)
     */
     public function getNextMemberId($project_id) {
     	$project_id = intval($project_id);
     	
     	$count = $this->getMemberIdCount();
     	
     	while (true) {
	     	if ($count < 10000)
	     	    $id = rand(1,99999);
	     	else
	     	    $id = rand($count,$count*10);
	     	    
	     	$resource = mysql_query("SELECT member_id AS id" .
	     			           " FROM " . $this->TABLES['results'] . 
	     	                   " WHERE project_id = $project_id
	     	                     AND member_id = $id",$this->db_link);
     	    if (mysql_num_rows == 0) // not used!
     	       return $id;
     	}
     }
     
     public function getMemberIdCount($project_id) {
     	$project_id = intval($project_id);
     	$resource = mysql_query("SELECT COUNT(member_id) AS count" .
     			           " FROM " . $this->TABLES['results'] . 
     	                   " WHERE project_id = $project_id",$this->db_link);
     	$result = mysql_fetch_assoc($resource);
     	return $result['count'];
     }
     
     public function createRkey($project_id,$member_id,$crypt_module,$crypt_data) {
     	$project_id = intval($project_id);
     	$member_id = intval($member_id);
     	$crypt_module = mysql_escape_string($crypt_module);
     	$crypt_data = mysql_escape_string($crypt_data);
     	mysql_query("INSERT INTO " . $this->TABLES['results'] . 
     	                   " SET project_id = $project_id," .
     	                   "     member_id = $member_id," .
     	                   "     crypt_module = '$crypt_module'," .
     	                   "     crypt_data = '$crypt_data'" ,$this->db_link);
     	if (mysql_errno($this->db_link) != 0) 
     	  throw new DatabaseException(mysql_error($this->db_link));
     	return true;
     }
     
     public function getResultDataByRKey($rkey) {
     	$project_id = intval($rkey->getProjectId());
     	$member_id = intval($rkey->getMemberId());
         $res = mysql_query("SELECT *" .
         		           " FROM " . $this->TABLES['results'] . 
                           " WHERE project_id = $project_id" .
                           "   AND member_id = $member_id",$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     public function getResultDataByMatNo($project_id,$mat_no,$ignore_member_id = null) {
     	$project_id = intval($project_id);
     	$mat_no = mysql_escape_string($mat_no);
     	if ($ignore_member_id)
     		$ignore_member_id = intval($ignore_member_id);
         $res = mysql_query("SELECT *" .
         		           " FROM " . $this->TABLES['results'] . 
                           " WHERE project_id = $project_id" .
                           "   AND mat_no = '$mat_no'" . 
                           ($ignore_member_id ? 
                              " AND (NOT member_id = $ignore_member_id OR member_id IS NULL)" :
                              ""),$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     public function updateResultData($project_id,$member_id,$mat_no,$result) {
     	$project_id = intval($project_id);
     	$member_id = intval($member_id);
     	$mat_no = mysql_escape_string($mat_no);
     	$result = mysql_escape_string($result);
     	if ($member_id) {
          $res = mysql_query("UPDATE " . $this->TABLES['results'] .
         				   " SET member_id = NULL, " .
         				   "     mat_no = '$mat_no'," .
         				   "     result = '$result' " . 
                           " WHERE project_id = $project_id" .
                           "   AND member_id = $member_id",$this->db_link); 
     	} else {
          $res = mysql_query("UPDATE " . $this->TABLES['results'] .
         				   " SET result = '$result' " . 
                           " WHERE project_id = $project_id" .
                           "   AND mat_no ='$mat_no'",$this->db_link);      	
     	}
     	if (mysql_errno($this->db_link) != 0) 
     	  throw new DatabaseException(mysql_error($this->db_link));
     	return true;
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
