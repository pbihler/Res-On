<?php
/*
 * Created on 05.12.2007 by bihler
 *
 * Manage the metadata of a project here
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 
 class ManageProjectPage extends AdminPage {
 	
 	private $error = '';
 	private $SUCCESS_REDIRECT_PAGE = "admin.php";
    private $KEY_PDF_PHP = 'key_pdf.php';
 	
 	
     function __construct() {
         parent::__construct();
         
         if (isset($_POST['name'])) {
         	$this->error = $this->processForm();
         	if (! $this->error) {
	 			header("Location: " . $this->SUCCESS_REDIRECT_PAGE);  //Redirect to admin menu, if succeeded
         	} 
         	
         }
         
         $this->setTitle(sprintf(Messages::getString('ManageProjectPage.Title'),$this->project->getName()));
         $this->menu = array(Messages::getString('General.AdminMenu')=>"admin.php") + $this->menu; 
         
     }
     
     function processForm() {
     	if (! $_POST['name'])
     		return Messages::getString('CreateProjectPage.ProjectNameNotEmpty');
     	if ($_POST['pwd'] != $_POST['pwd2'])
     		return Messages::getString('CreateProjectPage.PasswordsNotEqual');
     		
     	if ($_POST['pwd'] && ! $this->project->verifyPassword($_POST['oldpwd']))
     		return Messages::getString('ManageProjectPage.OldPasswordWrong');
     		
         try {  	         
	                
	         $db = Database::getInstance();
	         $db->startTransaction();
	         
	         $this->project->setName(stripslashes($_POST['name']));
	         if ($_POST['pwd'])
	        	$this->project->setPassword(stripslashes($_POST['pwd']));
	         $this->project->setInfo(stripslashes($_POST['info']));
	         $this->project->setAccess($_POST['access'] == 'yes');
	         $this->project->setIntroduction(stripslashes($_POST['introduction']));
	         $this->project->setHint(stripslashes($_POST['hint']));
	         $db->commit();
	         $this->project->refresh();
	     } catch (Exception $exception){ // in this case, render exception as error.
	         $db->rollback();
	         //Rollback project information:
	         $this->project->refresh();
	         
			 return $exception;
		 }
		 return '';
     }
     
     function renderNotes() {
     	if ($this->error)
     	   $this->renderError($this->error,false);
     	   
     	$this->renderNote($this->getCreateForm(),Messages::getString('ManageProjectPage.ManageProject'));
     	$this->writeJavascript('document.createproject_form.name.focus();' .
     			'function pdfpreview() {' .
     			'   document.pdfpreview_form.introduction.value = document.createproject_form.introduction.value;' .
     			'   document.pdfpreview_form.hint.value = document.createproject_form.hint.value;' .
     			'   document.pdfpreview_form.submit();' .
     			'}');
     }
     
     private function getCreateForm() {
     	$result = '<div id="createproject"><form method="POST" name="createproject_form" autocomplete="off">';
     	
     	$result .= sprintf('<label for="name">%s: </label><input type="text" size="30" name="name" value="%s" /><br />',
     	                     Messages::getString('CreateProjectPage.ProjectName'),htmlspecialchars($this->postValue('name',null,$this->project->getName())));
     	                     
     	$result .= '<hr />';
     	$result .= sprintf('<label for="pwd">%s: </label><input type="password" size="30" name="oldpwd" value="" /><br />',
     	                     Messages::getString('ManageProjectPage.OldPwd'));
     	$result .= sprintf('<label for="pwd">%s: </label><input type="password" size="30" name="pwd" value="" /><br />',
     	                     Messages::getString('ManageProjectPage.NewPassword'));     	                     
     	$result .= sprintf('<label for="pwd2">%s: </label><input type="password" size="30" name="pwd2" value="" /><br />',
     	                     Messages::getString('General.PasswordRepeat')); 
     	$result .= '<hr />';
     	
     	$result .= sprintf('<label for="info">%s </label><textarea cols="50" rows="5" name="info">%s</textarea><br />',
     	                     Messages::getString('ManageProjectPage.FrontpageInfo'),htmlspecialchars($this->postValue('info',null,$this->project->getInfo())));
     	$access = $this->postValue('access',null,$this->project->getAccess() ? 'yes' : 'no') == 'yes';
     	$result .= sprintf('<label>%s: </label><input type="radio" name="access" value="yes" %s > %s</input> <input type="radio" name="access" value="no" %s > %s</input><br />',
     	                     Messages::getString('ManageProjectPage.Access'),
     	                     $access ? ' checked="checked"' : '',
     	                     Messages::getString('ManageProjectPage.AccessOpen'),
     	                     $access ? '' : ' checked="checked"',
     	                     Messages::getString('ManageProjectPage.Closed'));
     	$result .= '<hr />';
     	
     	$result .= sprintf('<label for="introduction">%s </label><textarea cols="50" rows="5" name="introduction">%s</textarea><br />',
     	                     Messages::getString('ManageProjectPage.Introduction'),htmlspecialchars($this->postValue('introduction',null,$this->project->getIntroduction())));
    	$result .= sprintf('<label for="hint">%s </label><textarea cols="50" rows="5" name="hint">%s</textarea>',
     	                     Messages::getString('ManageProjectPage.Hint'),htmlspecialchars($this->postValue('hint',null,$this->project->getHint())));
     	$result .= sprintf('<input type="button" value="%s" onclick="pdfpreview();" id="pdfpreviewbutton" /><br />',Messages::getString('ManageProjectPage.showPdfPreview'));
    	                                       
    	$result .= '<hr />';
     	                     
     	$result .= sprintf('<input type="submit" value="%s"  /> <input type="reset" value="%s"  />',Messages::getString('ManageProjectPage.ChangeProjectInformation'),Messages::getString('ManageProjectPage.ResetValues'));
     	
     	$result .= sprintf('</form><form name="pdfpreview_form" action="%s" method="POST" target="_blank">' .
     			'<input type="hidden" name="key[]" value="123-456789" />' .
     			'<input type="hidden" name="pwd[]" value="previewOnly" />' .
     			'<input type="hidden" name="introduction" value="" />' .
     			'<input type="hidden" name="hint" value="" />' .
     			'<input type="hidden" name="ext" value=".pdf" /></form>',
     	$this->KEY_PDF_PHP);
     	
     	$result .= '</form>&nbsp;</div>';
         return $result;
     }
     	
 }
?>
