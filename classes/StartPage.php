<?php 
/*
 * Created on 21.03.2007
 *
 * Defines the content and actions of the Startpage
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class StartPage extends Page {
     function __construct() {
         parent::__construct();
         $this->setTitle(Messages::getString('StartPage.Title'));
         $this->menu = array(Messages::getString('General.Admin')=>"admin.php") + $this->menu; 
     }
     
     function renderNotes() {
         
         //Results requested?
         if (isset($_POST['mat_no']) && $_POST['mat_no']) {
             $this->renderResult();
         } else {
	     	 $this->renderNote($this->getResultRequestForm(),Messages::getString('StartPage.RequestResults'));
	     	// $this->writeJavascript('document.request_results_form.mat_no.focus();');
         }
     }
     
     private function renderResult() {
         $mat_no = $_POST['mat_no'];
         if (! ctype_alnum($mat_no)) {
             $this->renderError(Messages::getString('StartPage.MatNoInvalid'));
         	 return;
         }
         $project_id = $_POST['project_id'];
         if (! ctype_digit($project_id)) {
             $this->renderError(Messages::getString('StartPage.ProjectIdInvalid'));
         	 return;
         }
         $pwd = $_POST['password'];
         if (! $pwd) {
             $this->renderError(Messages::getString('StartPage.PasswordEmpty'));
             return;
         }
         $result_str = Messages::getString('StartPage.NoResultsFound');
         if (preg_match(PasswordGenerator::$passwordCharacterRegExp,$pwd)) {
             //If not, we dont query the database, but we won't tell the intruder either
	        $db = Database::getInstance();
         	$data = $db->getResultDataByMatNo($project_id,$mat_no);
            $crypt = new CryptProxy($data['crypt_module'], $project_id,$data['member_id']);
         	$decrypted_result = $crypt->decryptResult($data['result'],$data['crypt_data'],$pwd);
         	if ($decrypted_result)
         		$result_str = sprintf('<div class="result">%s</div>',$decrypted_result);
         }
         $this->renderBackNote($result_str,sprintf(Messages::getString('StartPage.Results'),$mat_no));
         
     }
     
     private function renderError($error) {
             $this->renderBackNote(sprintf('<div class="error">%s</div>',$error),Messages::getString('General.Error'));         
     }
     
     protected function renderBackNote($text, $title = '', $date = '') {
             parent::renderNote(sprintf('%s' .
             		'<div class="back"><input type="button" value="%s" onclick="history.back();" /></div>',$text,Messages::getString('General.Back')),$title,$date);         
     }
     private function getResultRequestForm() {
     	return sprintf('<div id="requestresults"><form method="POST" name="request_results_form" autocomplete="off">' .
     			'<input type="hidden" name="project_id" size="3" value="%03d" readonly="readonly" />' .
     			'<label id="startformlabel" for="mat_no">%s: </label><input type="text" name="mat_no" value="" size="10" class="startinput" /><br />' .
     			'<label id="startformlabel" for="password">%s: </label><input type="password" name="password" value="" size="10" class="startinput" />&nbsp;' .
     			'<input type="submit" value="%s" id="requestbutton" />' .
     			'</form>&nbsp;</div>' .
     			'',MainConfig::$default_project_id,Messages::getString('General.MatNo'),Messages::getString('General.Password'),Messages::getString('StartPage.RequestResults'));
     }
     
 }
?>
