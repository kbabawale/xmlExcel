<?php 

$filename = "ClaimsXMLOvercharge2.xml";

$feed = simplexml_load_file($filename) or die('Problem reading xml file. Try again');

// echo "<pre>";
// print_r($feed);
// echo "</pre>";
foreach($feed->message->documentCommand->documentCommandOperand->debitCreditAdvice as $item){
    //foreach($item->debitCreditDetail as $product){
        
        echo "<pre>";
    if(isset($item->debitCreditDetail[0]->debitCreditReference[3]->reference->entityIdentification->contentOwner->additionalPartyIdentification->additionalPartyIdentificationValue)){
        echo $item->debitCreditDetail[0]->debitCreditReference[3]->reference->entityIdentification->contentOwner->additionalPartyIdentification->additionalPartyIdentificationValue;
    }else{
        echo $item->debitCreditDetail[0]->debitCreditReference[2]->reference->entityIdentification->contentOwner->additionalPartyIdentification->additionalPartyIdentificationValue;
    }
        echo "</pre>";
        
        
        //echo $product->reference;
        //if($k == 1){
            //$ClaimArray['REASONFORCLAIM_'.$Counter] = $product->adjustmentReason->messageReason;
            //foreach($product->debitCreditReference as $pro){
                // if($product->reference['entityType'] == 4){
                //     //$ClaimArray['TRUCKDETAILS_'.$Counter] = $pro->entityIdentification->contentOwner->additionalPartyIdentification->additionalPartyIdentificationValue;
                // }
            //}
        //}
    //}
}

?>