<?php
/*
 * Created on 17.04.2007 by bihler
 *
 * Generates a PDF-File containing instructions for using R-Keys
 * 
 * Licenced under GPL: http://www.gnu.org/licenses/gpl.txt
 *
 */
 

 class KeyPDFPage extends AdminPage {
     
     
    private $url = '';
    protected $introduction= '';
    protected $hint = '';
    
    function __construct() {        
         parent::__construct();
		 
		 //Construct access URL:
		 if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
		 	$this->url = 'https://';
		 else 
		 	$this->url = 'http://';
		 $PHP_SELF = $_SERVER['PHP_SELF'];
		 $this->url .= $_SERVER['HTTP_HOST'] . substr($PHP_SELF,0,strrpos($PHP_SELF,'/'));
		 
		 $this->introduction = $this->project->getIntroduction();
		 if (isset($_POST['introduction'])) 
		    $this->introduction = stripslashes($_POST['introduction']);		    
		    
		 $this->hint = $this->project->getHint();
		 if (isset($_POST['hint'])) 
		    $this->hint = stripslashes($_POST['hint']);
    }
    
    /**
     * Replaces variables in the string
     */
    private function replaceVariables($s,$key,$pwd) {
    	return preg_replace(array("/%RKEY%/i","/%PASSWORD%/i","/%URL%/i","/%PROJECT%/i"),
    	                  array($key,$pwd,$this->url,$this->project->getName()),
    	                  $s);
    }
    
      /**
       * Overwrites render to generate PDF output
       */
 	public function render() {
		
        $pdf=new PDF_HTML(Config::$pdf_settings['orientation'],'pt',Config::$pdf_settings['format']);
 	    $this->renderPDF($pdf);
		$pdf->Output("rkeys.pdf","I");
 	}
 	
 	/**
 	 * Renders the actual PDF
 	 * 
 	 * @param FPDF pdf
 	 * 
 	 */
 	private function renderPDF($pdf) {
		if (isset($_POST['key']) || isset($_POST['pwd'])) {
		    $keys = $_POST['key'];
		    $pwds = $_POST['pwd'];
		    $minimal = $_POST['minimal'];
		    if (count($keys) > 0 && count($keys) == count($pwds)) {
		        if ($minimal) {
		        	$this->WriteMinimalHandout($pdf,$keys,$pwds);
		        } else {
			        for ($i = 0; $i < count($keys); $i++) {
			 			$introduction = $this->replaceVariables($this->introduction,$keys[$i],$pwds[$i]);
			 			$hint = $this->replaceVariables($this->hint,$keys[$i],$pwds[$i]);
			            $this->AddCredentialPage($pdf,$keys[$i],$pwds[$i],$introduction,$hint);
			           
			        }	
		        }	    
		        return;
		    }
		}
		$this->renderError($pdf,Messages::getString('KeyPDFPage.NoValidCredentials'));	    
 	}
 	
 	private function AddCredentialPage($pdf,$key,$pwd,$introduction,$hint) {
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(0,18,html_entity_decode(Messages::getString('KeyPDFpage.Credentials')),0,1); 
		
		
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->WriteHTML(0,14,$introduction); 		
		$pdf->Ln();
		
		$pdf->Ln();
		$pdf->SetFont('Courier','B',14);
		$pdf->Cell(100,16,html_entity_decode(Messages::getString('General.RKey')) . ':'); 
		$pdf->Cell(100,16,$key); 
		$pdf->Ln();
		$pdf->Cell(100,16,html_entity_decode(Messages::getString('General.Password')).':'); 
		$pdf->Cell(100,16,$pwd); 
		$pdf->Ln();
		
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->WriteHTML(0,14,$hint); 		
		$pdf->Ln();
 	    
 	}
 	
 	private function WriteMinimalHandout($pdf,$keys,$pwds) {
 	
 	   $count_x = 3;
 	   $count_y = 10;
 	   $w = $pdf->getPageWidth() / $count_x;
 	   $h = $pdf->getPageHeight() / $count_y;
		
 	    
 	    $pdf->SetFillColor(255,255,255);
 	    
 	    // Calculate title properties
 	    $max_title_width = $w*0.8;
 	    $title_font_height = 10;
		$pdf->SetFont('Arial','B',$title_font_height);
		$title = " " . $this->project->getName() . " "; 
		while($pdf->GetStringWidth($title) > $max_title_width) {
		    $pdf->SetFont('Arial','B',--$title_font_height);
		}
		$title_width = $pdf->GetStringWidth($title);
 	    
		$shift = 0;
		$key_count = 0;
		
		while (true) {
			$cell_count = count($keys)-$shift;
			
			if ($cell_count <= 0)
			  break;
			
			
			// Print R-Keys and Passwords:
	 	    $this->addUrlPage($pdf,$this->url);
	 	    
	 	    $font_height = 10;
			$pdf->SetFont('Courier','B',$font_height);
			$i = 0;
	 	    for ($y = 0; $y < $count_y; $y++) {
		 	    for($x = 0;$x < $count_x;$x++) {
		 	        $i++;
		 	        if ($i > $cell_count)
		 	          break;
		 	          
		 	        
		 	        $line1 = Messages::getString('General.RKey') . ": " . $keys[$key_count];
		 	        $line2 = Messages::getString('General.Password') . ": " . $pwds[$key_count];
	
		 	        $width_line1 = $pdf->GetStringWidth($line1)+2;
		 	        $width_line2 = $pdf->GetStringWidth($line2)+2;
		 	        $width = max($width_line1,$width_line2);
		 	    	
		 	        $pdf->SetXY($x*$w+($w-$width)/2,$y*$h+($h- $font_height-3)/2);
		 	    	$pdf->Cell($width_line1, $font_height+2,"$line1",0,0,'C',true); 
		 	    	
		 	        $pdf->SetXY($x*$w+($w-$width)/2,$y*$h+($h- $font_height-3)/2 + $font_height+2);
		 	    	$pdf->Cell($width_line2, $font_height+2,"$line2",0,0,'C',true); 
		 	    	
		 	    	
		 	        $key_count++;
		 	    }
	 	    }
			
			
			//paint backside
			
	 	    $this->addUrlPage($pdf,$this->url);
	 	    
			$pdf->SetFont('Arial','B',$title_font_height);
			$i = 0;
	 	    for ($y = 0; $y < $count_y; $y++) {
		 	    for($x = $count_x-1; $x >=0 ;$x--) {
		 	        $i++;
		 	        if ($i > $cell_count)
		 	          break;
		 	          
		 	    	$pdf->SetXY($x*$w+($w-$title_width )/2,$y*$h+($h- $title_font_height-2)/2);
		 	    	$pdf->Cell($title_width , $title_font_height+2,$title,0,0,'C',true); 
		 	    }
	 	    }
	 	    $shift += $count_x*$count_y;
		}
		
 	}
 	
 	private function addUrlPage($pdf,$url) {
 		$url_string = "";
 	    for ($i = 0; $i < strlen($url); $i++) {
 	         $url_string .= substr($url,$i,1) . " ";
 	    }
 	    $url_string .= "  ";
 	    
 	    $pdf->AddPage();
		$pdf->SetMargins(0,0);
		$pdf->SetAutoPageBreak(false);
		
		
		$pdf->SetFont('Arial','',5);
		$pdf->SetTextColor(20,20,20);
		
		$pdf->SetXY(0,0);
		$pageHeight = $pdf->GetPageHeight();
		while ($pdf->GetY() < $pageHeight)
			$pdf->Write(6,$url_string);
			
		$pdf->SetXY(0,0); 

 	}
 	
 	protected function renderError($pdf,$error) {
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->SetTextColor(255,0,0);
		$pdf->Write(18,sprintf('%s: %s',html_entity_decode(Messages::getString('General.Error')),$error)); 
 	    
 	}
 }
 
/**
 * Borrowed from http://www.fpdf.org/en/script/script41.php
 * 
 */
class PDF_HTML extends FPDF
{
    var $B=0;
    var $I=0;
    var $U=0;
    var $HREF='';
    var $ALIGN='';

    function WriteHTML($width, $height, $html)
    {
        //HTML parser
        $html=html_entity_decode(str_replace("\n",' ',$html));
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Text
                if($this->HREF)
                    $this->PutLink($height,$this->HREF,$e);
                elseif($this->ALIGN == 'center')
                    $this->Cell($width,$height,$e,0,1,'C');
                else
                    $this->Write($height,$e);
            }
            else
            {
                //Tag
                if($e{0}=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extract properties
                    $a2=split(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $prop=array();
                    foreach($a2 as $v)
                        if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
                            $prop[strtoupper($a3[1])]=$a3[2];
                    $this->OpenTag($tag,$prop,$height);
                }
            }
        }
    }

    function OpenTag($tag,$prop,$height)
    {
        //Opening tag
        $tag = strtoupper($tag);
        if($tag=='B' or $tag=='I' or $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF=$prop['HREF'];
        if($tag=='BR')
            $this->Ln($height);
        if($tag=='P')
            $this->ALIGN=$prop['ALIGN'];
        if($tag=='HR')
        {
            if( $prop['WIDTH'] != '' )
                $Width = $prop['WIDTH'];
            else
                $Width = $this->w - $this->lMargin-$this->rMargin;
            $this->Ln(2);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetLineWidth(0.4);
            $this->Line($x,$y,$x+$Width,$y);
            $this->SetLineWidth(0.2);
            $this->Ln(2);
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if($tag=='B' or $tag=='I' or $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='P')
            $this->ALIGN='';
    }

    function SetStyle($tag,$enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s)
            if($this->$s>0)
                $style.=$s;
        $this->SetFont('',$style);
    }

    function PutLink($height,$URL,$txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write($height,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
}
?>
