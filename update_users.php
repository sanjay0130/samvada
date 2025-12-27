<?php

// Run from Vtiger root
chdir(__DIR__);

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

require_once 'config.inc.php';
require_once 'include/utils/utils.php';
require_once 'include/database/PearDatabase.php';

// vtiger classes
require_once 'modules/Users/Users.php';
require_once('modules/Users/CreateUserPrivilegeFile.php');
//require_once 'modules/Users/models/User.php';
//require_once 'modules/Roles/models/Record.php';
//require_once 'modules/Settings/SharingAccess/models/Module.php';

echo "<pre>";

// Connect DB
$db = PearDatabase::getInstance();

// Get all active users
$users = array();
$result = $db->pquery("SELECT id FROM vtiger_users WHERE status != 'Inactive' AND id = 5", array());
while ($row = $db->fetch_array($result)) {
    $users[] = $row['id'];
}

echo "Processing users: ";
print_r($users);

// Loop each user and regenerate privilege files
foreach ($users as $userid) {
    echo "Regenerating privilege files for USER $userid ...\n";

    // this triggers rebuilding of privilege files
    createUserPrivilegesfile($userid);
    createUserSharingPrivilegesfile($userid);
}

echo "\nDONE and finished.\n";
echo "</pre>";
