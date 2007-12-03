<?php
/*
 * Created on 21.03.2007
 *
 * Saves password and result in plaintext into the database
  * 
  * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
  * 
 */
 class CryptHash implements ICryptModule {
     
   function __construct($project_id = 0, $member_id = 0) {
   }
     
    public function decryptResult($crypted_result,$crypt_data,$password = '') {
        if (crypt($password, $crypt_data) == $crypt_data) 
        	return base64_decode($this->xorCrypt($crypted_result,$crypt_data));
        else
            return null;
    }
    
    public function encryptResult($plain_result,$crypt_data) {
        return base64_encode($this->xorCrypt($plain_result,$crypt_data));
    }
    
    public function generateCryptData($password){
        return crypt($password,Config::$crypt_info['hash']['salt']);
    }
    
    /*
     * Does a so called "xor-encryption"
     */
    private function xorCrypt($alice,$crypt) {
        $bob = '';
        $alicesplit = str_split($alice);
        $cryptsplit = str_split($crypt);
        $cryptlen = strlen($crypt);
        for ($i = 0; $i < strlen($alice);$i++) {
            $bob .= $alicesplit[$i] xor $cryptsplit[$i % $cryptlen];
        }
        return $bob;
    }
    
 }
?>
