<?php 
/*
 * Created on 21.03.2007
 *
 * Used to administrator Res-On
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class AdminMenuPage extends AdminPage {
 	 
 	 private $option_list = array('generate_keys.php' => 'Generate new R-Keys',
 	                              'enter_data.php' => 'Enter results',
 	                             'logout.php' => 'Logout from Administration');
 	
     function __construct() {
         parent::__construct();
         $this->setTitle(sprintf("Administration of %s (Id: %03d)", $this->project->getName(),$this->project->getId()));
     }
     
     function renderNotes() {
     	
         $db = Database::getInstance();
         $note = sprintf("Currently there are %d R-Keys for %s",$db->getMemberIdCount($this->project->getId()),$this->project->getName());
         $note .= '<ul>';
         foreach ($this->option_list as $file => $caption)
             $note .= '<li><a href="' . $file . '">' . $caption . '</li>';
         $note .= '</ul><br />';
         $this->renderNote($note,'Please select what to do:');
     }
     
 }
?>
