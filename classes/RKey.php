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
 	    $project_id = strtoupper($project_id);
 		if (! $member_id) {
 			// The String in $project_id is parsed
 			if (preg_match('/^([0-9]+)-([0-9]+)([0-9X])$/i',$project_id,$elements)) {
 			    $this->project_id = $elements[1];
 			    $this->member_id = $elements[2];
 			    if ($this->getChecksum() != $elements[3]) 
					throw new RKeyException('Checksum error');
 			} else 
 			    throw new RKeyException(sprintf('String "%s" is not a valid RKey.',$project_id));
 		} else {
 		    $member_id = strtoupper($member_id);
 			$this->project_id = $project_id;
 			$this->member_id = $member_id;
 		}
 	}
 	/**
 	 * Calculates the checksum (similar to ISBN)
 	 * member_id is assumed to be smaller 10^10
 	 */
	public function getChecksum() {
		$sum = 0;
		$numbers = str_split(strrev(sprintf("%d%09d",$this->project_id,$this->member_id)));
		for ($i = 1; $i <= count($numbers);$i++) {
		    $sum += $i*intval($numbers[$i-1]);
		}
		return $this->DICT[$sum % 11];
	}
	
	public function getProjectId() {
	    return $this->project_id;
	}
	
	public function getMemberId() {
	    return $this->member_id;
	}
	
	public function __toString() {
		return sprintf("%03d-%05d%s",$this->project_id,$this->member_id,$this->getChecksum());
	}
 }
 
  /**
  * An exception to throw if there is a rkey error
  */
 class RKeyException extends Exception {
   //
 }
?>
