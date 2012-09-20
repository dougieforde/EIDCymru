<?php
class ScotEID_LotDetailPDF extends TCPDF
{
	private $lot = null;
	
	public function __construct($lot) {
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$this->lot = $lot;

		$title  = "www.ScotEID.com Unique Reference: " . $lot->get_sams_movement_reference();
		$header = "Date:" . strftime("%d/%m/%y", $lot->get_lot_date()) . " Lot:" . $lot->get_lot_number() .
		" Sheep:" . $lot->get_head_count() . "\n" . 	"Movement: " . $lot->get_departure_location()->getCPH() . " -> " .
			$lot->get_read_location()->getCPH() . " -> " .
			$lot->get_destination_location()->getCPH();
		
		// set document information
		$this->SetCreator('ScotEID');
		$this->SetAuthor('ScotEID');

		// set default header data
		$this->SetHeaderData('scoteid_logo.png', 80, $title, $header);
		
		// set header and footer fonts
		$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		$this->setLanguageArray($l);
	}

	public function set_body($body) {
		$this->AddPage();
		$this->WriteHTML($body);
	}
}
?>
