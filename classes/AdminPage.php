<?php 
/*
 * Created on 21.03.2007
 *
 * Used to administrator Res-On
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class AdminPage extends Page {
     
 	
     function __construct() {
         parent::__construct();
         $this->page_title = "Administration of Res-on"; 
         $this->introduction = "Administration of Res-On"; 
         $this->menu = array("Logout"=>"admin.php?logout=1") + $this->menu; 
     }
     
     function renderNotes() {
         $note = "Logged in";
         $this->renderNote($note,'Please select what to do:');
     }
     
 }
?>
