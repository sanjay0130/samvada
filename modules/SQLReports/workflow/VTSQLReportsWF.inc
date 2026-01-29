<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'include/Webservices/Retrieve.php';
include_once 'include/Webservices/Revise.php';

function evaluateTPComments($entity)
{
    global $adb, $current_user, $log;
    include 'modules/SQLReports/workflow/VTSQLReportsWFConfig.inc';
    
    $log->debug("AGC - evaluateTPComments START");
    
    $sql = "select vtiger_crmentity.crmid, commentcontent, changedon, setype, DATEDIFF(CURDATE(), changedon) AS dayssince, UNIX_TIMESTAMP(changedon) as compstamp from vtiger_modcomments
        inner join vtiger_crmentity on (vtiger_crmentity.crmid = vtiger_modcomments.related_to and vtiger_crmentity.setype = 'Potentials' and deleted = 0)
        inner join vtiger_modtracker_basic on vtiger_modtracker_basic.crmid = vtiger_modcomments.modcommentsid
        inner join (
                select related_to, MAX(modcommentsid) maxid from vtiger_modcomments
                inner join vtiger_crmentity on (vtiger_crmentity.crmid = vtiger_modcomments.related_to and vtiger_crmentity.setype = 'Potentials' and deleted = 0)
                group by related_to
        ) b on vtiger_modcomments.related_to = b.related_to and vtiger_modcomments.modcommentsid = b.maxid
        where commentcontent LIKE 'TP:%'";
    
    /*$sqlAccounts = "select vtiger_crmentity.crmid, commentcontent, changedon, setype, DATEDIFF(CURDATE(), changedon) AS days_since, UNIX_TIMESTAMP(changedon) as comp_stamp from vtiger_modcomments
        inner join vtiger_crmentity on (vtiger_crmentity.crmid = vtiger_modcomments.related_to and deleted = 0)
        inner join vtiger_modtracker_basic on vtiger_modtracker_basic.crmid = vtiger_modcomments.modcommentsid
        inner join (
                select related_to, MAX(modcommentsid) maxid from vtiger_modcomments
                inner join vtiger_crmentity on (vtiger_crmentity.crmid = vtiger_modcomments.related_to and deleted = 0)
                where related_to = ?
                group by related_to
        ) b on vtiger_modcomments.related_to = b.related_to and vtiger_modcomments.modcommentsid = b.maxid
        where commentcontent LIKE 'TP:%'
        and vtiger_modcomments.related_to = ?";*/
    
    $result = $adb->pquery($sql, array());
    if($adb->num_rows($result))
    {
        while($row = $adb->fetchByAssoc($result))
        {                
            //retrieve potential record through server api
            try {
                    $wsid = vtws_getWebserviceEntityId('Potentials', $row['crmid']); // Module_Webservice_ID x CRM_ID
                    $potObj = vtws_retrieve($wsid, $current_user);
                    $potObj['id'] = $wsid;

            } catch (WebServiceException $ex) {
                    $log->debug("AGC - Could not retrieve potential record ".$row['crmid'].": ".$ex->getMessage());
            }

            //update potential record through server api
            try {
                    $potObj[$lastCommentFieldName] = $row['commentcontent'];
                    $potObj[$notChangedSinceFieldName] = $row['dayssince'];
                    $potObj = vtws_revise($potObj, $current_user);

            } catch (WebServiceException $ex) {
                    $log->debug("AGC - Could not update potential record ".$row['crmid'].": ".$ex->getMessage());
            }
        }
    }
    
    $log->debug("AGC - evaluateTPComments END");
}

