<?php
/*
 * Created on 04.12.2007 by bihler
 *
 * Add new projects to the site
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class CreateProjectPage extends Page {
 	
 	private $error = '';
 	private $new_project_id=0;
 	
 	
     function __construct() {
         parent::__construct();
         $this->setTitle(Messages::getString('CreateProjectPage.CreateNewProject'));
         $this->menu = array(Messages::getString('General.Admin')=>"admin.php") + $this->menu;  
         $this->menu = array(Messages::getString('General.Home')=>"index.php") + $this->menu;
         
         if (isset($_POST['name'])) {
         	$this->error = $this->processForm();
         	if (! $this->error) {
	 			$session = Session::createNewSession($_POST['pwd'],$this->new_project_id);
	 			header("Location: admin.php");  //Redirect to admin menu, if succeeded
         	} 
         	
         }
         
     }
     
     function processForm() {
     	if (! $_POST['name'])
     		return Messages::getString('CreateProjectPage.ProjectNameNotEmpty');
     	if (! $_POST['pwd'])
     		return Messages::getString('CreateProjectPage.PasswordNotEmpty');
     	if ($_POST['pwd'] != $_POST['pwd2'])
     		return Messages::getString('CreateProjectPage.PasswordsNotEqual');
     	if ($_POST['master_pwd'] != Config::$master_password)
     		return Messages::getString('CreateProjectPage.MasterPasswordWrong');
     		
     		
         try {
	         $db = Database::getInstance();          
	         $project_info = array('name' => stripslashes($_POST['name']),
	                               'pwd' => stripslashes($_POST['pwd']),
	                               'info' => Config::$default_project_info['info'],
	                               'access' => Config::$default_project_info['access'],
	                               'introduction' => Config::$default_project_info['introduction'],
	                               'hint' => Config::$default_project_info['hint']);
	          if (! $this->new_project_id = $db->updateProject($project_info)) 
	             return sprintf("%s: %s",Messages::getString('General.dbError'),$db->lastError());
	     } catch (Exception $exception){ // in this case, render exception as error.
			 return $exception;
		 }
		 return '';
     }
     
     function renderNotes() {
     	if ($this->error)
     	   $this->renderError($this->error,false);
     	   
     	$this->renderNote($this->getCreateForm(),Messages::getString('CreateProjectPage.CreateNewProject'));
     	$this->writeJavascript('document.createproject_form.name.focus();');
     }
     
     private function getCreateForm() {
     	$result = '<div id="createproject"><form method="POST" name="createproject_form" autocomplete="off">';
     	
     	$result .= sprintf('<label for="name">%s: </label><input type="text" size="30" name="name" value="%s" /><br />',
     	                     Messages::getString('CreateProjectPage.ProjectName'),$_POST['name']);
     	                     
     	$result .= sprintf('<label for="pwd">%s: </label><input type="password" size="30" name="pwd" value="" /><br />',
     	                     Messages::getString('General.Password'));     	                     
     	$result .= sprintf('<label for="pwd2">%s: </label><input type="password" size="30" name="pwd2" value="" /><br />',
     	                     Messages::getString('General.PasswordRepeat')); 
     	$result .= '<hr />';
     	                                       
     	$result .= sprintf('<label for="master_pwd">%s: </label><input type="password" size="30" name="master_pwd" value="" /> ',
     	                     Messages::getString('CreateProjectPage.CreateProjectPassword')); 
     	                     
     	$result .= sprintf('<input type="submit" value="%s"  />',Messages::getString('CreateProjectPage.CreateNewProject'));
     	
     	$result .= '</form>&nbsp;</div>';
         return $result;
     }
     	
 }
?>
