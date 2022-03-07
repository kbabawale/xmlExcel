<?php

try{

    //edit xml file - remove prefixes
    $xml = @file_get_contents("TempUploads/".$filename);
    
    //format certain tags
    $xml = str_replace('<sh:', '<', $xml);
    $xml = str_replace('</sh:', '</', $xml);
    
    $xml = str_replace('<pay:', '<', $xml);
    $xml = str_replace('</pay:', '</', $xml);
    
    $xml = str_replace('<eanucc:', '<', $xml);
    $xml = str_replace('</eanucc:', '</', $xml);
    
    @file_put_contents("TempUploads/".$filename, $xml);
    //##############################################################

    $feed = @simplexml_load_file("TempUploads/".$filename) or die('Problem reading xml file. Try again');

    //fetch details of xml files and store into an array
    $ClaimArray = array(); $ClaimArrayCount=array();
    $Counter = 0; $brancharray = array();
    //get details of each return claim
    foreach($feed->message->documentCommand->documentCommandOperand->debitCreditAdvice as $item){
        $ClaimArrayCount[] = $Counter;
        $ClaimArray['TOTALFIGURE_'.$Counter] = 0;
        $ClaimArray['MAINTITLE1_'.$Counter] = 'SHOPRITE NIGERIA (REG. NO. RC611080)';
        $ClaimArray['MAINTITLE2_'.$Counter] = 'GOODS RETURNED CLAIM NUMBER '. $item->debitCreditAdviceIdentification->uniqueCreatorIdentification;
        $ClaimArray['MAINTITLE3_'.$Counter] = $item->debitCreditAdviceIdentification->uniqueCreatorIdentification;
        $ClaimArray['THIRDLINENOTICE_'.$Counter] = '(PLEASE REPLACE YOUR CREDIT NOTE NUMBER ON YOUR STATEMENT WITH ABOVE CLAIM NUMBER)';
        $ClaimArray['DATECREATED_'.$Counter] = 'Created on '.$item['creationDateTime'];
        $ClaimArray['DATECREATED2_'.$Counter] = 'Created on '.$item['creationDateTime'];
        $ClaimArray['DIVISION_'.$Counter] = '0 - Unknown';
        $ClaimArray['BRANCH_'.$Counter] = $item->buyerSellerPartyIdentification->buyerIdentification->additionalPartyIdentification[1]->additionalPartyIdentificationValue.' '.$item->buyerSellerPartyIdentification->buyerIdentification->additionalPartyIdentification[0]->additionalPartyIdentificationValue;
        $ClaimArray['BRANCH_NUMBER_'.$Counter] = $item->buyerSellerPartyIdentification->buyerIdentification->additionalPartyIdentification[0]->additionalPartyIdentificationValue;
        $ClaimArray['CREDITNOTENUMBER_'.$Counter] = '[ToBeEdit]';
        $ClaimArray['SUPPLIERINVOICENUMBER_'.$Counter] = '-';
        $ClaimArray['DOCUMENTSTATUS_'.$Counter] = 'New';
        $ClaimArray['DOCUMENTSTATUSDATE_'.$Counter] = '[ToBeEdited]';
        $ClaimArray['DELIVERYDATE_'.$Counter] = '[ToBeEdited]';
        $ClaimArray['ACCOUNTINGSUPPLIER_'.$Counter] = '118906 - BRAND CONCEPT STORES LIMITED';
        $ClaimArray['MERCHANDISINGSUPPLIER_'.$Counter] = '118906 00 - BRAND CONCEPT STORES LIMITED';
        $ClaimArray['REASON_'.$Counter] = $item->debitCreditDetail[0]->adjustmentReason->messageReason;

        $ClaimArray['TRUCKDETAILS1_'.$Counter] = $item->debitCreditDetail[0]->debitCreditReference[1]->reference->entityIdentification->contentOwner->additionalPartyIdentification->additionalPartyIdentificationValue;    
        if(preg_match('/-/', $ClaimArray['TRUCKDETAILS1_'.$Counter])){
            $temp = explode('-', $ClaimArray['TRUCKDETAILS1_'.$Counter]);

            for($o = 0; $o < count($temp); $o++){
                $ClaimArray['DRIVERNAME_'.$Counter] = $temp[0];
                $ClaimArray['VEHICLENUMBER_'.$Counter] = $temp[1];
            }   
        }else{
            $ClaimArray['DRIVERNAME_'.$Counter] = $ClaimArray['TRUCKDETAILS1_'.$Counter];
            $ClaimArray['VEHICLENUMBER_'.$Counter] = $ClaimArray['TRUCKDETAILS1_'.$Counter];
        }

        $ClaimArray['CC_'.$Counter] = array();
        //store unique branch number
        $brancharray[] = intval($ClaimArray['BRANCH_NUMBER_'.$Counter]);
    
        //get partial details of items (first round)
        $FIRSTPRODUCTCOUNTER = 1;
        foreach($item->debitCreditDetail as $product){
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['RETURNQUANTITY'] = $product->subLineDetail->quantity;
            //change quantity to 1 if default = 0
            if($ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['RETURNQUANTITY'] == 0){ 
                $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['RETURNQUANTITY'] = 1;
            }

            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['ITEMNUMBER'] = $product->subLineDetail->tradeItemIdentification->additionalTradeItemIdentification[0]->additionalTradeItemIdentificationValue;
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['ITEMDESCRIPTION'] = $product->subLineDetail->tradeItemIdentification->additionalTradeItemIdentification[1]->additionalTradeItemIdentificationValue;        
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['ITEMBARCODE'] = $product->subLineDetail->tradeItemIdentification->gtin;
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$FIRSTPRODUCTCOUNTER]['SUPPLIERREFNO'] = 0;
            $ClaimArray['CC_'.$Counter][] = $FIRSTPRODUCTCOUNTER;

            $FIRSTPRODUCTCOUNTER++;
        }
    
        //get partial details of items (second round)
        //foreach($ClaimArray['CC_'.$Counter] as $cc1){  
        $SECONDPRODUCTCOUNTER = 1;  
        foreach($item->extension->claimExtension->itemInformation as $product){
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['PACKSIZE'] = $product->packsize->value;
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['CONTROLNUMBER'] = $product->tradeAgreement->tradeAgreementReferenceNumber;
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['COSTPER'] = $product->costPer->value;
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['UNITCOST'] = $product->SUPPUnitCosts->value;
            $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['RETURNAMOUNT'] = $product->SUPPUnitCosts->value * $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['RETURNQUANTITY'];
            
            $ClaimArray['TOTALFIGURE_'.$Counter] += $ClaimArray['ITEMS_'.$Counter]['PRODUCT_'.$SECONDPRODUCTCOUNTER]['RETURNAMOUNT'];

            $SECONDPRODUCTCOUNTER++;
        }
        //}
    
        $Counter++;
    }//foreach end

    /** PHPExcel */
    include_once 'Classes/PHPExcel.php';

    /** PHPExcel_Writer_Excel2007 */ 
    include_once 'Classes/PHPExcel/Writer/Excel2007.php'; 

    // Create new PHPExcel object 
    $objPHPExcel = new PHPExcel(); 

    // Setting Excel file properties 
    // Change the properties detail as per your requirement 
    $objPHPExcel->getProperties()->setCreator("Kolapo Babawale"); 
    $objPHPExcel->getProperties()->setLastModifiedBy("Kolapo Babawale (softwaredeveloper2.ho@greatbrandsng.com)"); 
    $objPHPExcel->getProperties()->setTitle("XML DOCUMENT"); 
    $objPHPExcel->getProperties()->setSubject("SHOPRITE XML DOCUMENT"); 
    $objPHPExcel->getProperties()->setDescription("Excel file generated automatically"); 

    // Select current sheet 
    $objPHPExcel->setActiveSheetIndex(0); 
    // Add some data 
    //column number, which we will be incrementing 
    $rownum=1; 

    //add table headings for product list
    $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Return Date Format');
    $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", 'Location/Depot');
    $objPHPExcel->getActiveSheet()->SetCellValue('C'."$rownum", 'Outlet Name');
    $objPHPExcel->getActiveSheet()->SetCellValue('D'."$rownum", 'Return GRN No/Credit Note/CCV Number/Claim Number');
    $objPHPExcel->getActiveSheet()->SetCellValue('E'."$rownum", 'Invoice Number');
    $objPHPExcel->getActiveSheet()->SetCellValue('F'."$rownum", 'Item Code');
    $objPHPExcel->getActiveSheet()->SetCellValue('G'."$rownum", 'Description');
    $objPHPExcel->getActiveSheet()->SetCellValue('H'."$rownum", 'Quantity Cartons');
    $objPHPExcel->getActiveSheet()->SetCellValue('I'."$rownum", 'Quantity Pieces');
    $objPHPExcel->getActiveSheet()->SetCellValue('J'."$rownum", 'Lot Number');
    $objPHPExcel->getActiveSheet()->SetCellValue('K'."$rownum", 'Expiry Date (DD/MM/YY)');
    $objPHPExcel->getActiveSheet()->SetCellValue('L'."$rownum", 'Reason for return');
    $objPHPExcel->getActiveSheet()->SetCellValue('M'."$rownum", 'Driver Name');
    $objPHPExcel->getActiveSheet()->SetCellValue('N'."$rownum", 'Vehicle Plate Number');
    $objPHPExcel->getActiveSheet()->SetCellValue('O'."$rownum", 'Approved By');
    $objPHPExcel->getActiveSheet()->SetCellValue('P'."$rownum", 'Status');
    $rownum++;

    for ($i = 0; $i < count($ClaimArrayCount); $i++) 
    { 
        //for($k=1;$k<=$ClaimArray['ProductCount_'.$i];$k++) //loop through products
        foreach($ClaimArray['CC_'.$i] as $k) //loop through products
        { 
            $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", $ClaimArray['DATECREATED2_'.$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", ' ');
            $objPHPExcel->getActiveSheet()->SetCellValue('C'."$rownum", $ClaimArray['BRANCH_'.$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'."$rownum", $ClaimArray['MAINTITLE3_'.$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'."$rownum", ' ');
            $objPHPExcel->getActiveSheet()->SetCellValue('F'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['ITEMNUMBER']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['ITEMDESCRIPTION']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'."$rownum", ' ');
            $objPHPExcel->getActiveSheet()->SetCellValue('I'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['RETURNQUANTITY']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'."$rownum", ' ');
            $objPHPExcel->getActiveSheet()->SetCellValue('K'."$rownum", ' ');
            $objPHPExcel->getActiveSheet()->SetCellValue('L'."$rownum", $ClaimArray['REASON_'.$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'."$rownum", $ClaimArray['DRIVERNAME_'.$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue('N'."$rownum", $ClaimArray['VEHICLENUMBER_'.$i]);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'."$rownum", ' ');
            $objPHPExcel->getActiveSheet()->SetCellValue('P'."$rownum", ' ');
            //$ClaimArray['TRUCKDETAILS_'.$i]
            //$rownum++;
        }
        $rownum++;
    
    
    } //for

    // Create a write object to save the the excel 
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); 

    // save to a file
    //getenv("HOMEDRIVE") . getenv("HOMEPATH")."\Documents\\".basename($filename, '.xml') 
    $objWriter->save('TempUploads/'.basename($filename, '.xml').'.xlsx'); 

}catch(Exception $e){
    echo 'There was an error, please load the page again.';
}

?>