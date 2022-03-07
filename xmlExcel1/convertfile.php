<?php

try{

    //edit xml file - remove prefixes
    $xml = file_get_contents("TempUploads/".$filename);
    
    //format certain tags
    $xml = str_replace('<sh:', '<', $xml);
    $xml = str_replace('</sh:', '</', $xml);
    
    $xml = str_replace('<pay:', '<', $xml);
    $xml = str_replace('</pay:', '</', $xml);
    
    $xml = str_replace('<eanucc:', '<', $xml);
    $xml = str_replace('</eanucc:', '</', $xml);
    
    file_put_contents("TempUploads/".$filename, $xml);
    //##############################################################

    $feed = @simplexml_load_file("TempUploads/".$filename) or die('Problem reading xml file. Try again');

    //fetch details of xml files and store into an array
    $ClaimArray = array(); $ClaimArrayCount=array();
    $Counter = 0; $DontShow = array();$brancharray = array();
    //get details of each return claim
    foreach($feed->message->documentCommand->documentCommandOperand->debitCreditAdvice as $item){
        $ClaimArrayCount[] = $Counter;
        $ClaimArray['TOTALFIGURE_'.$Counter] = 0;
        $ClaimArray['MAINTITLE1_'.$Counter] = 'SHOPRITE NIGERIA (REG. NO. RC611080)';
        $ClaimArray['MAINTITLE2_'.$Counter] = 'GOODS RETURNED CLAIM NUMBER '. $item->debitCreditAdviceIdentification->uniqueCreatorIdentification;
        $ClaimArray['THIRDLINENOTICE_'.$Counter] = '(PLEASE REPLACE YOUR CREDIT NOTE NUMBER ON YOUR STATEMENT WITH ABOVE CLAIM NUMBER)';
        $ClaimArray['DATECREATED_'.$Counter] = 'Created on '.$item['creationDateTime'];
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
        $ClaimArray['REASONFORCLAIM_'.$Counter] = '[ToBeEdited]';
        $ClaimArray['TRUCKDETAILS_'.$Counter] = '[ToBeEdited]';
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

    //check frequency of values
    $arr_count = array_count_values($brancharray);
    
    $alreadydealtbranch = array();

    $duplicate = array();

    foreach ($brancharray as $key => $brancharray1) { //moves ten times or more

        if (array_key_exists($brancharray1, $arr_count) && ($arr_count["$brancharray1"] > 1) && (!in_array($brancharray1, $alreadydealtbranch)))
        {
            /* Execute code */
            $countFrequency = $arr_count["$brancharray1"]; //could equal 2,3,4,etc.
            
            //get keys of current array value
            $firstInstance = $key;
            
            //number of products in branch
            $keycount = count($ClaimArray['CC_'.$firstInstance]);
            $keycount++;
            
            //get subsequent ones
            $subseKeys = array_keys($brancharray, $brancharray1);
            //remove first value / first branch instance
            array_shift($subseKeys);

            //get subsequent branches in record
            foreach($subseKeys as $subseKeys1){
                $duplicate[] = $subseKeys1;
            }

            //transfer subsequent branches' details to first branch
            for($i=0;$i<count($subseKeys);$i++){
                $keycount2 = count($ClaimArray['CC_'.$subseKeys[$i]]);

                for($j=1;$j<=$keycount2;$j++){
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['RETURNQUANTITY'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['RETURNQUANTITY'];
                    
                    //change quantity to 1 if default = 0
                    if($ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['RETURNQUANTITY'] == 0){ 
                        $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['RETURNQUANTITY'] = 1;
                    }
        
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['ITEMNUMBER'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['ITEMNUMBER'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['ITEMDESCRIPTION'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['ITEMDESCRIPTION'];        
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['ITEMBARCODE'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['ITEMBARCODE'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['SUPPLIERREFNO'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['SUPPLIERREFNO'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['PACKSIZE'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['PACKSIZE'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['CONTROLNUMBER'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['CONTROLNUMBER'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['COSTPER'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['COSTPER'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['UNITCOST'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['UNITCOST'];
                    $ClaimArray['ITEMS_'.$key]['PRODUCT_'.$keycount]['RETURNAMOUNT'] = $ClaimArray['ITEMS_'.$subseKeys[$i]]['PRODUCT_'.$j]['RETURNAMOUNT'];
                    
                    $ClaimArray['TOTALFIGURE_'.$key] += $ClaimArray['TOTALFIGURE_'.$subseKeys[$i]];
        
                    $ClaimArray['CC_'.$key][] = $keycount;

                    $keycount++;
                }

            }//for

            //finally store branch list that have been dealt with to avoid repeating treatments
            $alreadydealtbranch[] = $brancharray1;
        
        }//if

    }

    /** PHPExcel */
    include_once 'Classes/PHPExcel.php';

    /** PHPExcel_Writer_Excel2007 */ 
    include_once 'Classes/PHPExcel/Writer/Excel2007.php'; 

    // Create new PHPExcel object 
    $objPHPExcel = new PHPExcel(); 

    // Setting Excel file properties 
    // Change the properties detail as per your requirement 
    $objPHPExcel->getProperties()->setCreator("Kolapo Babawale"); 
    $objPHPExcel->getProperties()->setLastModifiedBy("Kolapo Babawale"); 
    $objPHPExcel->getProperties()->setTitle("XML DOCUMENT"); 
    $objPHPExcel->getProperties()->setSubject("SHOPRITE XML DOCUMENT"); 
    $objPHPExcel->getProperties()->setDescription("Excel file generated automatically"); 

    // Select current sheet 
    $objPHPExcel->setActiveSheetIndex(0); 
    // Add some data 
    //column number, which we will be incrementing 
    $rownum=1; 

    for ($i = 0; $i < count($ClaimArrayCount); $i++) 
    { 
        //dont show duplicate claims
        if(in_array($i, $duplicate)){
             continue;
        }

        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", $ClaimArray['MAINTITLE1_'.$i]);
        $rownum++; 
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", $ClaimArray['MAINTITLE2_'.$i]);
        $rownum++; 
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", $ClaimArray['THIRDLINENOTICE_'.$i]);
        $rownum++; 
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['DATECREATED_'.$i]);
        $rownum++;
        //division
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Division');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['DIVISION_'.$i]);
        $rownum++;
        //branch
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Branch');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['BRANCH_'.$i]);
        $rownum++;
        //credit note number
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Credit Note Number');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['CREDITNOTENUMBER_'.$i]);
        $rownum++;
        //supplier Invoice Number
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Supplier Invoice Number');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['SUPPLIERINVOICENUMBER_'.$i]);
        $rownum++;
        //document status
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Document Status');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['DOCUMENTSTATUS_'.$i]);
        $rownum++;
        //document status date
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Document Status Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['DOCUMENTSTATUSDATE_'.$i]);
        $rownum++;
        //delivery date
        $objPHPExcel->getActiveSheet()->SetCellValue('D'."$rownum", 'Delivery Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'."$rownum", $ClaimArray['DELIVERYDATE_'.$i]);
        $rownum++;
        //accounting supplier
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Accounting Supplier');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['ACCOUNTINGSUPPLIER_'.$i]);
        //$rownum++;
        //merchandising supplier
        $objPHPExcel->getActiveSheet()->SetCellValue('D'."$rownum", 'Merchandising Supplier');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'."$rownum", $ClaimArray['MERCHANDISINGSUPPLIER_'.$i]);
        $rownum++;
        
        //add table headings for product list
        $rownum++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'Item Barcode');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", 'Item No');
        $objPHPExcel->getActiveSheet()->SetCellValue('C'."$rownum", 'Description');
        $objPHPExcel->getActiveSheet()->SetCellValue('D'."$rownum", 'Supp. Ref. No.');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'."$rownum", 'Pack Size');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'."$rownum", 'Control No.');
        $objPHPExcel->getActiveSheet()->SetCellValue('G'."$rownum", 'Ret Qty');
        $objPHPExcel->getActiveSheet()->SetCellValue('H'."$rownum", 'Cost Per');
        $objPHPExcel->getActiveSheet()->SetCellValue('I'."$rownum", 'Unit Cost');
        $objPHPExcel->getActiveSheet()->SetCellValue('J'."$rownum", 'Return Amount');
        $rownum++;

        //for($k=1;$k<=$ClaimArray['ProductCount_'.$i];$k++) //loop through products
        foreach($ClaimArray['CC_'.$i] as $k) //loop through products
        { 
            $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['ITEMBARCODE']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['ITEMNUMBER']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['ITEMDESCRIPTION']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['SUPPLIERREFNO']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['PACKSIZE']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['CONTROLNUMBER']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['RETURNQUANTITY']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['COSTPER']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['UNITCOST']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'."$rownum", $ClaimArray['ITEMS_'.$i]['PRODUCT_'.$k]['RETURNAMOUNT']);

            $rownum++;
        }
        $rownum++;
        //add total value
        $objPHPExcel->getActiveSheet()->SetCellValue('J'."$rownum", $ClaimArray['TOTALFIGURE_'.$i]);  
        $rownum++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'REASON FOR CLAIM');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['REASONFORCLAIM_'.$i]);
        $rownum++; 
        $objPHPExcel->getActiveSheet()->SetCellValue('A'."$rownum", 'TRUCK DETAILS');
        $objPHPExcel->getActiveSheet()->SetCellValue('B'."$rownum", $ClaimArray['REASONFORCLAIM_'.$i]);
        $rownum+=10; 
    
    
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