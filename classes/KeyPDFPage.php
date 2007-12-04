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
     
     
    private $pdfTexts = array();
    private $url = '';
    
    function __construct() {        
         parent::__construct();
         $db = Database::getInstance();
		 $this->pdfTexts = $db->getProjectPdfTexts($this->project->getId());
		 
		 //Construct access URL:
		 if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
		 	$this->url = 'https://';
		 else 
		 	$this->url = 'http://';
		 $PHP_SELF = $_SERVER['PHP_SELF'];
		 $this->url .= $_SERVER['HTTP_HOST'] . substr($PHP_SELF,0,strrpos($PHP_SELF,'/'));
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
		    if (count($keys) > 0 && count($keys) == count($pwds)) {
		        for ($i = 0; $i < count($keys); $i++) {
		            $this->AddCredentialPage($pdf,$keys[$i],$pwds[$i]);
		        }		    
		        return;
		    }
		}
		$this->renderError($pdf,Messages::getString('KeyPDFPage.NoValidCredentials'));	    
 	}
 	
 	private function AddCredentialPage($pdf,$key,$pwd) {
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(0,18,html_entity_decode(Messages::getString('KeyPDFpage.Credentials')),0,1); 
		
		
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->WriteHTML(0,14,sprintf($this->pdfTexts['introduction'],$key)); 		
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
		$pdf->WriteHTML(0,14,sprintf($this->pdfTexts['hint'],$this->url)); 		
		$pdf->Ln();
 	    
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
