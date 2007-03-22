<?php
/*
 * Created on 21.03.2007
 *
 * The methods a crypt-module has to support
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
interface ICryptModule {
    
    public function decryptResult($crypted_result,$crypt_data,$password = '');
    
    public function encryptResult($plain_result,$crypt_data);
    
    public function generateCryptData($password);
    
    
}
?>
