<?php
/*
 * Created on 29.03.2007 by bihler
 *
 * Reflecting the configuration of a project
 *
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 */
 class Project {
 	private $id = 0;
 	private $name = '';
 	private $info = '';
 	private $access = true;
 	private $introduction = '';
 	private $hint = '';
 	private $db = null;
 	
 	function __construct($id,$password) {
 		$this->__refresh($id,$password);
 	}
 	public function refresh() {
 		$this->__refresh($this->id);
 	}
 	private function __refresh($id,$password = '') {
 		$this->db = Database::getInstance();
 		$project_info = $this->db->getProjectInfo($id,$password);
 		if (! $project_info)
 			throw new PasswordException('Wrong password');
 		$this->id = $project_info['project_id'];
 		$this->name = $project_info['project_name'];
 		$this->info = $project_info['frontpage_info'];
 		$this->access = $project_info['access_open'] == 'yes';
 		$this->introduction = $project_info['project_pdf_introduction'];
 		$this->hint = $project_info['project_pdf_hint'];
 	}
 	
 	public function getId() {
 		return $this->id;
 	}
 	
 	public function verifyPassword($password) {
 		$this->db = Database::getInstance();	
 		return $this->db->getProjectInfo($this->id,$password) && true;
 	} 	
 	public function setPassword($password) {
 		$this->db = Database::getInstance();	
 	    if ($this->db->setProjectPassword($this->id,$password))
 		    return true;
		else
			throw new DatabaseException($db->last_error);
 	}
 	
 	public function getName() {
 		return $this->name;
 	} 	
 	public function setName($name) { 		
 		if ($this->setString('project_name',$name))
 			$this->name = $name;
 	}
 	
 	
 	public function getInfo() {
 		return $this->info;
 	} 	
 	public function setInfo($info) { 	
 		if ($this->setString('frontpage_info',$info))
 			$this->info = $info;
 	}
 	
 	public function getAccess() {
 		return $this->access;
 	} 	
 	public function setAccess($access) { 	
 		if ($this->setString('access_open',$access ? 'yes' : 'no'))
 			$this->access = $access;
 	}
 	
 	public function getIntroduction() {
 		return $this->introduction;
 	} 	
 	public function setIntroduction($introduction) { 	
 		if ($this->setString('project_pdf_introduction',$introduction))
 			$this->introduction = $introduction;
 	}
 	
 	public function getHint() {
 		return $this->hint;
 	} 	
 	public function setHint($hint) { 	
 		if ($this->setString('project_pdf_hint',$hint))
 			$this->hint = $hint;
 	}
 	
 	private function setString($name,$value) { 	
 		$this->db = Database::getInstance();	
 	    if ($this->db->setProjectString($this->id,$name,$value))
 		    return true;
		else
			throw new DatabaseException($this->db->lastError());
 	}
 	
 }
 
 class PasswordException extends Exception {
     //
 }
?>
