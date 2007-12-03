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
 	
 	private $frontpage_info = array();
 	private $error = '';
 	
     function __construct() {
         parent::__construct();
         $this->setTitle(Messages::getString('StartPage.Title'));
         $this->menu = array(Messages::getString('General.Admin')=>"admin.php") + $this->menu; 
         
         try {
	         $db = Database::getInstance();         
	         $this->frontpage_info = $db->getFrontpageInfo();
	         if ($this->frontpage_info[Config::$default_project_id]->info)
	         	$this->introduction = sprintf(Messages::getString('StartPage.FrontpageInfo'),$this->frontpage_info[Config::$default_project_id]->name,$this->frontpage_info[Config::$default_project_id]->info);
         } catch (Exception $exception){ // in this case, render exception as error.
			 $this->error = $exception;
		 }
     }
     
     function renderNotes() {
     	
     	if ($this->error) {
     		$this->renderError($this->error);
     		return;
     	}
         
         //Results requested?
         if (isset($_POST['mat_no']) && $_POST['mat_no']) {
             $this->renderResult();
         } else {
	     	 $this->renderNote($this->getResultRequestForm(),Messages::getString('StartPage.RequestResults'));
	     	 $js = 'document.request_results_form.mat_no.focus();';
	     	 if (! $this->frontpage_info[Config::$default_project_id]->access) {
	     	     $js .= 'document.getElementById("requestbutton").disabled = true;';
	     	 }
	     	 
	     	 $js .= 'var projects = new Array();';
	     	 foreach ($this->frontpage_info as $id => $project) {
	     	 	 $js .= sprintf('projects["%1$03d"] = new Array();' .
	     	 	 		'projects["%1$03d"]["access"] = %2$d;',$id,$project->access == 'yes');
	     	 	 if ($project->info)
	     	 	 	$js .= 	sprintf('projects["%1$03d"]["info"] = "%2$s";',$id,
	     	 	 		addslashes(sprintf(Messages::getString('StartPage.FrontpageInfo'),$project->name,preg_replace("/\s/"," ",$project->info))));
	     	 }
	     	 
	     	 $js .= ' function updateProjectInfo() {' .
	     	 		'  selector = document.getElementById("project_id_selector");' .
	     	 		'  index = selector.selectedIndex;' .
	     	 		'  value = selector.options[index].value;' .
	     	 		'  document.getElementById("requestbutton").disabled = ! projects[value]["access"];' .
	     	 		'  document.getElementById("introduction").innerHTML = projects[value]["info"] ? projects[value]["info"] : "";' .
	     	 		'}';
	     	 
	     	 $this->writeJavascript($js);
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
            if (! $db->accessOpen($project_id)) {
             	$this->renderError(Messages::getString('StartPage.NoAccessOpen'));
            	return;
            }
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
     	$result = '<div id="requestresults"><form method="POST" name="request_results_form" autocomplete="off">';
     	if (count($this->frontpage_info) > 1) {
	     	$result .= sprintf('<label class="startformlabel" for="project_id">%s: </label>',Messages::getString('General.Project'));
	     	$result .= '<select name="project_id" id="project_id_selector" onchange="updateProjectInfo();">';
	     	foreach ($this->frontpage_info as $id => $project) {
	     	    $result .= sprintf('<option value="%03d" %s>%s&nbsp;&nbsp;</option>',$id,
	     	        ($id == Config::$default_project_id ? 'selected="selected"' : ''),
	     	        		$project->name);
	     	}	
	     	$result .= '</select><br/>';
     	} else {
     		foreach ($this->frontpage_info as $id => $project) {
	     	    $result .= sprintf('<input type="hidden" name="project_id" value="%03d" />',$id);
	     	}	
     	}
     	$result .=	sprintf('<label class="startformlabel" for="mat_no">%s: </label><input type="text" name="mat_no" value="" size="10" class="startinput" /><br />',Messages::getString('General.MatNo')) .
     			sprintf('<label class="startformlabel" for="password">%s: </label><input type="password" name="password" value="" size="10" class="startinput" />&nbsp;',Messages::getString('General.Password')) .
     			sprintf('<input type="submit" value="%s" id="requestbutton" />',Messages::getString('StartPage.RequestResults')) .
     			'</form>&nbsp;</div>';
         return $result;
     }
     
 }
?>
