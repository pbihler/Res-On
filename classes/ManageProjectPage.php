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
 	private $LOGOUT_REDIRECT_PAGE = "logout.php";
    private $KEY_PDF_PHP = 'key_pdf.php';
 	
 	
     function __construct() {
         parent::__construct();
         
         if (isset($_POST['name'])) {
         	$this->error = $this->processForm();
         	if (! $this->error) {
	 			header("Location: " . $this->SUCCESS_REDIRECT_PAGE);  //Redirect to admin menu, if succeeded
         	} 
         	
         } else if (isset($_POST['reset_data'])) {
         	$this->error = $this->processResetForm();
         	if (! $this->error) {
	 			header("Location: " . $this->SUCCESS_REDIRECT_PAGE);  //Redirect to admin menu, if succeeded
         	} else if ($this->error == "project_deleted") {
         		header("Location: " . $this->LOGOUT_REDIRECT_PAGE);  //Redirect to admin menu, if succeeded
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
     
     function processResetForm() {
         $action = $_POST['reset_data'];
         if (! $action)         
	         return Messages::getString('ManageProjectPage.NoActionSelected');
	         
         try {  	         
	                
	         $db = Database::getInstance();
	         $db->startTransaction();
	         switch ($action) {
	            case 'purge':
	              $db->removeEmptyRkeys($this->project->getId());
	              break;
	            case 'clean':
	              $db->removeStoredResults($this->project->getId());
	              break;
	            case 'remove':
	              $db->removeAllResultData($this->project->getId());
	              $gpg = new CryptGPG($this->project->getId(),0);
	              $gpg->removeKeyring();
	              break;
	            case 'delete_project':
	              $gpg = new CryptGPG($this->project->getId(),0);
	              $gpg->removeKeyring();
	              $db->removeProject($this->project->getId());
	              $db->commit();
	              return "project_deleted";
	              break;
	         	default:
	         	  $db->rollback();
	         	  return Messages::getString('ManageProjectPage.NoActionSelected');
	         }
	         $db->commit();
	         $this->project->refresh();
	     } catch (Exception $exception){ // in this case, render exception as error.
	         $db->rollback();
	         //Rollback project information:
	         try {
	           $this->project->refresh();
	         } catch (Exception $ex) {}
	         
			 return $exception;
		 }
		 return '';
     }
     
     function renderNotes() {
     	if ($this->error)
     	   $this->renderError($this->error,false);
     	   
     	$this->renderNote($this->getManageForm(),Messages::getString('ManageProjectPage.ManageProject'));
     	$this->writeJavascript('document.createproject_form.name.focus();' .
     			'function pdfpreview() {' .
     			'   document.pdfpreview_form.introduction.value = document.createproject_form.introduction.value;' .
     			'   document.pdfpreview_form.hint.value = document.createproject_form.hint.value;' .
     			'   document.pdfpreview_form.submit();' .
     			'}' .
     	        'function resetData(question,reset_value){
     	            $("reset_data").value="";
     	            if (confirm(question)) {
     	              $("reset_data").value=reset_value;
     	              $("reset_form").submit();
     	            }
     	         }');
     }
     
     private function getManageForm() {
     	$result = '<div id="manageproject" class="formlayout"><form method="POST" name="createproject_form" autocomplete="off">';
     	
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
     	$result .= '<hr />';
     	$result .= sprintf('<label>%s: </label><input type="radio" name="access" value="yes" %s > %s</input> <input type="radio" name="access" value="no" %s > %s</input><br />',
     	                     Messages::getString('ManageProjectPage.Access'),
     	                     $access ? ' checked="checked"' : '',
     	                     Messages::getString('ManageProjectPage.AccessOpen'),
     	                     $access ? '' : ' checked="checked"',
     	                     Messages::getString('ManageProjectPage.Closed'));
     	$result .= '<hr />';
     	$result .= sprintf('</p><div id="pdf_config"><h3>%s:</h3><p>',Messages::getString('ManageProjectPage.Handout'));
     	
     	
     	$result .= sprintf('<label for="introduction">%s </label><textarea cols="50" rows="6" name="introduction">%s</textarea><br />',
     	                     Messages::getString('ManageProjectPage.Introduction'),htmlspecialchars($this->postValue('introduction',null,$this->project->getIntroduction())));
    	
     	$result .= sprintf('<span class="handout_center">(%s)</span>',Messages::getString('ManageProjectPage.HandoutCenter'));
     	$result .= sprintf('<label for="hint">%s </label><textarea cols="50" rows="6" name="hint">%s</textarea>',
     	                     Messages::getString('ManageProjectPage.Hint'),htmlspecialchars($this->postValue('hint',null,$this->project->getHint())));
     	$result .= sprintf('<input type="button" value="%s" onclick="pdfpreview();" id="pdfpreviewbutton" /><br />',Messages::getString('ManageProjectPage.showPdfPreview'));
    	                       
     	$result .= '</div>';                
    	$result .= '<hr />';
     	                     
     	$result .= sprintf('<input type="submit" value="%s" id="submit_data" /><br /> <input type="reset" value="%s"  id="reset_data_button" />',Messages::getString('ManageProjectPage.ChangeProjectInformation'),Messages::getString('ManageProjectPage.ResetValues'));
     	
     	$result .= sprintf('</form><form name="pdfpreview_form" action="%s" method="POST" target="_blank">' .
     			'<input type="hidden" name="key[]" value="123-456789" />' .
     			'<input type="hidden" name="pwd[]" value="previewOnly" />' .
     			'<input type="hidden" name="introduction" value="" />' .
     			'<input type="hidden" name="hint" value="" />' .
     			'<input type="hidden" name="ext" value=".pdf" /></form>',
     	$this->KEY_PDF_PHP);
     	
     	$result .= '</form>&nbsp;</p></div><p>';
     	
     	$result .= sprintf('<h3>%s</h3>',Messages::getString('ManageProjectPage.ResetData'));
     	
     	$result .= '<div id="resetproject" class="formlayout"><form method="POST" name="reset_form" id="reset_form" autocomplete="off">';
     	$result .= '<input type="hidden" id="reset_data" name="reset_data" value="" />';
     	$result .= sprintf('<input type="button" value="%s" onclick="resetData(\'%s\',\'purge\');" />',Messages::getString('ManageProjectPage.PurgeData'),Messages::getString('ManageProjectPage.PurgeDataConfirm'));
     	$result .= sprintf(' %s<br />',Messages::getString('ManageProjectPage.PurgeDataDescr'));
     	$result .= sprintf('<input type="button" value="%s" onclick="resetData(\'%s\',\'clean\');" />',Messages::getString('ManageProjectPage.CleanData'),Messages::getString('ManageProjectPage.CleanDataConfirm'));
     	$result .= sprintf(' %s<br />',Messages::getString('ManageProjectPage.CleanDataDescr'));
     	$result .= sprintf('<input type="button" value="%s" onclick="resetData(\'%s\',\'remove\');" />',Messages::getString('ManageProjectPage.RemoveData'),Messages::getString('ManageProjectPage.RemoveDataConfirm'));
     	$result .= sprintf(' %s<br />',Messages::getString('ManageProjectPage.RemoveDataDescr'));
     	$result .= sprintf('<input type="button" value="%s" onclick="resetData(\'%s\',\'delete_project\');" />',Messages::getString('ManageProjectPage.DeleteProject'),Messages::getString('ManageProjectPage.DeleteProjectConfirm'));
     	$result .= sprintf(' %s<br />',Messages::getString('ManageProjectPage.DeleteProjectDescr'));
     	$result .= '</form>&nbsp;</div>';
     	
         return $result;
     }
     	
 }
?>
