<?php

ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING

//chdir(__DIR__);
require_once 'config.inc.php';
require_once 'include/utils/utils.php';
require_once 'include/database/PearDatabase.php';


$db = PearDatabase::getInstance();

// super-simple query: no joins, no limits
$sql = "SELECT contactid, firstname, lastname FROM vtiger_contactdetails WHERE contactid > 0 LIMIT 5";
$result = $db->pquery($sql, []);

echo "Row count: " . $db->num_rows($result) . "\n";

$fieldCount = $result->FieldCount();
echo "FieldCount: $fieldCount\n";

for ($i = 0; $i < $fieldCount; $i++) {
    $fld = $result->FetchField($i);
    echo "Col $i: " . $fld->name . "\n";
}

echo "\nData:\n";
for ($i = 0; $i < $db->num_rows($result); $i++) {
    $c1 = $db->query_result($result, $i, 'contactid');
    $c2 = $db->query_result($result, $i, 'firstname');
    $c3 = $db->query_result($result, $i, 'lastname');
    echo "Row $i: contactid=$c1, firstname=$c2, lastname=$c3\n";
}
