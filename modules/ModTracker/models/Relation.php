<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModTracker_Relation_Model extends Vtiger_Record_Model
{

	function setParent($parent)
	{
		$this->parent = $parent;
	}

	function getParent()
	{
		return $this->parent;
	}

	function getLinkedRecord()
	{
		$db = PearDatabase::getInstance();

		$targetId = $this->get('targetid');
		$targetModule = $this->get('targetmodule');

		if (!Users_Privileges_Model::isPermitted($targetModule, 'DetailView', $targetId)) {
			return false;
		}
		$query = 'SELECT * FROM vtiger_crmentity WHERE crmid = ?';
		$params = array($targetId);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);
		$moduleModels = array();
		if ($noOfRows) {
			if (!array_key_exists($targetModule, $moduleModels)) {
				$moduleModel = Vtiger_Module_Model::getInstance($targetModule);
			}
			$row = $db->query_result_rowdata($result, 0);
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $targetModule);
			$recordInstance = new $modelClassName();
			$recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			$recordInstance->set('id', $row['crmid']);
			if ($targetModule == 'Emails') {
				$recordInstance->set('parent_id', $this->parent->get('crmid'));
			}
			return $recordInstance;
		}
		return false;
	}

	public function getRecordDetailViewUrl()
	{
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($this->get('targetid'), $this->get('targetmodule'));
			if ($this->get('targetmodule') == 'Emails') {
				return $recordModel->getDetailViewUrl($this->parent->get('crmid'));
			}
			return $recordModel->getDetailViewUrl();
		} catch (Exception $e) {
			return false;
		}
	}


	public function getCalendarActivityUpdateSummary($activityId)
	{
		$db = PearDatabase::getInstance();

		$query = "
        SELECT 
            ce.crmid,
            ce.smcreatorid,
            CONCAT(u1.first_name, ' ', u1.last_name) AS created_by,
            ce.label AS subject,
            ce.createdtime,
            act.activitytype,
            act.date_start,
            act.time_start,
            act.due_date,
            act.time_end
        FROM vtiger_crmentity AS ce
        INNER JOIN vtiger_activity AS act ON act.activityid = ce.crmid
        LEFT JOIN vtiger_users AS u1 ON u1.id = ce.smcreatorid
        WHERE ce.crmid = ? AND ce.deleted = 0 AND ce.setype = 'Calendar'
    ";

		$result = $db->pquery($query, [$activityId]);
		if ($db->num_rows($result) > 0) {
			$row = $db->fetch_array($result);

			$createdBy   = trim($row['created_by']);
			$subject     = $row['subject'];
			$activityType = $row['activitytype'];
			$startDate   = $row['date_start'];
			$startTime   = $row['time_start'];

			// Combine date + time and format nicely
			$startDateTime = date('M d, Y h:i A', strtotime("$startDate $startTime"));

			// Build the readable summary
			/* $summary = sprintf(
				'%s created "%s" on %s (%s)',
				$createdBy ?: 'Unknown User',
				$subject ?: 'No Subject',
				$startDateTime,
				ucfirst($activityType)
			); */

			$summary = sprintf(
				'<span style="color:#007bff;font-weight:600;">%s</span> 
    created 
    <span style="color:#28a745;font-weight:600;">"%s"</span> 
    on 
    <span style="color:#6c757d;">%s</span> 
    <span style="background:#f8f9fa;border:1px solid #ddd;border-radius:4px;padding:2px 6px;font-size:12px;margin-left:4px;color:#333;">%s</span>',
				htmlspecialchars($createdBy ?: 'Unknown User', ENT_QUOTES, 'UTF-8'),
				htmlspecialchars($subject ?: 'No Subject', ENT_QUOTES, 'UTF-8'),
				htmlspecialchars($startDateTime, ENT_QUOTES, 'UTF-8'),
				htmlspecialchars(ucfirst($activityType), ENT_QUOTES, 'UTF-8')
			);


			return array(
				'activityType' => htmlspecialchars(ucfirst($activityType), ENT_QUOTES, 'UTF-8'),
				'startDateTime' => htmlspecialchars($startDateTime, ENT_QUOTES, 'UTF-8'),
				'subject' => htmlspecialchars($subject ?: 'No Subject', ENT_QUOTES, 'UTF-8')
			);
		}

		return false;
	}
}
