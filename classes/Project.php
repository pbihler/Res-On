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
 	private $db = null;
 	
 	function __construct($id) {
 		$this->db = Database::getInstance();
 		$project_info = $this->db->getProjectInfo($id);
 		$this->id = $project_info['project_id'];
 		$this->name = $project_info['project_name'];
 	}
 	
 	public function getId() {
 		return $this->id;
 	}
 	
 	public function getName() {
 		return $this->name;
 	}
 	
 	public function setName($name) {
 		
 	    if ($this->db->setProjectName($this->id,$name))
 		    $this->name=$name;
 	}
 	
 }
?>
