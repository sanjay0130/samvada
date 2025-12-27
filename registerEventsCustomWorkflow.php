<?php
require_once 'include/utils/utils.php';
require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
$emm = new VTEntityMethodManager($adb); 

$emm->addEntityMethod("Events", "Update Organization based on selected Contact", "modules/Events/workflow/UpdateOrgnization.php", "updateOrg");