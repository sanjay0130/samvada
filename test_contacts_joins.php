<?php
//chdir(__DIR__);
require_once 'config.inc.php';
require_once 'include/utils/utils.php';
require_once 'include/database/PearDatabase.php';

$db = PearDatabase::getInstance();

function runTest($label, $sql) {
    global $db;
    echo "========================\n";
    echo "$label\n";
    echo "SQL: $sql\n\n";

    $result = $db->pquery($sql, []);
    if (!$result) {
        echo "ERROR: query failed\n\n";
        return;
    }

    $rowCount = $db->num_rows($result);
    echo "Row count: $rowCount\n";

    $fieldCount = $result->FieldCount();
    echo "FieldCount: $fieldCount\n";

    for ($i = 0; $i < $fieldCount; $i++) {
        $fld = $result->FetchField($i);
        echo "Col $i: " . $fld->name . "\n";
    }

    echo "\nSample data:\n";
    for ($i = 0; $i < min($rowCount, 3); $i++) {
        $row = [];
        for ($j = 0; $j < $fieldCount; $j++) {
            $fld = $result->FetchField($j);
            $colName = $fld->name;
            $row[$colName] = $db->query_result($result, $i, $j);
        }
        print_r($row);
    }

    echo "\n\n";
}

// Q1: Only contactdetails (we already know this works, but for completeness)
$q1 = "SELECT contactid, firstname, lastname
       FROM vtiger_contactdetails
       WHERE contactid > 0
       LIMIT 5";
runTest('Q1: contactdetails only', $q1);

// Q2: contactdetails + crmentity (standard base join)
$q2 = "SELECT vtiger_contactdetails.contactid,
              vtiger_contactdetails.firstname,
              vtiger_contactdetails.lastname,
              vtiger_crmentity.smownerid,
              vtiger_crmentity.modifiedtime
       FROM vtiger_contactdetails
       INNER JOIN vtiger_crmentity
         ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
       WHERE vtiger_crmentity.deleted = 0
       LIMIT 5";
runTest('Q2: + vtiger_crmentity', $q2);

// Q3: add vtiger_contactsubdetails (the contactsubscriptionid join)
$q3 = "SELECT vtiger_contactdetails.contactid,
              vtiger_contactdetails.firstname,
              vtiger_contactdetails.lastname,
              vtiger_contactsubdetails.homephone,
              vtiger_crmentity.smownerid
       FROM vtiger_contactdetails
       INNER JOIN vtiger_crmentity
         ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
       INNER JOIN vtiger_contactsubdetails
         ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
       WHERE vtiger_crmentity.deleted = 0
       LIMIT 5";
runTest('Q3: + vtiger_contactsubdetails', $q3);

// Q4: add vtiger_contactscf
$q4 = "SELECT vtiger_contactdetails.contactid,
              vtiger_contactdetails.firstname,
              vtiger_contactdetails.lastname,
              vtiger_contactsubdetails.homephone,
              vtiger_contactscf.cf_723,
              vtiger_crmentity.smownerid
       FROM vtiger_contactdetails
       INNER JOIN vtiger_crmentity
         ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
       INNER JOIN vtiger_contactsubdetails
         ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
       INNER JOIN vtiger_contactscf
         ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
       WHERE vtiger_crmentity.deleted = 0
       LIMIT 5";
runTest('Q4: + vtiger_contactscf', $q4);

// Q5: add vtiger_crmentity_user_field (the starred join)
$q5 = "SELECT vtiger_contactdetails.contactid,
              vtiger_contactdetails.firstname,
              vtiger_contactdetails.lastname,
              vtiger_contactsubdetails.homephone,
              vtiger_contactscf.cf_723,
              vtiger_crmentity.smownerid,
              vtiger_crmentity_user_field.starred
       FROM vtiger_contactdetails
       INNER JOIN vtiger_crmentity
         ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
       INNER JOIN vtiger_contactsubdetails
         ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
       INNER JOIN vtiger_contactscf
         ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
       LEFT JOIN vtiger_crmentity_user_field
         ON vtiger_contactdetails.contactid = vtiger_crmentity_user_field.recordid
        AND vtiger_crmentity_user_field.userid = 5
       WHERE vtiger_crmentity.deleted = 0
       LIMIT 5";
runTest('Q5: + vtiger_crmentity_user_field', $q5);

// Q6: Full Contacts list query, but LIMIT is inline (no placeholders)
$q6 = "SELECT vtiger_contactdetails.firstname,
              vtiger_contactdetails.lastname,
              vtiger_contactdetails.email,
              vtiger_contactdetails.mobile,
              vtiger_contactsubdetails.homephone,
              vtiger_contactscf.cf_723,
              vtiger_contactscf.cf_707,
              vtiger_contactsubdetails.birthday,
              vtiger_crmentity.smownerid,
              vtiger_contactscf.cf_717,
              vtiger_contactscf.cf_729,
              vtiger_contactscf.cf_727,
              vtiger_contactdetails.accountid,
              vtiger_contactdetails.contactid,
              vtiger_crmentity_user_field.starred
       FROM vtiger_contactdetails
       INNER JOIN vtiger_crmentity
         ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
       INNER JOIN vtiger_contactsubdetails
         ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
       INNER JOIN vtiger_contactscf
         ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
       LEFT JOIN vtiger_users
         ON vtiger_crmentity.smownerid = vtiger_users.id
       LEFT JOIN vtiger_groups
         ON vtiger_crmentity.smownerid = vtiger_groups.groupid
       LEFT JOIN vtiger_crmentity_user_field
         ON vtiger_contactdetails.contactid = vtiger_crmentity_user_field.recordid
        AND vtiger_crmentity_user_field.userid = 5
       WHERE vtiger_crmentity.deleted = 0
         AND vtiger_contactdetails.contactid > 0
       ORDER BY vtiger_crmentity.modifiedtime DESC
       LIMIT 0, 20";

runTest('Q6: FULL Contacts query (inline LIMIT)', $q6);

// Q7: Same full query but using pquery + LIMIT ?, ? like ListView does
$q7 = "SELECT vtiger_contactdetails.firstname,
              vtiger_contactdetails.lastname,
              vtiger_contactdetails.email,
              vtiger_contactdetails.mobile,
              vtiger_contactsubdetails.homephone,
              vtiger_contactscf.cf_723,
              vtiger_contactscf.cf_707,
              vtiger_contactsubdetails.birthday,
              vtiger_crmentity.smownerid,
              vtiger_contactscf.cf_717,
              vtiger_contactscf.cf_729,
              vtiger_contactscf.cf_727,
              vtiger_contactdetails.accountid,
              vtiger_contactdetails.contactid,
              vtiger_crmentity_user_field.starred
       FROM vtiger_contactdetails
       INNER JOIN vtiger_crmentity
         ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
       INNER JOIN vtiger_contactsubdetails
         ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
       INNER JOIN vtiger_contactscf
         ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
       LEFT JOIN vtiger_users
         ON vtiger_crmentity.smownerid = vtiger_users.id
       LEFT JOIN vtiger_groups
         ON vtiger_crmentity.smownerid = vtiger_groups.groupid
       LEFT JOIN vtiger_crmentity_user_field
         ON vtiger_contactdetails.contactid = vtiger_crmentity_user_field.recordid
        AND vtiger_crmentity_user_field.userid = 5
       WHERE vtiger_crmentity.deleted = 0
         AND vtiger_contactdetails.contactid > 0
       ORDER BY vtiger_crmentity.modifiedtime DESC
       LIMIT ?, ?";

// Manual version of runTest using pquery with params
echo "========================\n";
echo "Q7: FULL Contacts query (pquery LIMIT ?, ?)\n";
echo "SQL: $q7\n\n";

$params = [0, 20];
$result = $db->pquery($q7, $params);

if (!$result) {
    echo "ERROR: pquery failed\n\n";
} else {
    $rowCount = $db->num_rows($result);
    echo "Row count: $rowCount\n";

    $fieldCount = $result->FieldCount();
    echo "FieldCount: $fieldCount\n";

    for ($i = 0; $i < $fieldCount; $i++) {
        $fld = $result->FetchField($i);
        echo "Col $i: " . $fld->name . "\n";
    }

    echo "\nSample data:\n";
    for ($i = 0; $i < min($rowCount, 3); $i++) {
        $row = [];
        for ($j = 0; $j < $fieldCount; $j++) {
            $fld = $result->FetchField($j);
            $colName = $fld->name;
            $row[$colName] = $db->query_result($result, $i, $j);
        }
        print_r($row);
    }

    echo "\n\n";
}
