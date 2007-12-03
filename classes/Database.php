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
    
    /**
     * Project-Related functions
     */
    
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
     	                   " WHERE project_id = $project_id",$this->db_link);
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
     
     
     public function getProjectPdfTexts($project_id) {
     	$project_id = intval($project_id);
         $res = mysql_query("SELECT project_pdf_introduction as introduction, project_pdf_hint as hint" .
     					   " FROM " . $this->TABLES['projects'] . 
                           " WHERE project_id = $project_id",$this->db_link);
        return mysql_fetch_assoc($res);
     }
     
     /**
      * Management of Results
      */
      
     public function getNextMemberId($project_id) {
     	$project_id = intval($project_id);
     	$resource = mysql_query("SELECT MAX(member_id) AS id" .
     			           " FROM " . $this->TABLES['results'] . 
     	                   " WHERE project_id = $project_id",$this->db_link);
     	$result = mysql_fetch_assoc($resource);
     	return $result['id'] + 1;
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
     	return mysql_query("INSERT INTO " . $this->TABLES['results'] . 
     	                   " SET project_id = $project_id," .
     	                   "     member_id = $member_id," .
     	                   "     crypt_module = '$crypt_module'," .
     	                   "     crypt_data = '$crypt_data'" ,$this->db_link);
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
                              " AND NOT member_id = $ignore_member_id" :
                              ""),$this->db_link); 
        return mysql_fetch_assoc($res);
     }
     
     public function updateResultData($rkey,$mat_no,$result) {
     	$project_id = intval($rkey->getProjectId());
     	$member_id = intval($rkey->getMemberId());
     	$mat_no = mysql_escape_string($mat_no);
     	$result = mysql_escape_string($result);
         $res = mysql_query("UPDATE " . $this->TABLES['results'] .
         				   " SET mat_no = '$mat_no'," .
         				   "     result = '$result' " . 
                           " WHERE project_id = $project_id" .
                           "   AND member_id = $member_id",$this->db_link); 
        return;
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
