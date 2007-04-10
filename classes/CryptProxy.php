<?php
/*
 * Created on 22.03.2007 by bihler
 *
 * Acts like the CryptModule selected in main.conf.php
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 class CryptProxy implements ICryptModule {
    
    private $crypt_module;
    
    function __construct($module_name = null) {
    	
        if (! $module_name)
        	$module_name = MainConfig::$default_crypt_module;
        	
        switch($module_name) {
            case 'none':
		    $this->crypt_module = new CryptNone();		   
		    break;
		default: // == 'hash'
		    $this->crypt_module = new CryptHash();          
        }
    }
    
    /* 
     * Delegation of interface methods
     */ 
    public function decryptResult($crypted_result,$crypt_data,$password = '') {
        return $this->crypt_module->decryptResult($crypted_result,$crypt_data,$password);
    }
    
    public function encryptResult($plain_result,$crypt_data) {
        return $this->crypt_module->encryptResult($plain_result,$crypt_data);
    }
    
    public function generateCryptData($password) {
        return $this->crypt_module->generateCryptData($password);
    }

     
 }
?>
