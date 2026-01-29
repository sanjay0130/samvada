<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
require_once('include/utils/utils.php');
require_once('modules/Cashflow4You/actions/Cashflow4YouRelation.php');

class Cashflow4YouDeleteRelHandler extends VTEventHandler {
    public function handleEvent($handlerType, $entityData){
        global $log;
        $entityId = $entityData->getId();

        $db = PearDatabase::getInstance();

        $selectrel = "SELECT relcrmid, relmodule  FROM  vtiger_crmentityrel WHERE crmid=?";
        $select_res = $db->pquery($selectrel, Array( $entityId ) );
        $row = $db->num_rows($select_res);
        $i = 0;
        if( $row > 0 )
        {
            while( $row = $db->fetch_row($select_res))
            {
                $Relation[$i]["id"] = $row["relcrmid"];
                $Relation[$i++]["module"] = $row["relmodule"];
            }

            $cashflow_utils = new Cashflow4YouRelation();
            foreach( $Relation AS $relation )
            {
                $cashflow_utils->updateSavedRelation($relation["module"],$relation["id"]);
            }
        }
    }
}
