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

class Cashflow4YouRelationHandler extends VTEventHandler {
	public function handleEvent($handlerType, $entityData){
		global $log, $adb;
		$entityId = $entityData->getId();
		$moduleName = $entityData->getModuleName();
                $cashflow_utils = new Cashflow4YouRelation();
                $cashflow_utils->updateSavedRelation($moduleName,$entityId);
	}
}
