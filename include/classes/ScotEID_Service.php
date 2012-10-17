<?php

/**
 * Wrapper class providing a configured SoapServer
 */
class ScotEID_Service
{
  private $server;
    
  public function __construct() {
        
    $classmap = array(
      'AbstractRequest_Structure'                   => 'ScotEID_AbstractRequest',
      'AbstractResponse_Structure'                  => 'ScotEID_AbstractResponse',
      
      // requests
      'CreateIncompleteLotRequest_Structure'        => 'ScotEID_CreateIncompleteLotRequest',
      'UpdateIncompleteLotRequest_Structure'        => 'ScotEID_UpdateIncompleteLotRequest',
      'UpdateIncompleteLotByReferenceRequest_Structure' => 'ScotEID_UpdateIncompleteLotByReferenceRequest',
      'CreateCompleteLotRequest_Structure'          => 'ScotEID_CreateCompleteLotRequest',
      'GetIncompleteLotRequest_Structure'           => 'ScotEID_GetIncompleteLotRequest',
      'CompleteLotRequest_Structure'                => 'ScotEID_CompleteLotRequest',
      'FindIncompleteLotsRequest_Structure'         => 'ScotEID_FindIncompleteLotsRequest',
      'FindIncompleteLotDatesRequest_Structure'     => 'ScotEID_FindIncompleteLotDatesRequest',
      'GetIncompleteLotsRequest_Structure'          => 'ScotEID_GetIncompleteLotsRequest',
      'SplitIncompleteLotAtRequest_Structure'       => 'ScotEID_SplitIncompleteLotAtRequest',
      'DeleteIncompleteLotRequest_Structure'        => 'ScotEID_DeleteIncompleteLotRequest',
      'MergeIncompleteLotsRequest_Structure'        => 'ScotEID_MergeIncompleteLotsRequest',
      'FindCompleteLotsRequest_Structure'           => 'ScotEID_FindCompleteLotsRequest',
      'GetCompleteLotRequest_Structure'			        => 'ScotEID_GetCompleteLotRequest',
      'GetCompleteLotByReferenceRequest_Structure'	=> 'ScotEID_GetCompleteLotByReferenceRequest',
      'CancelCompleteLotByReferenceRequest_Structure' => 'ScotEID_CancelCompleteLotByReferenceRequest',
      'UpdateCompleteLotByReferenceRequest_Structure' => 'ScotEID_UpdateCompleteLotByReferenceRequest',
      
	    'GetCompleteLotsRequest_Structure'		        => 'ScotEID_GetCompleteLotsRequest',
	    'GetAnimalsOnHoldingRequest_Structure'        => 'ScotEID_GetAnimalsOnHoldingRequest',
	    
	    'FindLotsByDepartureLocationRequest_Structure'   => 'ScotEID_FindLotsByDepartureLocationRequest',
	    'GetLotsByDepartureLocationRequest_Structure'    => 'ScotEID_GetLotsByDepartureLocationRequest',
	    'FindLotsByDestinationLocationRequest_Structure' => 'ScotEID_FindLotsByDestinationLocationRequest',
	    'GetLotsByDestinationLocationRequest_Structure'  => 'ScotEID_GetLotsByDestinationLocationRequest',
	    
	    'GetSummaryByLotRequest_Structure'				 => 'ScotEID_GetSummaryByLotRequest',
			'GetSummaryByMovementRequest_Structure'		 => 'ScotEID_GetSummaryByMovementRequest',

      // responses
      'GetIncompleteLotResponse_Structure'       => 'ScotEID_GetIncompleteLotResponse',
      'UpdateIncompleteLotResponse_Structure'    => 'ScotEID_UpdateIncompleteLotResponse',
      'CreateCompleteLotResponse_Structure'      => 'ScotEID_CreateCompleteLotResponse',
      'CreateIncompleteLotResponse_Structure'    => 'ScotEID_CreateIncompleteLotResponse',
      'CompleteLotResponse_Structure'            => 'ScotEID_CompleteLotResponse',
      'FindIncompleteLotsResponse_Structure'     => 'ScotEID_FindIncompleteLotsResponse',
      'FindIncompleteLotDatesResponse_Structure' => 'ScotEID_FindIncompleteLotDatesResponse',
      'GetIncompleteLotsResponse_Structure'      => 'ScotEID_GetIncompleteLotsResponse',
      'SplitIncompleteLotAtResponse_Structure'   => 'ScotEID_SplitIncompleteLotAtResponse',
      'DeleteIncompleteLotResponse_Structure'    => 'ScotEID_DeleteIncompleteLotResponse',
      'MergeIncompleteLotsResponse_Structure'    => 'ScotEID_MergeIncompleteLotsResponse',
      'FindCompleteLotsResponse_Structure'       => 'ScotEID_FindCompleteLotsResponse',
      'GetCompleteLotResponse_Structure'	       => 'ScotEID_GetCompleteLotResponse',
      'GetCompleteLotByReferenceResponse_Structure'	=> 'ScotEID_GetCompleteLotByReferenceResponse',
      'CancelCompleteLotByReferenceResponse_Structure' => 'ScotEID_CancelCompleteLotByReferenceResponse',
	    'GetCompleteLotsResponse_Structure'		     => 'ScotEID_GetCompleteLotsResponse',
	    'UpdateCompleteLotResponse_Structure'      => 'ScotEID_UpdateCompleteLotResponse',

	    'GetAnimalsOnHoldingResponse_Structure'    => 'ScotEID_GetAnimalsOnHoldingResponse',
	    
	    'FindLotsByDepartureLocationResponse_Structure'   => 'ScotEID_FindLotsByDepartureLocationResponse',
	    'GetLotsByDepartureLocationResponse_Structure'    => 'ScotEID_GetLotsByDepartureLocationResponse',
	    'FindLotsByDestinationLocationResponse_Structure' => 'ScotEID_FindLotsByDestinationLocationResponse',
	    'GetLotsByDestinationLocationResponse_Structure'  => 'ScotEID_GetLotsByDestinationLocationResponse',
      
      'GetCompleteLotResponse_Structure'	     	 => 'ScotEID_GetCompleteLotResponse',
	  	'GetCompleteLotsResponse_Structure'				 => 'ScotEID_GetCompleteLotsResponse',
      'GetSummaryByLotResponse_Structure'				 => 'ScotEID_GetSummaryByLotResponse',
			'GetSummaryByMovementResponse_Structure'	 => 'ScotEID_GetSummaryByMovementResponse',
			
      // models
      // 'Lot_Structure'                            => 'ScotEID_Lot',
      'Holding_Type'                             => 'ScotEID_Holding',
      'TagReading_Structure'                     => 'ScotEID_TagReading',
      'HexTagID_Type'                            => 'ScotEID_TagID',
      'ISO24631TagID_Type'                       => 'ScotEID_ISO24631TagID',
      'Animal_Structure'                         => 'ScotEID_Animal',
      'FlockTag_Structure'                       => 'ScotEID_FlockTag',
			'SummaryDate_Structure'										 => 'ScotEID_SummaryDate',
      'Keeper_Structure'                         => 'ScotEID_Keeper',
      'FCI_Structure'                            => 'ScotEID_FCI',
      'TransportInformation_Structure'           => 'ScotEID_TransportDetails',
      
      // faults
      'LotValidationFault_Structure'             => 'ScotEID_LotValidationFault',
      'UnmergeableLotsFault_Structure'           => 'ScotEID_UnmergeableLotsFault',
      'SecurityFault_Structure'                  => 'ScotEID_SecurityFault'
    );

    $server = new SoapServer(ScotEID_Service::getWSDLPath() . ".local", 
      array(
        'classmap' => $classmap, 
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        'typemap'  => array(
         array(
           'type_name' => 'HexTagID_Type',
           'type_ns'   => 'http://api.scoteid.com/api/',
           'to_xml'    => function($tag_id) { return $tag_id ? $tag_id->to_xml() : null; },
           'from_xml'  => function($xml)    { return ScotEID_TagID::from_xml($xml); }
         ),
         array(
            'type_name' => 'ISO24631TagID_Type',
            'type_ns'   => 'http://api.scoteid.com/api/',
            'to_xml'    => function($tag_id) { return $tag_id ? $tag_id->to_xml() : null; },
            'from_xml'  => function($xml)    { return ScotEID_ISO24631TagID::from_xml($xml); }
         )
       )
      )
    );
    $server->setClass('ScotEID_SoapServer');
    $this->server = $server;
  }
  
  public function handle($soap_request = null) {
    if($soap_request) {
      return $this->server->handle($soap_request);
    } else {
      return $this->server->handle();
    }
  }
    
  public static function getWSDLPath() {
    return SCOTEID_WEBSERVICES_ROOT . "/api.wsdl";
  }
}
?>