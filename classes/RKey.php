<?php
/*
 * Created on 30.03.2007 by pascal
 *
 * Class to handle the Reson-Keys
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 * 
 */
 class RKey {
 	
    private $project_id = 0;
    private $member_id = 0;
    
    private $DICT = array('0','1','2','3','4','5','6','7','8','9','X');
    
 	function __construct ($project_id,$member_id = null) {
 		if (! $member_id) {
 			// The String in $project_id needs to be parsed
 		} else {
 			$this->project_id = $project_id;
 			$this->member_id = $member_id;
 		}
 	}
 	/**
 	 * Calculates the checksum (similar to ISBN)
 	 * member_id is assumed to be smaller 10^10
 	 */
	function getChecksum() {
		$sum = 0;
		$numbers = str_split(strrev(sprintf("%d%09d",$this->project_id,$this->member_id)));
		for ($i = 1; $i <= count($numbers);$i++) {
		    $sum += $i*intval($numbers[$i-1]);
		}
		return $this->DICT[$sum % 11];
	}
	
	public function __toString() {
		return sprintf("%03d-%05d%s",$this->project_id,$this->member_id,$this->getChecksum());
	}
 }
?>
