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
     private $csvDataSets = array();
     private $processError = '';
     private $numberOfCsvCols = 0;
     private $remark = array();
     private $db;
     
     function __construct() {
         parent::__construct();
         $this->setTitle(sprintf(Messages::getString('EnterDataPage.Title'), $this->project->getName()));
                $this->menu = array(Messages::getString('General.AdminMenu')=>"admin.php") + $this->menu; 
         $this->db = Database::getInstance();
     }
     
     function renderNotes() {      
         
        $this->processError = '';
        if (isset($_FILES['csvfile']))
        	$this->processCsvImport();
        	
        if ($this->processError) {
            $this->renderNote($this->processError,Messages::getString('General.OperationFailed'));
        }
     
        	
        if (isset($_POST['key']))
        	$this->processInput();
        	
        if ($this->storeResult > 0) {
            $this->renderNote(sprintf(Messages::getString('EnterDataPage.SuccessMessage'),$this->storeResult),Messages::getString('General.OperationSuccessful'));
            
            //Clear POST data
            unset($_POST['key']);
            unset($_POST['mat_no']);
            unset($_POST['data']);
            unset($_POST['ignore']);
            $this->remark = array();
            
        } elseif ($this->storeResult == 0) {
            $this->renderNote(sprintf(Messages::getString('EnterDataPage.ErrorMessage'),$this->storeResult),Messages::getString('General.OperationFailed'));
        }
        
        if (count($this->csvDataSets) == 0) {
         	$this->renderNote(Messages::getString('EnterDataPage.ImportDataChoice'),Messages::getString('EnterDataPage.ImportDataChoiceTitle'));
         	
         	$this->renderNote($this->generateCsvImportForm(),Messages::getString('EnterDataPage.ImportFromCsv'),"csv");
        }
        	
        $this->renderNote($this->generateDataForm(),Messages::getString('EnterDataPage.EnterDataHere'),"direct");
        $this->writeJavascript('document.enter_data_form.elements[0].focus();');
        
     
     }
     
     private function generateDataForm() {
         
         $this->writeJavascript('function remIgnore($i) {
             if (ignoreElement = document.enter_data_form.elements["ignore["+$i+"]"]) {
                 /* Just allow ignoring, when data did not change */
                 $("remark_"+$i).innerHTML = "";
             }
         }
         function clear_row($i) {
             document.enter_data_form.elements["key["+$i+"]"].value="";
             document.enter_data_form.elements["mat_no["+$i+"]"].value="";
             document.enter_data_form.elements["data["+$i+"]"].value="";
             document.enter_data_form.elements["key["+$i+"]"].focus();' .
             		'document.getElementById("set_" + $i).className="";
             document.getElementById("remark_"+$i).innerHTML = "";
         }
         function ignoreAll($max_datasets) {
             for ($i = 0; $i < $max_datasets; ++$i) {
               if (ignoreElement = document.enter_data_form.elements["ignore["+$i+"]"]) {             
                 ignoreElement.checked = true;
               }
             }
         }');
         

         //prepare Data
         $max_datasets = max(Config::$numberOfDataSetsToEnter,count($_POST['key']),count($_POST['mat_no']),count($_POST['data']));
         
         if (count($this->csvDataSets) > 0) {
         	$result .= $this->CsvDataSelector();
            $max_datasets = count($this->csvDataSets);
         }
         
         
         //Find out whether there are errors/warnings
         $errorCount = 0;
         $warningCount = 0;
         for ($i = 0; $i < $max_datasets; $i++) {
             $hasRemark = isset($this->remark[$i]) && $this->remark[$i];
             $isIgnorable = isset($_POST['ignore'][$i]);
             if ($hasRemark) {
             	if ($isIgnorable)
             	  $warningCount++;
             	else
             	  $errorCount++;
             }
         }
         
         //print out the form
         $result .= '<form method="POST" name="enter_data_form">';
         
         $result .= '<table id="enter_data">';
         
         $result .=	sprintf('<tr>' .
         		'<th>%s</th><th>%s</th><th>%s</th><th>%s</th>' .
         		'</tr>',Messages::getString('General.RKey'),Messages::getString('General.MatNo'),Messages::getString('General.Result'),($warningCount || $errorCount ? Messages::getString('General.Remark'): ''));
         
         
         
         
         for ($i = 0; $i < $max_datasets; $i++) {
             $hasRemark = isset($this->remark[$i]) && $this->remark[$i];
             $isIgnorable = isset($_POST['ignore'][$i]);
             
             $result .= sprintf('<tr%s id="set_%d">',$hasRemark ? sprintf(' class="%s"', $isIgnorable ? 'warning' : 'error') : '',$i);
             $result .= sprintf('<td nowrap="nowrap">%03d&ndash;<input type="text" name="key[%d]" id="key[%d]" value="%s" size="10" maxlength="10" onchange="remIgnore(%1$d)" /></td>',$this->project->getId(),$i,$i,htmlspecialchars($this->postValue('key',$i)));
             $result .= sprintf('<td><input type="text" name="mat_no[%d]" id="mat_no[%d]" value="%s" size="10" onchange="remIgnore(%1$d)" /></td>',$i,$i,htmlspecialchars($this->postValue('mat_no',$i)));
             $result .= sprintf('<td><input type="text" name="data[%d]" id="data[%d]" value="%s" size="10" onchange="remIgnore(%1$d)" /></td>',$i,$i,htmlspecialchars($this->postValue('data',$i)));
             
             $result .= sprintf('<td id="remark_%d">',$i);
             if ($hasRemark) {
                 $result .= $this->remark[$i];
                 $ignore = $this->postValue('ignore',$i,null);
                 if ($isIgnorable) {
                     $result .= sprintf('<br /><input type="checkbox" name="ignore[%1$d]" value="1" %2$s/>&nbsp;%3$s',$i, $ignore ? 'checked="checked" ' : '',Messages::getString('EnterDataPage.Ignore'));
                 }
                 $result .= sprintf(' <input type="button" value="%s" onclick="clear_row(%2$d)" />',Messages::getString('EnterDataPage.ClearInput'),$i);
             }
             $result .= '</td>';
             
             $result .= '</tr>';
         }
         
         $result .= '</table>';
         if ($warningCount)
         	$result .= sprintf('<input type="button" value="%s" id="ignore_all" onclick="ignoreAll(%d)" /><br />',Messages::getString('EnterDataPage.IgnoreAll'), $max_datasets);         
         $result .= sprintf('<input type="submit" value="%s" id="submit_data" />',Messages::getString('EnterDataPage.StoreData'));         
         $result .= '</form>&nbsp;';
         return $result;
     }
     
     private function processInput() {
         $this->storeResult = 0;
         
         
         // Strip header, if required
         if ($_POST['csv_has_header']) {
         	$_POST['key'][0] = '';
         	$_POST['mat_no'][0] = '';
         	$_POST['data'][0] = '';
         }
         
         //Inputs
         $keys = $_POST['key'];
         $mat_nos = $_POST['mat_no'];
         $data = $_POST['data'];
         $element_count = max(count($keys),count($mat_nos),count($data));
         
         $nonempty_elements = $element_count;
         	         
         //Check validity of inputs
         $commitData = true;
         
         $this->db->startTransaction();
         for ($i =  0; $i <$element_count; $i++) {
         	if (! $keys[$i] && ! $mat_nos[$i] && ! $data[$i]) {
                unset($_POST['ignore'][$i]);
                $nonempty_elements--;
         		continue;
         	}
         		
         	$this->remark[$i] = '';
         
         	// check the R-Key
            if (! $keys[$i]) {
                $this->remark[$i] = Messages::getString('EnterDataPage.NoRKey');
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            
            try {
				$rkey = new RKey(sprintf('%03d-%s',$this->project->getId(),$keys[$i]));                
            } catch (Exception $e) {
                $this->remark[$i] = Messages::getString('EnterDataPage.RKeyInvalid');
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
         
            // check the Mat-No
            if (! $mat_nos[$i]) {
                $this->remark[$i] = Messages::getString('EnterDataPage.NoMatNo');
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            $mat_no = $mat_nos[$i];
            
            if (! ctype_alnum($mat_no)) {
                $this->remark[$i] = Messages::getString('StartPage.MatNoInvalid');
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
            
            // Verify for current data
            $current_data = $this->db->getResultDataByRKey($rkey);
            if (! $current_data ) {
            	$current_data = $this->db->getResultDataByMatNo($rkey->getProjectId(),$mat_no);
            }
            if (! $current_data) {
                $this->remark[$i] = Messages::getString('EnterDataPage.RKeyNotFound');
                $commitData = false; //Fatal Error
                unset($_POST['ignore'][$i]);
                continue;
            }
             
            
            // mat used with other rkey?
            if ($current_data['member_id']) {
	            $alt_data = $this->db->getResultDataByMatNo($rkey->getProjectId(),$mat_no,$current_data['member_id']);
	            if ($alt_data) {
	                $this->remark[$i] .= sprintf(Messages::getString('EnterDataPage.MatNoAlreadyUsed'),$mat_no);
	                $commitData = false; //Fatal Error
	                unset($_POST['ignore'][$i]);
	                continue;
	            }
            }
            
            // Now go for warnings:
            
            // rkey already used? (this test will get obsolete)
            if ($current_data['mat_no'] && $current_data['mat_no'] != $mat_no) {
                $this->remark[$i] .= sprintf(Messages::getString('EnterDataPage.RKeyAlreadyUsedMatNo'),$current_data['mat_no']);
                if (! $this->postValue('ignore',$i,null)) {
	                $commitData = false; //Fatal Error
	                $_POST['ignore'][$i] = false; // might be ignored the next time
                }
            }
            
            
            // check the Data
            if (! $data[$i]) {
                $this->remark[$i] .= Messages::getString('EnterDataPage.NoResultProvided');
                if (! $this->postValue('ignore',$i,null)) {
	                $commitData = false; //Fatal Error
	                $_POST['ignore'][$i] = false; // might be ignored the next time
                }
            }
            $date = $data[$i];
            
            if ($current_data['result']) {
                $this->remark[$i] .=  Messages::getString('EnterDataPage.AlreadyResultStored');
                if (! $this->postValue('ignore',$i,null)) {
	                $commitData = false; //Fatal Error
	                $_POST['ignore'][$i] = false; // might be ignored the next time
                }
            }
            
            if (! $this->remark[$i] || $this->postValue('ignore',$i,null)) {
                
                // encrypt data:
                $crypt = new CryptProxy($current_data['crypt_module'], $this->project->getId(),$current_data['member_id']);
                $crypted_date = $crypt->encryptResult($date,$current_data['crypt_data']);
                // Save data to database
                try {
                	$this->db->updateResultData($current_data['project_id'],$current_data['member_id'],$mat_no,$crypted_date);
                } catch (Exception $exception) {
                    $this->remark[$i] = $exception;
	                $commitData = false; //Fatal Error
	                unset($_POST['ignore'][$i]);
	                continue;
                }
                $this->storeResult++;
            }
            
         }
         
         //Finish transaction
         if ($nonempty_elements == 0) {
          	$this->db->rollback();
         	$this->storeResult = -1;
         } elseif ($commitData) {
         	$this->db->commit();
         } else {
          	$this->db->rollback();
            $this->storeResult = 0;
         }
     }
     
     
     
     
     
     
     
     /**
      * Processes the uploaded CSV-file
      */
     private function processCsvImport() {
     	if ($handle = fopen ($_FILES['csvfile']['tmp_name'],'r')) {
     		$separator = urldecode($_POST['separator']);
     		if (! $separator) $separator = ',';
     		$c = 0;
			while (($data = fgetcsv ($handle, 1024,$separator)) !== FALSE ) {
				if (! $data || count($data) == 0 || ! $data[0])
				    continue;
				$this->numberOfCsvCols = max($this->numberOfCsvCols, count($data));
				$this->csvDataSets[] = $data;
			}
			fclose ($handle);
			if (count($this->csvDataSets) == 0) {
				$this->processError = Messages::getString('No data found in input file.');
			}
     	} else {
     		$this->processError = Messages::getString('EnterDataPage.CouldNotReadInputFile') + ' ' + $_FILES['csvfile']['error'];
     	}
     	@unlink($_FILES['csvfile']['tmp_name']);
     }
     
     /**
      * creates a selct box for CSV data input
      */
     private function csvDataSelector() {
     	
     	$js = sprintf("var csv_data = new Array(%d);",count($this->csvDataSets));
     	foreach ($this->csvDataSets as $i => $row) {
     		$js .= sprintf("csv_data[%d] = new Array(%d);",$i,count($row));
     		foreach ($row as $j => $data) {
     			$data = addslashes ($data);  // Escape Quote sign;
     		    $js .= sprintf("csv_data[%d][%d] = '%s';",$i,$j,$data);
     		}
     	}
     	$js .= sprintf(' function toggle_headers() {' .
     			'    var has_header = document.getElementById("csv_has_header").checked;' .
     			'    var select_boxes = new Array("rkey_selector","matno_selector","result_selector");' .
     			'    for(i=0;i<select_boxes.length;i++) {' .
     			'        for (j=1;j<%d;j++) {' .
     			'            document.getElementById(select_boxes[i]).options[j].text = has_header ? csv_data[0][j-1] : "%s " + j;' .
     			'        }' .
     			'    }' .
     			'    document.getElementById("set_0").style.visibility = has_header ? "hidden" : "visible";' .
     			'}',$this->numberOfCsvCols,Messages::getString('EnterDataPage.Column'));
     	$js .= sprintf(' function fill_col(selector,id) {' .
     			'    var col = document.getElementById(selector).selectedIndex - 1;' .
     			'    if (col < 0) return;' .
     			'    for (j=0;j<%d;j++) {' .
     			'      document.getElementById(id + "[" + j + "]").value = csv_data[j][col];' .
     			'    }' .
     			'}',count($this->csvDataSets));
     	$this->writeJavascript($js);
     	
     	$result = '<tr>';
     	foreach (array('key' => 'rkey_selector','mat_no' => 'matno_selector','data' => 'result_selector') as $id => $selector) {
	     	$result .= sprintf('<td><select id="%s" onchange="fill_col(\'%s\',\'%s\')">',$selector,$selector,$id);
	     	$result .= sprintf('<option value="-1">%s</option>',Messages::getString('EnterDataPage.SelectColumn'));
	     	for ($i = 0; $i < $this->numberOfCsvCols; $i++) {
	     		$result .= sprintf('<option value="%d">%s %d</option>',$i,Messages::getString('EnterDataPage.Column'),$i+1);	     		
	     	}
	     	$result .= '</select></td>';
     	}
     	$result .= sprintf('<td><input name="csv_has_header" type="checkbox" id="csv_has_header" onclick="toggle_headers()">&nbsp;<label for="csv_has_header">%s</label></input></td>',Messages::getString('EnterDatapage.InputFileContainsHeader'));
         		
        $result .= '</tr>';
     	return $result;
     }
          	 
     	 
     /**
      * Offers the option to upload a CSV file
      */
     private function generateCsvImportForm() {
     	
        $result = '<form method="POST" name="import_csv_form" enctype="multipart/form-data" class="formlayout">';
     	$result .= sprintf('<p><label for="csvfile">%s:</label> <input name="csvfile" type="file" /></p>',Messages::getString('EnterDataPage.SelectFile'));
     	$result .= sprintf('<p><label for="separator">%s:</label> <select name="separator">',Messages::getString('EnterDataPage.Separator'));
     	foreach(array(';' => ';',',' => ',',"\t" => Messages::getString('General.Tab')) as $sep => $sep_name) {
     		$result .= sprintf('<option value="%s">%s</option>',urlencode($sep),$sep_name);
     	}
     	$result .= sprintf('</select><br /><input type="submit" value="%s" id="upload_data" /></p>',Messages::getString('EnterDataPage.Upload'));
     	$result .= '</form>&nbsp;';
     	return $result;
     }
 }
?>
