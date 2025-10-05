<?php
/* * *******************************************************************************
 * The content of this file is subject to the Cashflow4You license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Cashflow4You_SaveAjax_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
            $recordModel = $this->saveRecord($request);

            $fieldModelList = $recordModel->getModule()->getFields();
            $result = array();
            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $recordFieldValue = $recordModel->get($fieldName);
                if(is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
                    $recordFieldValue = implode(' |##| ', $recordFieldValue);
                }
                $fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
                if ($fieldModel->getFieldDataType() !== 'currency' && $fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'date') { 
                    $displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId()); 
                }

                $result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
            }

            $result['_recordLabel'] = $recordModel->getName();
            $result['_recordId'] = $recordModel->getId();
            
            if($error == "") {
                $result['success'] = true;
                $result['message'] = vtranslate("LBL_CASHFLOW_PAYMENT","Cashflow4You" )." '".$recordModel->getName()."' ".vtranslate("LBL_CASHFLOW_IS_SAVED","Cashflow4You" );
            } else {
                $result['success'] = false;
                $result['message'] = vtranslate("LBL_CASHFLOW_SAVE_ERROR","Cashflow4You").":".$error;
            }
            
            $response = new Vtiger_Response();
            $response->setEmitType(Vtiger_Response::$EMIT_JSON);
            $response->setResult($result);
            $response->emit();
	}

        public function saveRecord($request) {
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if($request->get('relationOperation')) {
                    $parentModuleName = $request->get('sourceModule');
                    $parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
                    $parentRecordId = $request->get('sourceRecord');
                    $relatedModule = $recordModel->getModule();
                    $relatedRecordId = $recordModel->getId();

                    $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
                    $relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
                /*$db = PearDatabase::getInstance();
            
                $recordId = $request->get('record');
                $selectrel = "SELECT relcrmid, relmodule FROM vtiger_crmentityrel WHERE crmid=?";
                $select_res = $db->pquery($selectrel, Array( $recordId ) );
                $row = $db->num_rows($select_res);
                $i = 0;
                
echo "selectrel=".$selectrel."<br />";
exit;
                while( $row = $db->fetch_row($select_res))
                {
                    $Relation[$i]["id"] = $row["relcrmid"];
                    $Relation[$i++]["module"] = $row["relmodule"];
                }

                $cashflow_utils = new Cashflow4YouRelation();
                foreach( $Relation AS $relation )
                {
                    $cashflow_utils->updateSavedRelation($relation["module"],$relation["id"]);
                }*/
                
		return $recordModel;
	}
	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {
            $moduleName = $request->getModule();
            $recordId = $request->get('record');

            if(!empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
                $recordModel->set('id', $recordId);
                $recordModel->set('mode', 'edit');

                $fieldModelList = $recordModel->getModule()->getFields();
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    $fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));

                    if ($fieldName === $request->get('field')) {
                        $fieldValue = $request->get('value');
                    }
                    $fieldDataType = $fieldModel->getFieldDataType();
                    if ($fieldDataType == 'time') {
                        $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                    }
                    if ($fieldValue !== null) {
                        if (!is_array($fieldValue)) {
                            $fieldValue = trim($fieldValue);
                        }
                        $recordModel->set($fieldName, $fieldValue);
                    }
                    $recordModel->set($fieldName, $fieldValue);
                }
            } else {
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                $recordModel->set('mode', '');

                $fieldModelList = $moduleModel->getFields();
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($request->has($fieldName)) {
                        $fieldValue = $request->get($fieldName, null);
                    } else {
                        $fieldValue = $fieldModel->getDefaultFieldValue();
                    }
                    $fieldDataType = $fieldModel->getFieldDataType();
                    if ($fieldDataType == 'time') {
                        $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                    }
                    if ($fieldValue !== null) {
                        if (!is_array($fieldValue)) {
                            $fieldValue = trim($fieldValue);
                        }
                        $recordModel->set($fieldName, $fieldValue);
                    }
                } 
            }

            return $recordModel;
	}
}
