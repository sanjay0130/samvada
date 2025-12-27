<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Project_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
        $recordModel = $this->record;
		 if(!$recordModel){
            if (!empty($recordId)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            }
        }
		
		$createmode=$request->get("createmode");
		if(isset($createmode) && $createmode!="")
		{
		$viewer = $this->getViewer($request);	
		$createmode=base64_decode(urldecode($createmode));
		$access_str=Vtiger_Session::get('QRY_PARAM'); 
		 if($access_str!=$createmode){
		   throw new AppException("Invalid Token passsed in URL for multiple creation"); exit;
		 }
		 //Vtiger_Session::set('QRY_PARAM','');
		 $potential_id=$request->get("potential_id");
		$recordModel_po = Vtiger_Record_Model::getInstanceById($potential_id,"Potentials");
		$data = $recordModel_po->getData();
		$fye_mo=$data["cf_876"];
		$fye_mo=sprintf("%02d",$fye_mo);
		$fye_dt=$data["cf_874"];
		$fye_dt=sprintf("%02d",$fye_dt);
		$fye_year=$data["cf_835"];
		$fye_year_explode=explode(' |##| ',$fye_year);
		
		$fy_dt=$fye_year.'-'.$fye_mo.'-'.$fye_dt;
		$fy_dt = DateTime::createFromFormat('Y-m-d', $fy_dt);
		 $recordModel->set("cf_783",$fy_dt);
		 $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel,Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);                                                                           
		 $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		 $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		}
		
		/***********Vivek*************/
		
		
		$viewer = $this->getViewer($request);

		parent::process($request);
	}

}
