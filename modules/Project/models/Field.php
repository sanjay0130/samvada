<?php

/**
 * Project Field Model Class
 */
class Project_Field_Model extends Vtiger_Field_Model {

	/**
	 * Function to get the field details
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo() {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$fieldDataType = $this->getFieldDataType();

		$this->fieldInfo['mandatory'] = $this->isMandatory();
		$this->fieldInfo['presence'] = $this->isActiveField();
		$this->fieldInfo['quickcreate'] = $this->isQuickCreateEnabled();
		$this->fieldInfo['masseditable'] = $this->isMassEditable();
		$this->fieldInfo['defaultvalue'] = $this->hasDefaultValue();
		$this->fieldInfo['column'] = $this->get('column');
		$this->fieldInfo['type'] = $fieldDataType;
		$this->fieldInfo['name'] = $this->get('name');
		$this->fieldInfo['label'] = vtranslate($this->get('label'), $this->getModuleName());

		if($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist' || $fieldDataType == 'multiowner') {
			$pickListValues = $this->getPicklistValues();
            $editablePicklistValues = $this->getEditablePicklistValues();
			if(!empty($pickListValues)) {
				$this->fieldInfo['picklistvalues'] = $pickListValues;
			} else {
				$this->fieldInfo['picklistvalues'] = array();
			}
            
            if(!empty($editablePicklistValues)) {
                $this->fieldInfo['editablepicklistvalues'] = $editablePicklistValues;
            } else {
				$this->fieldInfo['editablepicklistvalues'] = array();
			}

			$this->fieldInfo['picklistColors'] = array();
			$picklistColors = $this->getPicklistColors();
			if ($picklistColors) {
				$this->fieldInfo['picklistColors'] = $picklistColors;
			}
		}
        
        if($fieldDataType == "documentsFolder"){
            $documentFolders = $this->getDocumentFolders();
            if(!empty($documentFolders)) {
                $this->fieldInfo['documentFolders'] = $documentFolders;
            }
        }

		if($fieldDataType === 'currencyList'){
		   $currencyList = $this->getCurrencyList();
		   $this->fieldInfo['currencyList'] = $currencyList;
		}

		if($this->getFieldDataType() == 'date' || $this->getFieldDataType() == 'datetime'){
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$this->fieldInfo['date-format'] = $currentUser->get('date_format');
		}

		if($this->getFieldDataType() == 'time') {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$this->fieldInfo['time-format'] = $currentUser->get('hour_format');
		}

		if($this->getFieldDataType() == 'currency') {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$this->fieldInfo['currency_symbol'] = $currentUser->get('currency_symbol');
			$this->fieldInfo['decimal_separator'] = $currentUser->get('currency_decimal_separator');
			$this->fieldInfo['group_separator'] = $currentUser->get('currency_grouping_separator');
		}

		if($this->getFieldDataType() == 'owner') {
			// if fieldname is cf_2325 (Renewals) then return getPicklistValuesForRenewals
			if($this->getFieldName() == 'cf_2325') {
				$userList = $this->getPicklistValuesForRenewals();
				$groupList = [];
			// if fieldname is cf_2328 (Payroll Admin) then return getPicklistValuesForPayrollAdmin
			} elseif($this->getFieldName() == 'cf_2327') { // Payroll Admin
				$userList = $this->getPicklistValuesForPayrollAdmin();
				$groupList = [];
			} elseif($this->getFieldName() == 'cf_2329') { // Tech
				$userList = $this->getPicklistValuesForTech();
				$groupList = [];
			}else {
				$userList = $currentUser->getAccessibleUsers();
				$groupList = $currentUser->getAccessibleGroups();
			}
			$pickListValues = array();
			$pickListValues[vtranslate('LBL_USERS', $this->getModuleName())] = $userList;
			$pickListValues[vtranslate('LBL_GROUPS', $this->getModuleName())] = $groupList;
			$this->fieldInfo['picklistvalues'] = $pickListValues;
		}

		if($this->getFieldDataType() == 'ownergroup') {
			$groupList = $currentUser->getAccessibleGroups();
			$pickListValues = array();
			$this->fieldInfo['picklistvalues'] = $groupList;
		}

		if($this->getFieldDataType() == 'reference') {
			$this->fieldInfo['referencemodules'] = $this->getReferenceList();
		}

		if($fieldDataType == 'groupFilteredUserField') {
			$pickListValues = $this->getPicklistValues();
            $editablePicklistValues = $this->getPicklistValues();
			if(!empty($pickListValues)) {
				$this->fieldInfo['picklistvalues'] = $pickListValues;
			} else {
				$this->fieldInfo['picklistvalues'] = array();
			}
            
            if(!empty($editablePicklistValues)) {
                $this->fieldInfo['editablepicklistvalues'] = $editablePicklistValues;
            } else {
				$this->fieldInfo['editablepicklistvalues'] = array();
			}

			$this->fieldInfo['picklistColors'] = array();
			// $picklistColors = $this->getPicklistColors();
			// if ($picklistColors) {
			// 	$this->fieldInfo['picklistColors'] = $picklistColors;
			// }
		}

		$this->fieldInfo['validator'] = $this->getValidator();
		return $this->fieldInfo;
	}

	/**
	 * Function to get the picklist values for renewals
	 * @return <Array> List of picklist values for renewals
	 */
	public function getPicklistValuesForRenewals() {
        global $adb;
        $users = [];

        $query = "
            SELECT vtiger_users.id, vtiger_users.first_name, vtiger_users.last_name
            FROM vtiger_users
            INNER JOIN vtiger_users2group ON vtiger_users.id = vtiger_users2group.userid
            INNER JOIN vtiger_groups ON vtiger_users2group.groupid = vtiger_groups.groupid
            WHERE vtiger_groups.groupname = 'Renewals Team'";
        //$query .= " AND vtiger_users.status = 'Active'";

        $result = $adb->pquery($query, []);
        while ($row = $adb->fetch_array($result)) {
            $users[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }

        return $users;
	}

	/**
	 * Function to get the picklist values for renewals
	 * @return <Array> List of picklist values for renewals
	 */
	public function getPicklistValuesForPayrollAdmin() {
        global $adb;
        $users = [];

        $query = "
            SELECT vtiger_users.id, vtiger_users.first_name, vtiger_users.last_name
            FROM vtiger_users
            INNER JOIN vtiger_users2group ON vtiger_users.id = vtiger_users2group.userid
            INNER JOIN vtiger_groups ON vtiger_users2group.groupid = vtiger_groups.groupid
            WHERE vtiger_groups.groupname = 'Payroll Admin Team'";
        //$query .= " AND vtiger_users.status = 'Active'";

        $result = $adb->pquery($query, []);
        while ($row = $adb->fetch_array($result)) {
            $users[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }

        return $users;
	}

	/**
	 * Function to get the picklist values for renewals
	 * @return <Array> List of picklist values for renewals
	 */
	public function getPicklistValuesForTech() {
        global $adb;
        $users = [];

        $query = "
            SELECT vtiger_users.id, vtiger_users.first_name, vtiger_users.last_name
            FROM vtiger_users
            INNER JOIN vtiger_users2group ON vtiger_users.id = vtiger_users2group.userid
            INNER JOIN vtiger_groups ON vtiger_users2group.groupid = vtiger_groups.groupid
            WHERE vtiger_groups.groupname = 'Tech Team'";
        //$query .= " AND vtiger_users.status = 'Active'";

        $result = $adb->pquery($query, []);
        while ($row = $adb->fetch_array($result)) {
            $users[$row['id']] = $row['first_name'] . ' ' . $row['last_name'];
        }

        return $users;
	}
}