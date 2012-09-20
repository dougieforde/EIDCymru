<?php
class ScotEID_MoveDocPDF extends TCPDF
{
    // copy of the ScotEID_LotDetailPDF class

  private $lot;

    // Page footer
  public function Footer() {
      // Position at 15 mm from bottom
      $this->SetY(-15);
      // Set font
      $this->SetFont('helvetica', 'I', 8);
      // Page number
      if($this->lot)
        {
            $samref = $this->lot->get_sams_movement_reference();
            $county = substr($this->lot->get_destination_location(),0,2);
        }
      $link = 'www.scoteid.com/r&id=' .$samref;
      // for consignments to England don't put in the link just put the unique ref
      if($county < 65 )
      {
          $link = "ScotEID Reference: " . $samref ;
      }
      $this->Cell(80, 10, 'Printed: ' . date("d/m/Y H:m") , 0, false, 'L', 0, '', 0, false, 'T', 'M');
      $this->Cell(80, 10,  $link , 0, false, 'L', 0, 'http://' .$link, 0, false, 'T', 'M');
      $this->Cell(0, 10, 'Ref: '. $samref ." ",  0, false, 'L', 0, '', 0, false, 'T', 'M');      
  }
	
	public function __construct($lots) {
	  if(!is_array($lots)) {
	    $lots = array($lots);
	  }
	  
	  // TODO: might be better to pass an instance of ScotEID_User in here now
	  global $user;
    
  	parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$title  = "ScotEID Information Centre www.scoteid.com";  
		$header = "Huntly Business Centre, 83 Gordon Street, Huntly\nAB54 8FG Tel: 01466 794323 Fax: 01466 792801";
		
		// set document information
		$this->SetCreator('ScotEID');
		$this->SetAuthor('ScotEID');
        //$pdf->SetSubject('ScotEID Pig Movement Document');
        //$pdf->SetKeywords('ScotEID, pig, movement, document');

		// set default header data
		$this->SetHeaderData('new_scoteid_logo.png', 25, $title, $header);

		// set header and footer fonts
		$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		$this->SetFont('helvetica', '', 10, '', true);

		//set margins
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$this->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$this->setLanguageArray($l);

    foreach($lots as $lot) {
      if(!$lot->is_saved()) {
  	    continue;
  	  }

  		$this->AddPage();  	  
  	  $this->lot = $lot;
  	  
  		$this->WriteHTML(theme("movement_document", $lot));

      //**************** CUSTOMISE HEADER LOGOS ***********************
      // find the keepers group
      $keeper = ScotEID_Keeper::first(array('conditions' => array('uid' => $user->uid)));
      if($keeper)
      {
          $group_id = $keeper->get_pig_producer_group_id();
      }
      // vion logo
      if($user->name =="vion_brydock" || $lot->get_destination_location() == "97/849/8000" || $group_id ==2)
      {
          $img_file = K_PATH_IMAGES.'vion.jpeg';
          $this->Image($img_file, 130, 5, 18, null, 'JPEG', 'http://www.vionfood.co.uk/', '', true, 300, '', false, false, 0, false, false, false);
      }
      //scotlean logo
      if( $group_id ==4)
      {
          $img_file = K_PATH_IMAGES.'scotlean.gif';
          $this->Image($img_file, 155, 5, 16.5, null, 'GIF', 'http://http://www.scotlean.com/', '', true, 300, '', false, false, 0, false, false, false);
      }
      // scottish pig producers logo
      if($user->name =="scot_pig_prod" || $group_id ==3)
      {
          $img_file = K_PATH_IMAGES.'spp.jpeg';
          $this->Image($img_file, 155, 5, 22, null, 'JPEG', '', '', true, 300, '', false, false, 0, false, false, false);        
      }

  		// add 2d barcode 
  		$barcode = "http://www.scoteid.com/r&id=" . $lot->get_sams_movement_reference() ;
      $this->write2DBarcode($barcode, 'QRCODE,H', 180, 5, 100, 100, $style, 'N');
    }
   
        // additional page if moving many individually identified animals
        $tr = $lot->get_or_create_transport_details();
		if(strlen($tr->get_individual_ids()) > 255)
		{
		    $this->AddPage();
		    $this->WriteHTML("<h2>Individual Animal Identities</h2><br><multicol cols=3>" . str_replace("\n","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$tr->get_individual_ids()));
		}      

	}
}
?>
