<?php

function updateOrg($entity) {
    global $adb, $log;

    $log->debug("Entering Custom Workflow Function: update Orgnization from (" . $entity->get('moduleName') . ")...");
    $log->info("Trigerring Event is: " . $entity->get('id') . ".");

    include_once 'include/utils/CommonUtils.php';
    include_once 'include/database/PearDatabase.php';


    $id = explode("x", $entity->get('id'));
    $event_id = $id[1];


    $query = "INSERT INTO vtiger_seactivityrel (crmid,activityid) (select 
                C.accountid, A.activityid
            from
                vtiger_cntactivityrel AS A
                    LEFT JOIN
                vtiger_seactivityrel AS B ON (A.activityid = B.activityid)
                    LEFT JOIN
                vtiger_contactdetails AS C ON (C.contactid = A.contactid)
            WHERE
                B.activityid is NULL
                    AND A.activityid = ? )";
    $adb->pquery($query, array($event_id));
}