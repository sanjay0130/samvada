<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class BoruPayments_Module_Model extends Vtiger_Module_Model {

	public function getModuleIcon() {
		$moduleName = $this->getName();
		$lowerModuleName = strtolower($moduleName);
		$title = vtranslate($moduleName, $moduleName);

		$moduleIcon = "<i class='vicon-$lowerModuleName' title='$title'></i>";
		if ($this->source == 'custom') {
			$moduleShortName = mb_substr(trim($title), 0, 2);
			$moduleIcon = "<span class='custom-module' title='$title'>P</span>";
		}

		$imageFilePath = 'layouts/'.Vtiger_Viewer::getLayoutName()."/modules/$moduleName/$moduleName.png";
		if (file_exists($imageFilePath)) {
			$moduleIcon = "<img src='$imageFilePath' title='$title'/>";
		}

		return $moduleIcon;
	}
	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();

		$settingsLinks[] = array(
		    'linktype' => 'MODULESETTING',
		    'linklabel' => 'Uninstall',
		    'linkurl' => 'index.php?module=BoruPayments&view=Uninstall',
		    'linkicon' => ''
		);

        	return $settingsLinks;
	}
	public static function getModuleIconPath($moduleName) {
		$moduleModel = BoruPayments_Module_Model::getInstance($moduleName);
		return $moduleModel->getModuleIcon();
	}
}

