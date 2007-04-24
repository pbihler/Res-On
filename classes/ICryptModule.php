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
    
    function __construct($project_id=0, $member_id = 0);
    
    public function decryptResult($crypted_result,$crypt_data,$password = '');
    
    public function encryptResult($plain_result,$crypt_data);
    
    public function generateCryptData($password);
    
    
}
?>
