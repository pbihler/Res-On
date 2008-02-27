<?php
/*
 * Created on 06.12.2007 by bihler
 *
 * This file helps to setup the res-on installation
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 
 class SetupPage extends Page {
 	
 	private $error = '';
 	private $local_conf = array();
 	private $master_password_correct = false;
 	private $SUCCESS_REDIRECT_PAGE = "create_project.php";
 	private $LOCAL_CONF = 'config/local.php';
 	private $BASEDIR = '';
 	private $SEPARATOR = '//** DO NOT MODIFY ANYTHING BELOW THIS LINE!! **//';
 	private $new_master_salt;
 	
 	
 	
     function __construct() {
         parent::__construct();
         $this->setTitle(Messages::getString('SetupPage.Title'));
         
         //Set absolute basedir
         $this->BASEDIR = dirname(dirname(__FILE__ ));
         
         // Check, if transmitted master password is correct
         if (Config::$master_password) {
	         $check = $this->CheckPostMasterPassword();
	         if (check !== null && $check === false) 
	         	$this->error = Messages::getString('CreateProjectPage.MasterPasswordWrong');
	         $this->master_password_correct = $check;
         } 
         
         
         // process transmitted form
         if ((! Config::$master_password || $this->CheckPostMasterPassword()) && isset($_POST['setup'])) {
         	$this->error = $this->processForm();
         	if (! $this->error) {
	 			header("Location: " . $this->SUCCESS_REDIRECT_PAGE);  //Redirect to create_project, if succeeded
         	}          	
         }         
     }
     
     
     function renderNotes() {
     	if ($this->error)
     	   $this->renderError(htmlentities($this->error),false);
     	   
     	if (Config::$master_password && ! $this->master_password_correct) {
     		// show master password form
     		$this->renderNote($this->getMasterPasswordForm(),Messages::getString('SetupPage.EnterMasterPassword'));
     		$this->writeJavascript('document.createproject_form.master_pwd.focus();');
     	} else {
     		
     		// Check, if config is writeable:
     		$configdir = dirname($this->BASEDIR . '/' . $this->LOCAL_CONF);
     		if (! $this->isWriteable($configdir))
     	   		$this->renderError(htmlentities(sprintf(Messages::getString('SetupPage.ConfigDirNotWriteable'),$configdir)),false);
     	       
     	    $this->renderNote($this->getSetupForm(),Messages::getString('SetupPage.ConfigureInstallation'));
     	    $this->writeJavascript('document.createproject_form.pwd.focus();' .
     	    		'function checkForm() {' .
     	    		'  p = document.getElementById("master_pwd");' .
     	    		'  if (! p) return true;' .
     	    		'  if (p.value != "") return true;' .
     	    		sprintf('  alert("%s");',Messages::getString('SetupPage.MasterPasswordNotGiven')) .
     	    		'  document.createproject_form.master_pwd.focus();' .
     	    		'  return false;' .
     	    		'}');
     	}
     	   
     }
     
     
     /**
      * Asks for Master password
      */
     private function getMasterPasswordForm() {
     	$result = '<div id="createproject" class="formlayout"><form method="POST" name="createproject_form" autocomplete="off">';   
     	$result .= sprintf('<label for="master_pwd">%s: </label><input type="password" size="30" name="master_pwd" value="" /> ',
     	                     Messages::getString('CreateProjectPage.CreateProjectPassword')); 
     	                     
     	                     
     	$result .= sprintf('<input type="submit" value="%s"  />',Messages::getString('SetupPage.EnterSetup'));
     	
     	$result .= '</form>&nbsp;</div>';
         return $result;
     	
     }
     
     /**
      * The setup form
      */
     private function getSetupForm() {
     	$result = '<div id="createproject" style="formlayout"><form method="POST" name="createproject_form" autocomplete="off" onsubmit="return checkForm();">';
     	
     	
     	$result .= sprintf('<h4>%s</h4>',Messages::getString('SetupPage.GeneralSettings'));  
     	                     
     	$result .= sprintf('<label for="pwd">%s: </label><input type="password" size="30" name="pwd" value="" /><br />',
     	                     Messages::getString('SetupPage.MasterPassword'));     	                     
     	$result .= sprintf('<label for="pwd2">%s: </label><input type="password" size="30" name="pwd2" value="" /><br />',
     	                     Messages::getString('General.PasswordRepeat')); 
     	                     
     	$req_ssl = $this->postValue('general','ssl',Config::$require_ssl ? 'yes' : 'no') == 'yes';
     	$result .= sprintf('<label>%s: </label>',Messages::getString('SetupPage.RequireSSL'));
     	$using_ssl = $_SERVER['HTTPS'] == "on";
     	$result .= sprintf('<input type="radio" name="general[ssl]" value="yes" %1$s %2$s> %3$s</input> <input type="radio" name="general[ssl]" value="no" %4$s %2$s> %5$s</input> ',
     	                     $req_ssl ? ' checked="checked"' : '',
     	                     $using_ssl ? '' : 'disabled="disabled"',
     	                     Messages::getString('SetupPage.RequireSSLYes'),
     	                     $req_ssl ? '' : ' checked="checked"',
     	                     Messages::getString('SetupPage.RequireSSLNo'));
     	
     	if (! $using_ssl) {
     		$result .= sprintf(Messages::getString('SetupPage.VisitWithSSLtoSet'),"https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
     	} 
     	$result .= sprintf('<br /><label for="general[contact]">%s: </label><input type="text" size="30" name="general[contact]" value="%s" /><br />',
     	                     Messages::getString('SetupPage.ContactUrl'),$this->postValue('general','contact',Config::$contact_url)); 
     	
     	$result .= '<hr />';
     	
     	$result .= sprintf('<h4>%s</h4>',Messages::getString('SetupPage.DatabaseSettings'));
     	$result .= sprintf('<label for="db[server]">%s: </label><input type="text" size="30" name="db[server]" value="%s" /><br />',
     	                     Messages::getString('SetupPage.DatabaseServer'),$this->postValue('db','server',Config::$database['server']));    
     	$result .= sprintf('<label for="db[name]">%s: </label><input type="text" size="30" name="db[name]" value="%s" /><br />',
     	                     Messages::getString('SetupPage.DatabaseName'),$this->postValue('db','name',Config::$database['database']));   
     	$result .= sprintf('<label for="db[prefix]">%s: </label><input type="text" size="30" name="db[prefix]" value="%s" /><br />',
     	                     Messages::getString('SetupPage.DatabaseTablePrefix'),$this->postValue('db','prefix',Config::$database['table_prefix']));  
     	$result .= sprintf('<label for="db[user]">%s: </label><input type="text" size="30" name="db[user]" value="%s" /><br />',
     	                     Messages::getString('SetupPage.DatabaseUserName'),$this->postValue('db','user',Config::$database['username']));    	    	                      	                     
     	$result .= sprintf('<label for="db[pwd]">%s: </label><input type="password" size="30" name="db[pwd]" value="%s" /><br />',
     	                     Messages::getString('General.Password'),$this->postValue('db','pwd',Config::$database['username'] ? '**********' : ''));  
     	$result .= '<hr />';  
     	 
     	$result .= sprintf('<h4>%s</h4>',Messages::getString('SetupPage.ResultEncryptionSettings'));
     	$result .= sprintf('<label for="crypt[default]">%s: </label>',
     	                     Messages::getString('SetupPage.DefaultCryptModule'));    
     	$result .= '<select name="crypt[default]">';
     	foreach (array_keys(Config::$crypt_info) as $module)
     		$result .= sprintf('<option value="%1$s" %2$s>%1$s</option>',$module,$module == $this->postValue('crypt','default',Config::$default_crypt_module) ? ' selected="selected"' : '');
     	$result .= '</select><br />';     	  
     	$result .= sprintf('<label for="crypt[gpg_path]">%s: </label><input type="text" size="30" name="crypt[gpg_path]" value="%s" /><br />',
     	                     Messages::getString('SetupPage.GPGPath'),$this->postValue('crypt','gpg_path',$this->getDefaultGPGpath())); 
     	$result .= '<hr />'; 
     	                     
     	if (Config::$master_password) {
	     	$result .= sprintf('<label for="master_pwd">%s: </label><input type="password" size="30" name="master_pwd" id="master_pwd" value="" /> ',
	     	                     Messages::getString('SetupPage.CurrentMasterPassword'));
     	}
     	                     
     	$result .= sprintf('<input type="submit" value="%s" name="setup" />',Messages::getString('SetupPage.SaveSetup'));
     	
     	$result .= '</form>&nbsp;</div>';
         return $result;
     	
     }
     
     /**
      * Process the setup form
      */
      private function processForm() {      	
      	if ($error = $this->initLocalConf()) 
      	    return $error;	
      	    
      	if ($error = $this->initGeneral()) 
      	    return $error;
      	         	
      	if ($error = $this->initMysqlDatabase()) 
      	    return $error;
      	    
      	if ($error = $this->initCrypt()) 
      	    return $error;
      	    
      	if ($error = $this->writeLocalConf()) 
      	    return $error;
      	    
      }
      
      /**
       * Read local configuration file (for manual modifcations)
       */
      private function initLocalConf() {
      	$local_conf = $this->BASEDIR . '/' . $this->LOCAL_CONF;
      	if (file_exists($local_conf)) {
      		$handle = fopen ($local_conf, "r");
      		if (! $handle)
      		    return sprintf(Messages::getString('SetupPage.CouldNotReadFile'),$local_conf); 
			
			$this->local_conf = array();
			while (! feof($handle)) {
				$buffer = rtrim(fgets($handle));
				$this->local_conf[] = $buffer;
				if ($buffer == $this->SEPARATOR)
					break;
			}
			fclose ($handle);
      	} else {// Init default
			$this->local_conf = array();
			$this->local_conf[] = '<?php';
			$this->local_conf[] = '// Local configuration (overwrites defaults)';
			$this->local_conf[] = '// Insert manual modifications here:';
			$this->local_conf[] = '';
			
	     	// Some settings, which are interesting to the user, but which aren't supported by this GUI (yet)
	     	$this->local_conf[] = '// Config::$pdf_settings[\'orientation\'] = \'P\'; // \'P\' or \'Portrait\' /  \'L\' or \'Landscape\'';
	     	$this->local_conf[] = '// Config::$pdf_settings[\'format\'] = \'A5\'; // A3, A4, A5, Letter, Legal';
	     	$this->local_conf[] = '// Config::$default_project_info[\'info\']=\'\';';
	     	$this->local_conf[] = '// Config::$default_project_info[\'access\']=true; // Whether access is open by default, or not';
	     	$this->local_conf[] = '// Config::$default_project_info[\'introduction\']=\'\'; // The top of the PDF handout. You can use these variables: %RKEY%, %PASSWORD%, %URL%, %PROJECT%';
	     	$this->local_conf[] = '// Config::$default_project_info[\'hint\']=\'\'; // The bottom of the PDF handout. You can use these variables: %RKEY%, %PASSWORD%, %URL%, %PROJECT%';
			$this->local_conf[] = '// Config::$disclaimer[\'title\']=\'Important remark\';';
	     	$this->local_conf[] = '// Config::$disclaimer[\'text\']=\'Add important remarks here, which are shown on the bottom of every page\';';
			
	     	// Generate Hash salt:
	     	$this->local_conf[] = sprintf('Config::$crypt_info[\'hash\'][\'salt\'] = \'%s\';',$this->generateSalt());
	     	// Generate master salt:
	     	$this->new_master_salt = $this->generateSalt();
	     	$this->local_conf[] = sprintf('Config::$master_salt = \'%s\';',$this->new_master_salt);
	     		
			$this->local_conf[] = $this->SEPARATOR;
      	
      	}
      }
      
      /**
       * Sets General settings
       */
      private function initGeneral() {
      	
      	//Change Master Password      
     	if (! Config::$master_password && ! $_POST['pwd'])
     		return Messages::getString('SetupPage.PasswordNotEmpty');
     	if (((! Config::$master_password)  || $_POST['pwd'] || $_POST['pwd2']) && ($_POST['pwd'] != $_POST['pwd2']))
     		return Messages::getString('SetupPage.PasswordsNotEqual');
      	if ($pwd = $_POST['pwd']) {
      	  $salt = $this->new_master_salt ? $this->new_master_salt : Config::$master_salt;
      	  $pwd = $salt ? crypt($pwd,$salt) : $pwd;
	      $this->local_conf[] = sprintf('Config::$master_password = \'%s\';',addslashes($pwd));
      	} else {
	      $this->local_conf[] = sprintf('Config::$master_password = \'%s\';',Config::$master_password);
      	}
      	
      	// SSL Require
     	$req_ssl = $this->postValue('general','ssl',Config::$require_ssl ? 'yes' : 'no') == 'yes';
	    $this->local_conf[] = sprintf('Config::$require_ssl = %s;',$req_ssl == 'yes' ? 'true' : 'false');
     	$this->local_conf[] = sprintf('Config::$contact_url = \'%s\';',addslashes($this->postValue('general','contact',Config::$contact_url)));
      	
      }
      
      /**
       * Initializes the Mysql-Database
       */
      private function initMysqlDatabase() {
      	
      	// Read parameters from post
      	$server = $this->postValue('db','server',Config::$database['server']);
      	$dbname = $this->postValue('db','name',Config::$database['database']);
      	$table_prefix = $this->postValue('db','prefix',Config::$database['table_prefix']);
      	$user = $this->postValue('db','user',Config::$database['username']);
      	if ($_POST['db']['pwd']=='**********')
      	    unset($_POST['db']['pwd']);
      	$password = $this->postValue('db','pwd',Config::$database['password']);
      	
      	//Try to connect to database server
      	 $db = mysql_connect($server, $user, $password);      	 
      	 if (! $db || mysql_errno() != 0) return sprintf(Messages::getString('SetupPage.CouldNotConnectToServer'),$server,mysql_error()); 
      	 
      	 //Check, if mysql server is good enough for us
      	 $res = @mysql_query("SELECT Version()"); 
      	 $version=mysql_result($res,0,0);
      	 if (! preg_match("/^(\d+\.\d+\.\d+)/",$version,$matches))
      	     return Messages::getString('SetupPage.CouldNotIdentifyMysqlVersion');
      	 $min_mysql = "4.0.11"; // we need transactions
      	 if (version_compare($min_mysql,$matches[1]) > 0) 
      	     return Messages::getString('SetupPage.MysqlTooOld',$min_mysql,$matches[1]);
      	 
      	 //check or create database
      	 if(mysql_num_rows(mysql_query("SHOW DATABASES LIKE '".$dbname."'",$db))==1) { // Database exists
      	 	if (! mysql_select_db($dbname,$db)) 
      	 		return sprintf(Messages::getString('SetupPage.CouldNotConnectToDatabase'),$dbname,mysql_error());
      	 } else {
      	 	if (! mysql_query(sprintf('CREATE DATABASE `%s`',$dbname),$db)) 
      	 		return sprintf(Messages::getString('SetupPage.CouldNotCreateDatabase'),$dbname,mysql_error());
      	 	if (! mysql_select_db($dbname,$db)) 
      	 		return sprintf(Messages::getString('SetupPage.CouldNotConnectToDatabase'),$dbname,mysql_error()); 
      	 }
      	 
      	 //Create tables
      	 $TABLES = array('projects' => array('init' => '`project_id` int(11) unsigned NOT NULL auto_increment,' .
      	 		                                       '`project_name` varchar(255) NOT NULL,' .
      	 		                                       '`project_pwd` varchar(255) NOT NULL,' .
      	 		                                       'PRIMARY KEY  (`project_id`),' .
      	 		                                       'UNIQUE KEY `project_name` (`project_name`)',      	 		                                       
      	                                     'cols' => array('frontpage_info' => 'text',
      	                                                     'access_open' => "enum('no','yes') NOT NULL default 'yes'",
      	                                                     'project_pdf_introduction' => 'longtext',
      	                                                     'project_pdf_hint' => 'longtext')),
                         'results' => array('init' => '`project_id` int(11) NOT NULL,' .
                         		                      '`member_id` int(11) default NULL,' .
                         		                      '`mat_no` varchar(255) default NULL,' .
                         		                      '`result` text,' .
                         		                      'UNIQUE KEY `unique_member` (`project_id`,`member_id`),' .
                                                      'UNIQUE KEY `unique_mat_no` (`project_id`,`mat_no`),' .
                         		                      'KEY `mat_no` (`mat_no`)',
      	                                     'cols' => array('crypt_module' => "varchar(10)NOT NULL default 'hash'",
      	                                                     'crypt_data' => 'text')));
      	 foreach ($TABLES as $tname => $tdetails) {
      	 	$tname = $table_prefix . $tname;
      	 	if(mysql_num_rows(mysql_query(sprintf("SHOW TABLES  LIKE '%s'",$tname),$db))==0) {
	      	 	$query = sprintf('CREATE TABLE `%s` (%s) ENGINE=InnoDB DEFAULT CHARSET=latin1',$tname,$tdetails['init']);
	      	 	if (! mysql_query($query))
	      	 	   return sprintf(Messages::getString('SetupPage.CouldNotCreateTable'),$tname,mysql_error());
      	 	} 
      	 	
      	 	// Create additional columns (if required, to allow update of existing databases)
      	 	foreach ($tdetails['cols'] as $cname => $cparams) {
      	 		if(mysql_num_rows(mysql_query(sprintf("SHOW COLUMNS from `%s` LIKE '%s'",$tname,$cname),$db))==0) {
      	 			$query = sprintf('ALTER TABLE `%s` ADD `%s` %s',$tname,$cname,$cparams);
		      	 	if (! mysql_query($query))
		      	 	   return sprintf(Messages::getString('SetupPage.CouldNotAddColumn'),$cname,$tname,mysql_error()); 
      	 		}      	 		
      	 	}      	 	
      	 }
      	 
      	 // Index Migration from 0.4 db -> 0.5:
      	 $tname = $table_prefix . "results";
	     if (! mysql_query(sprintf('ALTER TABLE `%s` CHANGE `member_id` `member_id` INT(11) NULL DEFAULT NULL ',$tname)))
	      	 return sprintf(Messages::getString('SetupPage.CouldNotAlterTable'),$tname,mysql_error());
	      	 
	     if(mysql_num_rows(mysql_query(sprintf("SHOW INDEX FROM %s WHERE Key_name = 'PRIMARY'",$tname),$db))==2) { 	 
		     if (! mysql_query(sprintf('ALTER TABLE `%s` DROP PRIMARY KEY',$tname)))
		      	 return sprintf(Messages::getString('SetupPage.CouldNotAlterTable'),$tname,mysql_error());
	     }
	     
	     $UNIQUES = array('unique_member' => array('project_id','member_id'),
	                      'unique_mat_no' => array('project_id','mat_no'));
	     foreach ($UNIQUES as $iname => $icols) {
	         $res = mysql_query(sprintf("SHOW INDEX FROM %s WHERE Key_name = '%s'",$tname,$iname),$db);
	         //check if index is all right:
	         $drop_index = true;
	         $num_rows = mysql_num_rows($res);
	         if ($num_rows == 0) {
	             $drop_index = false;
	         } else {
	             if ($num_rows == count($icols)) {
	                //check index elements
	                $elements_all_right = true;
	             	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
					    if (! in_array($row['Key_name'],$icols)) {  
					       // Wrong index element => Renew it
					       $elements_all_right = false;
					       break;
					    }
					}
					if ($elements_all_right) // index seems to be okay => continue with next index;
					    continue;
	             }
	         }
	         if ($drop_index) {
		         if (! mysql_query(sprintf('ALTER TABLE `%s` DROP INDEX `%s`',$tname,$iname)))
			      	 return sprintf(Messages::getString('SetupPage.CouldNotAlterTable'),$tname,mysql_error());
	         }
	         if (! mysql_query(sprintf('ALTER TABLE `%s` ADD UNIQUE `%s` (%s)',$tname,$iname,join(', ',$icols))))
		      	 return sprintf(Messages::getString('SetupPage.CouldNotAlterTable'),$tname,mysql_error());
	     }
	     
	     //Lift database to new (> 0.4) security standard (= dont save member_id AND mat.number AND result the same time):
      	 $tname = $table_prefix . "results";
	     mysql_query(sprintf('UPDATE `%s` SET `member_id` = NULL WHERE `mat_no` IS NOT NULL AND `result` IS NOT NULL',$tname));
	     
      	 
      	 // OK, everything seems to be fine, so lets store these config values:
      	 
      	 foreach (array('server' => $server,
				          'username' => $user,
				          'password' => $password,
				          'database' => $dbname,
				          'table_prefix' => $table_prefix) as $key => $value) {
      		$this->local_conf[] = sprintf('Config::$database[\'%s\'] = \'%s\';',$key,addslashes($value));
      	 }
      	
      	
      }
     
     private function initCrypt() {
     	
     		
     	$gpg_path = $this->postValue('crypt','gpg_path',Config::$crypt_info['gpg']['program_path']);
     	if (! $gpg_path) { // deactivate GPG
     		$this->local_conf[] = 'unset(Config::$crypt_info[\'gpg\']);';
     	} else {
     		
	     	if (! file_exists($gpg_path))
	     		return sprintf(Messages::getString('SetupPage.GPGPathNotFound'),$gpg_path);     		
	     	$this->local_conf[] = sprintf('Config::$crypt_info[\'gpg\'][\'program_path\'] = \'%s\';',addslashes($gpg_path));
	     	
	     	// Check if keyring dir is writeable
	     	$keyring_home = dirname($this->BASEDIR . '/' . Config::$crypt_info['gpg']['keyring_home']);
	     	if ( ! $this->isWriteable($keyring_home))
	     		return sprintf(Messages::getString('SetupPage.KeyringHomeNotWriteable'),$keyring_home);    	
     	}	
     	
     	if (in_array($_POST['crypt']['default'],array_keys(Config::$crypt_info)))
     		$this->local_conf[] = sprintf('Config::$default_crypt_module = \'%s\';',addslashes($_POST['crypt']['default']));
     	
     }
     
      /**
       * Write local configuration file 
       */
      private function writeLocalConf() {
      	
      	$local_conf = $this->BASEDIR . '/' . $this->LOCAL_CONF;
  		$handle = fopen ($local_conf, "w");
  		if (! $handle)
  		    return sprintf(Messages::getString('SetupPage.CouldNotWriteFile'),$local_conf); 
		
		foreach($this->local_conf as $line) {
			fwrite($handle,$line . "\n");
		}
		if (! fclose ($handle))
  		    return sprintf(Messages::getString('SetupPage.CouldNotWriteFile'),$local_conf); 
      }
     
     
     
     /**
      * Checks, if a directory is writeable
      */
     private function isWriteable($dir) {
     	$testfile = $dir . "/.writeTestFile";
     	return touch($testfile) && unlink($testfile);
     } 
     
     /**
      * Generates some salt for the crypt function
      */
      private function generateSalt() {
      	if (CRYPT_BLOWFISH) {
          	$result = '$2$';
          	$n = 13;
      	} else if (CRYPT_MD5) {
      		$result = '$1$';
      		$n = 9;
      	} else if (CRYPT_EXT_DES) {
      		$result = '';
      		$n = 9;
      	} else {
      		$result = '';
      		$n = 2;
      	}
      	for ($i = 0; $i < $n; $i++) {
      		$result .= chr(mt_rand(32,126));
      	}
      	return $result;
      }
      	
     /**
      * Tries to guess the GPG location on initial setup
      */
      private function getDefaultGPGPath() {
      	$path = Config::$crypt_info['gpg']['program_path'];
      	if ($path) // some path already configured)
      		return $path;
      		
        if (! isset(Config::$crypt_info['gpg'])) // GPG has been disabled
          return '';
         
        $guesses = array('C:\\Program Files\\GNU\\GnuPG\\gpg.exe', // Windows (en)
                         'C:\\Programme\\GNU\\GnuPG\\gpg.exe', // Windows (de)
                         '/bin/gpg',
                         '/usr/bin/gpg',
                         '/usr/local/bin/gpg',
                         '/sw/bin/gpg' //(Mac & Fink)
                         );
       
        foreach ($guesses as $guess) {
        	if (file_exists($guess))
        	  return $guess;
        }
      }
 	
 }
?>
