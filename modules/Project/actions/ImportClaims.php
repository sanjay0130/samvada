<?php

require_once('modules/Project/Project.php');

class Project_ImportClaims_Action extends Vtiger_Action_Controller {
    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
            throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    function process(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $response = new Vtiger_Response();

        $target_dir = "cache/import/";
        $target_file = $target_dir . basename($_FILES["file1"]["name"][0]);
        $uploadOk = 1;

        // Check if image file is a actual image or fake image
        if (isset($_POST["ImportClaims"])) {

            if ($target_file == "cache/import/") {
                $msg = "cannot be empty";
                $uploadOk = 0;
            } // Check if file already exists
            //else if (file_exists($target_file)) {
            //    $msg = "Sorry, file already exists.";
            //    $uploadOk = 0;
            /*}*/ // Check file size
            else if ($_FILES["file1"]["size"][0] > 5000000) {
                $msg = "Sorry, your file is too large.";
                $uploadOk = 0;
            } // Check if $uploadOk is set to 0 by an error
            else if ($uploadOk == 0) {
                $msg = "Sorry, your file was not uploaded.";

                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["file1"]["tmp_name"][0], $target_file)) {
                    //$msg = "The file " . basename($_FILES["file"]["name"][0]) . " has been uploaded.";

                    //$map['project_no'] = 0;//ClaimNo
                    $map['projectname'] = 1;//ClaimName
                    $map['cf_1098'] = 6;//ClaimStatus
                    $map['assigned_user_id'] = 8;//Analyst
                    $map['cf_928'] = 11;//LabourSpecified
                    $map['cf_930'] = 12;//LabourNonSpecified                    
                    $map['cf_932'] = 13;//Overhead
                    $map['cf_934'] = 14;//Proxy
                    $map['cf_936'] = 15;//Materials
                    $map['cf_938'] = 16;//Subcontracts
                    $map['cf_1493'] = 17;//IRAPAndOtherAssistance
                    $map['cf_1559'] = 18;//PrevYrTaxableIncome                    
                    $map['cf_918'] = 19;//ITCBalance
                    $map['cf_910'] = 20;//ITCTotal
                    $map['cf_912'] = 21;//ITCRefundable
                    $map['cf_914'] = 22;//ITCNonRefundable
                    $map['cf_916'] = 23;//OITC
                    $map['cf_920'] = 24;//ORDTCElected
                    $map['cf_926'] = 25;//Other
                    $map['cf_942'] = 26;//TotalEligibleExpenditures                    

                    $row = 1;
                    if (($handle = fopen($target_file, "r")) !== FALSE) {
                        $r = 0;
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            if($r == 0) {$r++; continue;}
                            //$num = count($data);
                            $res = $db->pquery('SELECT projectid FROM vtiger_project WHERE project_no = ?', array($data[0]));
                            if($db->num_rows($res)) {
                                $projectid = $db->query_result($res, 0, 'projectid');
                                $recordModel = Vtiger_Record_Model::getInstanceById($projectid, $moduleName);
                                $res1 = $db->pquery('SELECT id FROM vtiger_users WHERE user_name = ? and deleted = 0', array($data[$map['assigned_user_id']]));
                                if($db->num_rows($res1)) {
                                    $data[$map['assigned_user_id']] = $db->query_result($res1, 0, 'id');
                                }else{
                                    $data[$map['assigned_user_id']] = $recordModel->get('assigned_user_id');
                                }
                                /*$fieldModelList = $moduleModel->getFields();
                                foreach ($fieldModelList as $fieldName => $fieldModel) {
                                    if(isset($map[$fieldName]) && $map[$fieldName] > 0)
                                    {
                                        $fieldValue = $data[$map[$fieldName]];
                                        $fieldDataType = $fieldModel->getFieldDataType();
                                        if($fieldDataType == 'time'){
                                            $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                                        }
                                        if($fieldValue !== null) {
                                            if(!is_array($fieldValue)) {
                                                $fieldValue = trim($fieldValue);
                                            }
                                            $recordModel->set($fieldName, $fieldValue);
                                        }
                                    }
                                }*/

                                
                                $focus = new $moduleName();
                                $focus->id = $projectid;
                                $focus->retrieve_entity_info($projectid, $moduleName);
                                $focus->mode = 'edit';
                                $focus->column_fields = array_map(decode_html, $focus->column_fields);
                                foreach($map as $fieldName => $index)
                                {
                                    $focus->column_fields[$fieldName] = $data[$index];
                                }
                                $focus->save($moduleName);
                            }
                            //$r++;
                        }
                        fclose($handle);
                    }
                }
            }
        }

        $result = $fieldName;



        $response->setResult($result);
        return $response;
    }
}