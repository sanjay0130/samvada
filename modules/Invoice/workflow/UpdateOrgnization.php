<?php

function updateOrg($entity) {
    global $adb, $log;

    $log->debug("Entering Custom Workflow Function: update Orgnization from (" . $entity->get('moduleName') . ")...");
    $log->info("Trigerring invoice is: " . $entity->get('id') . ".");

    include_once 'include/utils/CommonUtils.php';
    include_once 'include/database/PearDatabase.php';


    $id = explode("x", $entity->get('id'));
    $inv_id = $id[1];


    $query = "update vtiger_invoice AS A set A.accountid = (select accountid from vtiger_contactdetails where contactid = A.contactid) where A.invoiceid = ?";
    $adb->pquery($query, array($inv_id));
}
