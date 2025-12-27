<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_ExportPDFTemplate_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {
    }

    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $Templates = explode(";", $request->get('templates'));

        sort($Templates);
        $c = '';

        if (count($Templates) > 0) {
            foreach ($Templates AS $templateid) {
                $sql = "SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
                  FROM vtiger_pdfmaker 
                  LEFT JOIN vtiger_pdfmaker_settings
                    ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
                 WHERE vtiger_pdfmaker.templateid=?";

                $result = $adb->pquery($sql, array($templateid));
                $num_rows = $adb->num_rows($result);

                if ($num_rows > 0) {
                    $pdftemplateResult = $adb->fetch_array($result);

                    $Margins = array("top" => $pdftemplateResult["margin_top"],
                        "bottom" => $pdftemplateResult["margin_bottom"],
                        "left" => $pdftemplateResult["margin_left"],
                        "right" => $pdftemplateResult["margin_right"]);

                    $Decimals = array("point" => $pdftemplateResult["decimal_point"],
                        "decimals" => $pdftemplateResult["decimals"],
                        "thousands" => $pdftemplateResult["thousands_separator"]);

                    $templatename = $pdftemplateResult["filename"];
                    $nameOfFile = $pdftemplateResult["file_name"];
                    $description = $pdftemplateResult["description"];
                    $module = $pdftemplateResult["module"];

                    $body = $pdftemplateResult["body"];
                    $header = $pdftemplateResult["header"];
                    $footer = $pdftemplateResult["footer"];

                    $format = $pdftemplateResult["format"];
                    $orientation = $pdftemplateResult["orientation"];

                    $c .= "<template>";
                    $c .= "<type>PDFMaker</type>";
                    $c .= "<templatename>" . $this->cdataEncode($templatename, true) . "</templatename>";
                    $c .= "<filename>" . $this->cdataEncode($nameOfFile, true) . "</filename>";
                    $c .= "<description>" . $this->cdataEncode($description, true) . "</description>";
                    $c .= "<module>" . $this->cdataEncode($module) . "</module>";
                    $c .= "<settings>";
                    $c .= "<format>" . $this->cdataEncode($format) . "</format>";
                    $c .= "<orientation>" . $this->cdataEncode($orientation) . "</orientation>";
                    $c .= "<margins>";
                    $c .= "<top>" . $this->cdataEncode($Margins["top"]) . "</top>";
                    $c .= "<bottom>" . $this->cdataEncode($Margins["bottom"]) . "</bottom>";
                    $c .= "<left>" . $this->cdataEncode($Margins["left"]) . "</left>";
                    $c .= "<right>" . $this->cdataEncode($Margins["right"]) . "</right>";
                    $c .= "</margins>";
                    $c .= "<decimals>";
                    $c .= "<point>" . $this->cdataEncode($Decimals["point"]) . "</point>";
                    $c .= "<decimals>" . $this->cdataEncode($Decimals["decimals"]) . "</decimals>";
                    $c .= "<thousands>" . $this->cdataEncode($Decimals["thousands"]) . "</thousands>";
                    $c .= "</decimals>";
                    $c .= "</settings>";

                    $c .= "<header>";
                    $c .= $this->cdataEncode($header, true);
                    $c .= "</header>";

                    $c .= "<body>";
                    $c .= $this->cdataEncode($body, true);
                    $c .= "</body>";

                    $c .= "<footer>";
                    $c .= $this->cdataEncode($footer, true);
                    $c .= "</footer>";

                    $c .= "</template>";
                }
            }
        }

        header('Content-Type: application/xhtml+xml');
        header("Content-Disposition: attachment; filename=export.xml");

        echo "<?xml version='1.0'?" . ">";
        echo "<export>";
        echo $c;
        echo "</export>";

        exit;
    }

    private function cdataEncode($text, $encode = false) {
        $From = array("<![CDATA[", "]]>");
        $To = array("<|!|[%|CDATA|[%|", "|%]|]|>");

        if ($text != "") {
            $pos1 = strpos("<![CDATA[", $text);
            $pos2 = strpos("]]>", $text);

            if ($pos1 === false && $pos2 === false && $encode == false) {
                $content = $text;
            } else {
                $text = decode_html($text);
                $encode_text = str_replace($From, $To, $text);

                $content = "<![CDATA[" . $encode_text . "]]>";
            }
        } else {
            $content = "";
        }

        return $content;
    }
}