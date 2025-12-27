<?php



//phpinfo();exit;
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*$mbox = imap_open("{imap.gmail.com:993/IMAP4/ssl/novalidate-cert}INBOX", "smartcrm2018@gmail.com", "438AFEblue") or die(imap_last_error()."<br>Connection Failure!");;


echo '<pre>';print_r($mbox); echo '</pre>';

exit;*/



require_once('include/utils/utils.php');

require_once 'config.inc.php';


if (file_exists('config_override.php')) {
    include_once 'config_override.php';
}

// Extended inclusions
require_once 'includes/Loader.php';
vimport('includes.runtime.EntryPoint');

global $adb;

$adb->query("SET FOREIGN_KEY_CHECKS=0;");


$adb->query("ALTER TABLE `vtiger_salutationtype` ADD `color` varchar(10) DEFAULT NULL");
//exit;
$res = $adb->pquery("select fieldname from vtiger_field where uitype in (15,16) and presence in (0,2)", array());
$rowCount = $adb->num_rows($res);

while ($row  = $adb->fetch_array($res)) 
{
	$table = 'vtiger_' . $row['fieldname'];
	$adb->query("ALTER TABLE `$table` ADD `color` varchar(10) DEFAULT NULL");
}


$modules = array( 'Accounts', 'Assets', 'Calendar', 'Campaigns', 'Contacts', 'Emails', 'Faq', 'HelpDesk', 'Invoice', 'Leads', 'Potentials', 'PriceBooks', 'Products', 'Project', 'ProjectMilestone', 'ProjectTask', 'PurchaseOrder', 'Quotes', 'SalesOrder', 'ServiceContracts', 'Services', 'Vendors');

foreach($modules as $module)
{
	$focus = CRMEntity::getInstance($module);
	$index = $focus->table_index;
	$table = 'vtiger_' . strtolower($module) . '_user_field';
	$key = 'fk_' .$index . '_vtiger_' . strtolower($module) . '_user_field';
	$adb->query("CREATE TABLE IF NOT EXISTS `$table` (
  `recordid` int(25) NOT NULL,
  `userid` int(25) NOT NULL,
  `starred` varchar(100) DEFAULT NULL,
  KEY `$key` (`recordid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}


$modules = array( 'Invoice', 'PurchaseOrder', 'Quotes', 'SalesOrder');

foreach($modules as $module)
{
	$table = 'vtiger_' . strtolower($module);
	$adb->query("ALTER TABLE `$table` ADD `compound_taxes_info` text");
}

$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_inventorychargesrel` (
  `recordid` int(19) NOT NULL,
  `charges` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");


$adb->query("ALTER TABLE `vtiger_inventorytaxinfo`
	ADD `method` varchar(10) DEFAULT NULL,
	ADD `type` varchar(10) DEFAULT NULL,
	ADD `compoundon` varchar(400) DEFAULT NULL,
	ADD `regions` text;");



$adb->query("ALTER TABLE `vtiger_taskstatus` ADD `color` varchar(10) DEFAULT NULL");

$adb->query("ALTER TABLE `vtiger_taskpriority` ADD `color` varchar(10) DEFAULT NULL");



$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_rollupcomments_settings` (
  `rollupid` int(19) NOT NULL AUTO_INCREMENT,
  `userid` int(19) NOT NULL,
  `tabid` int(19) NOT NULL,
  `rollup_status` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rollupid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;");


$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_app2tab` (
  `tabid` int(11) DEFAULT NULL,
  `appname` varchar(20) DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `visible` tinyint(3) DEFAULT '1',
  KEY `vtiger_app2tab_fk_tab` (`tabid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$adb->query("INSERT INTO `vtiger_app2tab` (`tabid`, `appname`, `sequence`, `visible`) VALUES
(2, 'SALES', 1, 0),
(20, 'SALES', 2, 0),
(4, 'SALES', 1, 1),
(6, 'SALES', 2, 1),
(14, 'SALES', 3, 0),
(36, 'SALES', 3, 1),
(4, 'MARKETING', 2, 1),
(6, 'MARKETING', 3, 1),
(7, 'MARKETING', 1, 1),
(26, 'MARKETING', 1, 1),
(4, 'INVENTORY', 8, 1),
(6, 'INVENTORY', 9, 1),
(22, 'INVENTORY', 5, 1),
(23, 'INVENTORY', 4, 1),
(14, 'INVENTORY', 1, 1),
(18, 'INVENTORY', 7, 1),
(19, 'INVENTORY', 3, 1),
(21, 'INVENTORY', 6, 1),
(36, 'INVENTORY', 12, 1),
(4, 'SUPPORT', 6, 1),
(6, 'SUPPORT', 7, 1),
(13, 'SUPPORT', 1, 1),
(15, 'SUPPORT', 2, 1),
(31, 'SUPPORT', 3, 1),
(45, 'SUPPORT', 12, 1),
(40, 'PROJECT', 12, 1),
(39, 'PROJECT', 1, 1),
(38, 'PROJECT', 2, 1),
(4, 'PROJECT', 4, 1),
(6, 'PROJECT', 5, 1),
(34, 'PROJECT', 13, 1),
(16, '', 1, 1),
(44, 'SALES', 4, 1),
(44, 'SUPPORT', 13, 1),
(50, 'PROJECT', 14, 1),
(2, 'SALES', 1, 0),
(20, 'SALES', 2, 0),
(4, 'SALES', 1, 1),
(6, 'SALES', 2, 1),
(14, 'SALES', 3, 0),
(36, 'SALES', 3, 1),
(4, 'MARKETING', 2, 1),
(6, 'MARKETING', 3, 1),
(7, 'MARKETING', 1, 1),
(26, 'MARKETING', 1, 1),
(4, 'INVENTORY', 8, 1),
(6, 'INVENTORY', 9, 1),
(22, 'INVENTORY', 5, 1),
(23, 'INVENTORY', 4, 1),
(14, 'INVENTORY', 1, 1),
(18, 'INVENTORY', 7, 1),
(19, 'INVENTORY', 3, 1),
(21, 'INVENTORY', 6, 1),
(36, 'INVENTORY', 12, 1),
(4, 'SUPPORT', 6, 1),
(6, 'SUPPORT', 7, 1),
(13, 'SUPPORT', 1, 1),
(15, 'SUPPORT', 2, 1),
(31, 'SUPPORT', 3, 1),
(45, 'SUPPORT', 12, 1),
(40, 'PROJECT', 12, 1),
(39, 'PROJECT', 1, 1),
(38, 'PROJECT', 2, 1),
(4, 'PROJECT', 4, 1),
(6, 'PROJECT', 5, 1),
(34, 'PROJECT', 13, 1),
(16, '', 1, 1),
(44, 'SALES', 4, 1),
(44, 'SUPPORT', 13, 1),
(50, 'PROJECT', 14, 1),
(2, 'SALES', 1, 0),
(20, 'SALES', 2, 0),
(4, 'SALES', 1, 1),
(6, 'SALES', 2, 1),
(14, 'SALES', 3, 0),
(36, 'SALES', 3, 1),
(4, 'MARKETING', 2, 1),
(6, 'MARKETING', 3, 1),
(7, 'MARKETING', 1, 1),
(26, 'MARKETING', 1, 1),
(4, 'INVENTORY', 8, 1),
(6, 'INVENTORY', 9, 1),
(22, 'INVENTORY', 5, 1),
(23, 'INVENTORY', 4, 1),
(14, 'INVENTORY', 1, 1),
(18, 'INVENTORY', 7, 1),
(19, 'INVENTORY', 3, 1),
(21, 'INVENTORY', 6, 1),
(36, 'INVENTORY', 12, 1),
(4, 'SUPPORT', 6, 1),
(6, 'SUPPORT', 7, 1),
(13, 'SUPPORT', 1, 1),
(15, 'SUPPORT', 2, 1),
(31, 'SUPPORT', 3, 1),
(45, 'SUPPORT', 12, 1),
(40, 'PROJECT', 12, 1),
(39, 'PROJECT', 1, 1),
(38, 'PROJECT', 2, 1),
(4, 'PROJECT', 4, 1),
(6, 'PROJECT', 5, 1),
(34, 'PROJECT', 13, 1),
(16, '', 1, 1),
(44, 'SALES', 4, 1),
(44, 'SUPPORT', 13, 1),
(50, 'PROJECT', 14, 1),
(32, 'SALES', 4, 0),
(32, 'INVENTORY', 2, 1),
(43, 'SUPPORT', 4, 1),
(37, 'PROJECT', 3, 1),
(41, 'SALES', 5, 1),
(41, 'SUPPORT', 5, 1),
(50, 'TOOLS', 5, 1),
(24, 'TOOLS', 2, 1),
(27, 'TOOLS', 3, 1),
(40, 'TOOLS', 1, 1),
(47, 'TOOLS', 4, 1);");


$adb->query("ALTER TABLE `vtiger_app2tab`
  ADD CONSTRAINT `vtiger_app2tab_fk_tab` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE;");

$adb->query("SET FOREIGN_KEY_CHECKS=1;");

echo 'done';


?>