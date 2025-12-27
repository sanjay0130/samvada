<?php

class BoruPayments_Uninstall_Action extends Vtiger_Action_Controller
{
    function checkPermission(Vtiger_Request $request)
    {
        return;
    }

    public function process(Vtiger_Request $request)
    {
       define('DS', DIRECTORY_SEPARATOR); 
        require_once('include/utils/utils.php');

        global $adb, $site_URL;

        echo "<br>&nbsp;&nbsp;<b>Uninstall BoruPayments module</b>";

        $adb->pquery("DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)",array('BoruPayments'));
        $adb->pquery("DELETE FROM vtiger_picklist WHERE name IN ('payment_method','payment_status','tran_status','cf_paymenttype','cf_frequency')",array());

        // vtiger_tab
        $sql = "DELETE FROM `vtiger_tab` WHERE `name` = 'BoruPayments';";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- Delete in vtiger_tab";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";
        

        $sql = "DELETE FROM `vtiger_field` WHERE `tablename` LIKE 'vtiger_payments'";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- Delete in vtiger_field";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";
          


        $sql = "DELETE FROM `vtiger_field` WHERE `tablename` LIKE 'vtiger_paymentscf'";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- Delete in vtiger_field";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";
        


        //delete Handler File.
        $sql = "DELETE FROM `vtiger_eventhandlers` WHERE `handler_class` = 'BoruPaymentsHandler';";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- Delete in vtiger_eventhandlers";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        // vtiger_links
        $sql = "DROP TABLE `vtiger_payments`;";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- vtiger_payments  tables";

        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DROP TABLE `vtiger_paymentscf`;";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- vtiger_paymentscf  tables";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DELETE FROM `vtiger_relatedlists` WHERE tabid = 23 and label = 'BoruPayments'";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- vtiger_relatedlists field tables";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DELETE FROM `vtiger_relatedlists` WHERE tabid = 22 and label = 'BoruPayments'";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- vtiger_relatedlists field tables";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";


        $sql = "DROP TABLE `vtiger_borupayments_user_field`;";
        $result = $adb->query($sql);
        echo "<br>&nbsp;&nbsp;- vtiger_borupayments_user_field  tables";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        //Delete Picklist tables
        $sql = "DROP TABLE `vtiger_payment_method`;";
        $result = $adb->query($sql);
        echo"<br> &nbsp;&nbsp; - vtiger_payment_method";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DROP TABLE `vtiger_payment_status`;";
        $result = $adb->query($sql);
        echo"<br> &nbsp;&nbsp; - vtiger_payment_status";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DROP TABLE `vtiger_tran_status`;";
        $result = $adb->query($sql);
        echo"<br> &nbsp;&nbsp; - vtiger_tran_status";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DROP TABLE `vtiger_cf_paymenttype`;";
        $result = $adb->query($sql);
        echo"<br> &nbsp;&nbsp; - vtiger_cf_paymenttype";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";

        $sql = "DROP TABLE `vtiger_cf_frequency`;";
        $result = $adb->query($sql);
        echo"<br> &nbsp;&nbsp; - vtiger_cf_frequency";
        if($result) echo " - DONE"; else echo " - <b>ERROR</b>";


        // remove directory
        $res_template = $this->delete_directory('layouts/v7/modules/BoruPayments');
        $res_module = $this->delete_directory('modules/BoruPayments');
        
        if (!$res_module) {
            echo '
                <div style="margin-bottom: 4px;" class="helpmessagebox">
                    <b style="color: red;">ERROR</b> Can not delete the folder <b>modules/BoruPayments</b>. Please check permission and run script again.
                </div>
            ';
        } else {
            echo "<script>
                alert('Unintall BoruPayments module successfully');
                window.location = '$site_URL';
            </script>";
        }
    }

    
    function delete_directory($dirname){
        if (is_dir($dirname)) $dir_handle = opendir($dirname);
        if (!$dir_handle) return false;
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file)) {
                    unlink($dirname . "/" . $file);
                } else {
                    $this->delete_directory($dirname . '/' . $file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

}