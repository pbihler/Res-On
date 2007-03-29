<?php 
/*
 * Created on 21.03.2007
 *
 * Used to administrator Res-On
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class AdminMenuPage extends Page {
     
 	 private $session = null;
 	 private $project = null;
 	 
 	 private $option_list = array('create_id.php' => 'Create new R-Keys',
 	                             'logout.php' => 'Logout from Administration');
 	
     function __construct() {
         parent::__construct();
         $this->session = Session::getInstance();
         $this->project = $this->session->getProject();
         $title = sprintf("Administration of %s (Id: %03d)", $this->project->getName(),$this->project->getId());
         $this->page_title = $title; 
         $this->introduction = $title; 
         $this->menu = array("Logout"=>"logout.php") + $this->menu; 
     }
     
     function renderNotes() {
         $note = '<ul>';
         foreach ($this->option_list as $file => $caption)
             $note .= '<li><a href="' . $file . '">' . $caption . '</li>';
         $note .= '</ul><br />';
         $this->renderNote($note,'Please select what to do:');
     }
     
 }
?>
