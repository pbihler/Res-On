<?php
/*
 * Created on 21.04.2007 by bihler
 *
 * Enables the administration to enter examination results
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 

 class EnterDataPage extends AdminPage {
     
     private $storeResult = -1;
     private $remark = array();
     private $db;
     
     function __construct() {
         parent::__construct();
         $this->setTitle(sprintf("Enter results for %s:", $this->project->getName()));
                $this->menu = array("Admin Menu"=>"admin.php") + $this->menu; 
         $this->db = Database::getInstance();
     }
     
     function renderNotes() {      
         
        if (isset($_POST['key']))
        	$this->processInput();
        	
        if ($this->storeResult > 0) {
            $this->renderNote(sprintf('%s data-sets successfully stored',$this->storeResult),'Operation successful');
            
            //Clear POST data
            unset($_POST['key']);
            unset($_POST['mat_no']);
            unset($_POST['data']);
            unset($_POST['ignore']);
            $this->remark = array();
            
        } elseif ($this->storeResult == 0) {
            $this->renderNote(sprintf('Data could not be stored - please correct errors below.',$this->storeResult),'Operation failed!');
        }
        $this->renderNote($this->generateDataForm(),'Enter data here:');
        $this->writeJavascript('document.enter_data_form.elements[0].focus();');
     
     }
     
     private function generateDataForm() {
         
         $this->writeJavascript('function remIgnore($i) {
             if (ignoreElement = document.enter_data_form.elements["ignore["+$i+"]"]) {
                 /* Just allow ignoring, when data did not change */
                 document.getElementById("remark_"+$i).innerHTML = "";
             }
         }
         function clear_row($i) {
             document.enter_data_form.elements["key["+$i+"]"].value="";
             document.enter_data_form.elements["mat_no["+$i+"]"].value="";
             document.enter_data_form.elements["data["+$i+"]"].value="";
             document.enter_data_form.elements["key["+$i+"]"].focus();
             document.getElementById("remark_"+$i).innerHTML = "";
         }');
         $result = '<form method="POST" name="enter_data_form">';
         
         $result .= '<table id="enter_data">' .
         		'<tr>' .
         		'<th>R-Key</th><th>Mat.-Number</th><th>Result</th><th>Remark</th>' .
         		'</tr>';
         
         
         for ($i = 0; $i < MainConfig::$numberOfDataSetsToEnter; $i++) {
             $hasRemark = isset($this->remark[$i]) && $this->remark[$i];
             $isIgnorable = isset($_POST['ignore'][$i]);
             
             $result .= sprintf('<tr%s>',$hasRemark ? sprintf(' class="%s"', $isIgnorable ? 'warning' : 'error') : '');
             $result .= sprintf('<td nowrap="nowrap">%03d&ndash;<input type="text" name="key[%d]" value="%s" size="10" maxlength="10" onchange="remIgnore(%1$d)" /></td>',$this->project->getId(),$i,$this->postValue('key',$i));
             $result .= sprintf('<td><input type="text" name="mat_no[%d]" value="%s" size="10" onchange="remIgnore(%1$d)" /></td>',$i,$this->postValue('mat_no',$i));
             $result .= sprintf('<td><input type="text" name="data[%d]" value="%s" size="10" onchange="remIgnore(%1$d)" /></td>',$i,$this->postValue('data',$i));
             
             $result .= sprintf('<td id="remark_%d">',$i);
             if ($hasRemark) {
                 $result .= $this->remark[$i];
                 $ignore = $this->postValue('ignore',$i,null);
                 if ($isIgnorable) {
                     $result .= sprintf('<br /><input type="checkbox" name="ignore[%1$d]" value="1" %2$s/>&nbsp;Ignore',$i, $ignore ? 'checked="checked" ' : '');
                 }
                 $result .= sprintf(' <input type="button" value="Clear" onclick="clear_row(%1$d)" />',$i);
             }
             $result .= '</td>';
             
             $result .= '</tr>';
         }
         
         $result .= '</table>';
         $result .= '<input type="submit" value="Store data" />';         
         $result .= '</form>';
         return $result;
     }
     
     private function processInput() {
         $this->storeResult = 0;
         
         //Inputs
         $keys = $_POST['key'];
         $mat_nos = $_POST['mat_no'];
         $data = $_POST['data'];
         $element_count = max(count($keys),count($mat_nos),count($data));
         
         //Check validity of inputs
         $commitData = true;
         
         $this->db->startTransaction();
         for ($i =0; $i <$element_count; $i++) {
         	if (! $keys[$i] && ! $mat_nos[$i] && ! $data[$i]) {
                unset($_POST['ignore'][$i]);
         		continue;
         	}
         		
         	$this->remark[$i] = '';
         
         	// check the R-Key
            if (! $keys[$i]) {
                $this->remark[$i] = 'No RKey provided.';
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            
            try {
				$rkey = new RKey(sprintf('%03d-%s',$this->project->getId(),$keys[$i]));                
            } catch (Exception $e) {
                $this->remark[$i] = 'RKey invalid.';
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            
            $current_data = $this->db->getResultDataByRKey($rkey);
            if (! $current_data) {
                $this->remark[$i] = 'RKey not found.';
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
             
            // check the Mat-No
            if (! $mat_nos[$i]) {
                $this->remark[$i] = 'No matriculation number provided.';
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            $mat_no = $mat_nos[$i];
            
            if (! ctype_alnum($mat_no)) {
                $this->remark[$i] = 'Matriculation number contains non-alphanumerical characters.';
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            
            // mat used with other rkey?
            $alt_data = $this->db->getResultDataByMatNo($rkey->getProjectId(),$mat_no,$rkey->getMemberId());
            if ($alt_data) {
                $alt_rkey = new RKey($alt_data['project_id'],$alt_data['member_id']);
                $this->remark[$i] .= sprintf('Matriculation number already used for RKey %s. ',$alt_rkey);
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            
            // Now go for warnings:
            
            // mat_no used?
            if ($current_data['mat_no'] && $current_data['mat_no'] != $mat_no) {
                $this->remark[$i] .= sprintf('R-Key already used for matriculation number %s. ',$current_data['mat_no']);
                if (! $this->postValue('ignore',$i,null)) {
	                $commitData = false; //Fatal Error
	                $_POST['ignore'][$i] = false; // might be ignored the next time
                }
            }
            
            
            // check the Data
            if (! $data[$i]) {
                $this->remark[$i] .= 'No result provided. ';
                if (! $this->postValue('ignore',$i,null)) {
	                $commitData = false; //Fatal Error
	                $_POST['ignore'][$i] = false; // might be ignored the next time
                }
            }
            $date = $data[$i];
            
            if ($current_data['result']) {
                $this->remark[$i] .=  'Already a result stored for this R-Key. ';
                if (! $this->postValue('ignore',$i,null)) {
	                $commitData = false; //Fatal Error
	                $_POST['ignore'][$i] = false; // might be ignored the next time
                }
            }
            
            if (! $this->remark[$i] || $this->postValue('ignore',$i,null)) {
                
                // encrypt data:
                $crypt = new CryptProxy($current_data['crypt_module'], $this->project->getId(),$rkey->getMemberId());
                $crypted_date = $crypt->encryptResult($date,$current_data['crypt_data']);
                // Save data to database
                $this->db->updateResultData($rkey,$mat_no,$crypted_date);
                $this->storeResult++;
            }
            
         }
         
         //Finish transaction
         if ($commitData) {
         	$this->db->commit();
         } else {
          	$this->db->rollback();
            $this->storeResult = 0;
         }
     }
     
     /**
      * To access a value (from POST) if set
      */
     private function postValue($name,$index = null,$default ='') {
         if (isset($_POST[$name]) && ($index === null || isset($_POST[$name][$index]))) {
             return ($index === null) ? $_POST[$name] : $_POST[$name][$index];
         } else {
             return $default;
         }
     }
 }
?>
