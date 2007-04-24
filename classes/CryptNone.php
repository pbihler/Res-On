<?php
/*
 * Created on 21.03.2007
 *
 * Saves password and result in plaintext into the database
  * 
  * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
  * 
 */
 class CryptNone implements ICryptModule {
   
   function __construct($project_id = 0, $member_id = 0) {
   }
   
    public function decryptResult($crypted_result,$crypt_data,$password = '') {
        if ($password == $crypt_data)
            return $crypted_result;
        else
            return null;
    }
    
    public function encryptResult($plain_result,$crypt_data) {
        return $plain_result;
    }
    
    public function generateCryptData($password){
        return $password;
    }
    
 }
?>
