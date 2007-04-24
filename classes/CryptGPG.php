<?php
/*
 * Created on 23.04.2007
 *
 * Saves password and result with GPG encoded into the database
  * 
  * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
  * 
 */
 class CryptGPG implements ICryptModule {
     
    private $gpg; 
    private $project_id;
    private $member_id;
    function __construct($project_id = 0, $member_id = 0) {
        $this->project_id = $project_id;
        $this->member_id = $member_id;
        
        $keyring_home = sprintf(MainConfig::$crypt_info['gpg']['keyring_home'],$project_id);
        
        // create absolute path:
        if (substr($keyring_home,0,1) != '/' && 
            substr($keyring_home,0,1) != "\\"&& 
            substr($keyring_home,1,1) != ":")
        	$keyring_home = dirname($_SERVER["SCRIPT_FILENAME"]) . "/$keyring_home";
        
        // create keyringdir
        if (! file_exists($keyring_home))
        	mkdir($keyring_home);
        	
        // Create keyrings
        if (! file_exists("$keysing_home/secring.gpg") || ! file_exists("$keysing_home/pubring.gpg"))
        
        $this->gpg = new gnuPG(MainConfig::$crypt_info['gpg']['program_path'], $keyring_home);
 	}
     
    public function decryptResult($crypted_result,$crypt_data,$password = '') {
        $key_id = $crypt_data;
        
        $decrypted_data = $this->gpg->Decrypt($key_id, $password, $crypted_result);
                
        if ($decrypted_data)
            return $decrypted_data;
        else
            return null;
    }
    
    public function encryptResult($plain_result,$crypt_data) {
        $key_id = $crypt_data;
        $encrypted_data = $this->gpg->SimpleEncrypt($key_id, $plain_result);
        
		if (! $encrypted_data)  
		   throw new GPGException($this->gpg->error);
		   
        return $encrypted_data;
    }
    
    public function generateCryptData($password){
        
        $key_name = sprintf('%09d@%03d.reson',$this->member_id,$this->project_id);
		$generated_key = $this->gpg->GenKey($key_name, 
                                           'Reson-key', 
                                           $key_name, 
                                           $password,
                                           0,
                                           MainConfig::$crypt_info['gpg']['key_type'],
                                           MainConfig::$crypt_info['gpg']['key_length'],
                                           'ELG-E',
                                           MainConfig::$crypt_info['gpg']['subkey_length']);
		
		if (! $generated_key)  
		   throw new GPGException($this->gpg->error);
		   
		// get the all keys
		$keys = $this->gpg->ListKeys('public',$key_name);
		$generated_key_id = $keys[0]['KeyID'];
        return $generated_key_id;
    }
    
 }
 
 class GPGException extends DatabaseException {
     //
 }
?>
