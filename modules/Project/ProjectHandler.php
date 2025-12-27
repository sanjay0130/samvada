<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
# getModuleName : Returns the module name of the entity.
# getId : Returns id of the entity, this will return null if the id has not been saved yet.
# getData : Returns the fields of the entity as an array where the field name is the key and the fields value is the value.
# isNew : Returns true if new record is being created, false otherwise. 
# 'vtiger.entity.beforesave.modifiable' : Setting values : $data->set('simple_field', 'value'); 

class ProjectHandler extends VTEventHandler {
	function get_contact_id($contactname)
	{
		global $adb;
		$sql=$adb->pquery("SELECT contactid FROM vtiger_contactdetails WHERE TRIM(CONCAT(firstname,' ',lastname))=?",array(trim($contactname)));
		$resultinfo = $adb->fetch_array($sql);
		return $resultinfo["contactid"];
		
	}
	function partner_information($crmid)
	{
		global $adb;
		$moduleName="Contacts";
		
		if($crmid>0)
		{
		 $sql=$adb->pquery("SELECT * FROM vtiger_crmentity WHERE crmid=? AND deleted=?",array($crmid,0));
			 if($adb->num_rows($sql)>0){
				$recordModel = Vtiger_Record_Model::getInstanceById($crmid, $moduleName);
				$data = $recordModel->getData();
			 }
		}
		else $data=array();
		return $data;
	}
	function handleEvent($eventName, $entityData) {
		global $adb;
		$moduleName = $entityData->getModuleName();	
		if($moduleName=='Project'){		
			if($eventName == 'vtiger.entity.beforesave.modifiable') {}
			if($eventName == 'vtiger.entity.beforesave') {
				$entityID = $entityData->getId();
				$_SESSION["BOSS_PHASE"]="";
				$_SESSION["CLAIM_CREDIT_SPECIAL"]="";
				if($entityID>0)
				{
					
					$sql=$adb->pquery("SELECT cf_1098,cf_1177 FROM vtiger_projectcf WHERE projectid=?",array($entityID));
					$resultinfo = $adb->fetch_array($sql);
					$bossphase=$resultinfo['cf_1098'];
					$claim_credit_special=$resultinfo['cf_1177'];
					$_SESSION["BOSS_PHASE"]=$bossphase;
					$_SESSION["CLAIM_CREDIT_SPECIAL"]=$claim_credit_special;
				}
			}
			if($eventName == 'vtiger.entity.beforesave.final') {}
			if($eventName == 'vtiger.entity.aftersave') {
				
				$entityID = $entityData->getId();
				$recordModel = Vtiger_Record_Model::getInstanceById($entityID, $moduleName);
				$data = $recordModel->getData();
				$prj_analyst=$data["prj_analyst"];
				$prj_analyst=$this->get_contact_id($prj_analyst);
				$prj_accountant=$data["prj_accountant"];
				$prj_accountant=$this->get_contact_id($prj_accountant);
				$financial_auditor=$data["cf_982"];
				$financial_auditor=$this->get_contact_id($financial_auditor);
				$tech_auditor=$data["cf_988"];
				$tech_auditor=$this->get_contact_id($tech_auditor);
				$legalpartner=$data["prj_legalpartner"];
				$legalpartner=$this->get_contact_id($legalpartner);
				
				
				
				$sql=$adb->pquery("DELETE FROM vtiger_crmentityrel WHERE crmid=? AND module=? AND relmodule=?",array($entityID,"Project","Contacts"));
				if($prj_analyst>0)
				{
				$sql=$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?,module=?,relmodule=?,relcrmid=?",array($entityID,"Project","Contacts",$prj_analyst));
				$sql=$adb->pquery("UPDATE vtiger_project SET prj_analyst_readonly=? WHERE projectid=?",array($prj_analyst,$entityID));
				}
				if($prj_accountant>0)
				{
				$sql=$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?,module=?,relmodule=?,relcrmid=?",array($entityID,"Project","Contacts",$prj_accountant));
				 $sql=$adb->pquery("UPDATE vtiger_project SET prj_accountant_readonly=? WHERE projectid=?",array($prj_accountant,$entityID));
				}
				if($financial_auditor>0)
				{
				$sql=$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?,module=?,relmodule=?,relcrmid=?",array($entityID,"Project","Contacts",$financial_auditor));
				$sql=$adb->pquery("UPDATE vtiger_project SET prj_financialauditor_readonly=? WHERE projectid=?",array($financial_auditor,$entityID));
				}
				if($tech_auditor>0)
				{
				$sql=$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?,module=?,relmodule=?,relcrmid=?",array($entityID,"Project","Contacts",$tech_auditor));
				$sql=$adb->pquery("UPDATE vtiger_project SET prj_techauditor_readonly=? WHERE projectid=?",array($tech_auditor,$entityID));
				}
				if($legalpartner>0)
				{
				$sql=$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?,module=?,relmodule=?,relcrmid=?",array($entityID,"Project","Contacts",$legalpartner));
				$sql=$adb->pquery("UPDATE vtiger_project SET prj_legalpartner_readonly=? WHERE projectid=?",array($legalpartner,$entityID));
				}
				
				$boss_phase=$data["cf_1098"];
				$claim_credit_special=$data['cf_1177'];
				$pay_raise_due=$data["cf_1272"];
				$update_analyst_sub_credit_life_time=false;
				$sql=$adb->pquery("SELECT * FROM vk_checkonce WHERE crmid=?",array($entityID));
				
				if($adb->num_rows($sql)==0 && $boss_phase!=$_SESSION["BOSS_PHASE"] && in_array($boss_phase, array("Accountant","Government")))
				{
					$claim_sub_credit=$data["cf_1235"];
					$claim_credit_special=$data["cf_1177"];
					$total_credit_special=$claim_sub_credit+$claim_credit_special;
					$this->update_current_qtr_sub_credit($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner,$total_credit_special);
					$this->update_analyst_sub_credit_life_time($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner,$total_credit_special);
					 $update_analyst_sub_credit_life_time=true;
					 $today = date("Y-m-d H:i:s");
					$sql=$adb->pquery("INSERT INTO vk_checkonce SET crmid=?,datentime=?",array($entityID,$today));
				}
				
				if($claim_credit_special!=$_SESSION["CLAIM_CREDIT_SPECIAL"] && $claim_credit_special>0 &&  in_array($boss_phase, array("Accountant","Government")))
					{
					 $this->update_current_qtr_sub_credit($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner,$claim_credit_special);
					
					 $update_analyst_sub_credit_life_time=true;
					 
					 
					$sql=$adb->pquery("SELECT cf_1251 FROM vtiger_contactscf WHERE contactid=?",array($prj_analyst));
					$resultinfo = $adb->fetch_array($sql);
					$analyst_sub_credit_life_time=$resultinfo["cf_1251"];
					$analyst_sub_credit_life_time=$analyst_sub_credit_life_time+$claim_credit_special;
					$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=? WHERE contactid=?",array($analyst_sub_credit_life_time,$prj_analyst));
					 
					}
					
				$this->update_good_standing_current_quarter($prj_analyst);
				$this->update_good_standing_current_quarter($prj_accountant);
				$this->update_good_standing_current_quarter($financial_auditor);
				$this->update_good_standing_current_quarter($tech_auditor);
				$this->update_good_standing_current_quarter($legalpartner);
				$info=$this->partner_information($prj_analyst);
				
				  	$analyst_sub_credit_life_time=$info["cf_1251"];
					if($analyst_sub_credit_life_time>10)   // Additional checking for Pay Raise Due 
					{
						$analyst_sub_credit_life_time=$analyst_sub_credit_life_time%10;
						$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=?,cf_1272=? WHERE contactid=?",array($analyst_sub_credit_life_time,"Yes",$prj_analyst));
					}
				$this->Submission_Bonus_Due($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner);
				
				
				if($boss_phase!=$_SESSION["BOSS_PHASE"] && in_array($boss_phase, array("Paid","Paid – installment")))
				{
					$com_credit=$data["cf_1253"];
					$TotalBondFees=$data["cf_1046"];
					$com_credit_special=$data["cf_1255"];
					$this->update_Analyst_Com_Credits($prj_analyst,$com_credit,$TotalBondFees,$com_credit_special);
					
					
				}
				
			}
		}	
	}
	function update_Analyst_Com_Credits($crmid,$com_credit,$TotalBondFees,$com_credit_special)
	{
		global $adb;
		$sql=$adb->pquery("SELECT cf_1261,cf_1259,cf_1257,cf_1265 FROM vtiger_contactscf WHERE contactid=?",array($crmid));
		$resultinfo = $adb->fetch_array($sql);
		$AnalystComCredits=$resultinfo["cf_1261"];
		$AnalystComRate=$resultinfo["cf_1259"];
		$AnalystMaxCom =$resultinfo["cf_1257"];
		$AnalystCommision =$resultinfo["cf_1265"];
		$expression=$AnalystComCredits+$com_credit+$com_credit_special;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1261=? WHERE contactid=?",array($expression,$crmid));
		$AnalystComCredits=$expression;
		if($AnalystComCredits>10)
		//if($expression>10)
		{
		
			if($AnalystComRate>=$AnalystMaxCom)
			{
				$AnalystCommision=$AnalystCommision+$AnalystComRate*$TotalBondFees;
				$AnalystCommision=$AnalystCommision/100;
				$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1265=? WHERE contactid=?",array($AnalystCommision,$crmid));
			}
			else
			{
				$AnalystComRate=$AnalystComRate+1;
				$AnalystCommision=$AnalystCommision+$AnalystComRate*$TotalBondFees;
				$AnalystCommision=$AnalystCommision/100;
				$AnalystComCredits=$AnalystComCredits%10;
				$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1259=?,cf_1265=?,cf_1261=? WHERE contactid=?",array($AnalystComRate,$AnalystCommision,$AnalystComCredits,$crmid));
			}
		}
		else
		{
			$AnalystCommision=$AnalystCommision+$AnalystComRate*$TotalBondFees;
			$AnalystCommision=$AnalystCommision/100;
			$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1265=? WHERE contactid=?",array($AnalystCommision,$crmid));
		}
		return;
	}
	
	function Submission_Bonus_Due($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner)
	{
		global $adb;
		$sql=$adb->pquery("SELECT cf_1187,cf_1191,cf_1189,cf_1239,cf_1241 FROM vtiger_contactscf WHERE contactid=?",array($prj_analyst));
		$resultinfo = $adb->fetch_array($sql);
		$CurrentQrtrSubCredits=$resultinfo["cf_1187"];
		$LastQrtrSubCredits=$resultinfo["cf_1189"];
		$SubQuota=$resultinfo["cf_1191"];
		$GoodStandingLastQtr=$resultinfo["cf_1239"];
		$GoodStandingCrrentQtr=$resultinfo["cf_1241"];
		$expression1=$CurrentQrtrSubCredits+$LastQrtrSubCredits;
		$expression2=2*$SubQuota;
		
		
		if($expression1 > $expression2 && $GoodStandingLastQtr=='Yes' && $GoodStandingCrrentQtr=='Yes')
		{
			$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1195=? WHERE contactid=?",array("Yes",$prj_analyst));
		}
		
		$sql=$adb->pquery("SELECT cf_1187,cf_1191,cf_1189,cf_1239,cf_1241 FROM vtiger_contactscf WHERE contactid=?",array($prj_accountant));
		$resultinfo = $adb->fetch_array($sql);
		$CurrentQrtrSubCredits=$resultinfo["cf_1187"];
		$LastQrtrSubCredits=$resultinfo["cf_1189"];
		$SubQuota=$resultinfo["cf_1191"];
		$GoodStandingLastQtr=$resultinfo["cf_1239"];
		$GoodStandingCrrentQtr=$resultinfo["cf_1241"];
		$expression1=$CurrentQrtrSubCredits+$LastQrtrSubCredits;
		$expression2=2*$SubQuota;
		if($expression1 > $expression2 && $GoodStandingLastQtr=='Yes' && $GoodStandingCrrentQtr=='Yes')
		{
			$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1195=? WHERE contactid=?",array("Yes",$prj_accountant));
		}
		
		$sql=$adb->pquery("SELECT cf_1187,cf_1191,cf_1189,cf_1239,cf_1241 FROM vtiger_contactscf WHERE contactid=?",array($financial_auditor));
		$resultinfo = $adb->fetch_array($sql);
		$CurrentQrtrSubCredits=$resultinfo["cf_1187"];
		$LastQrtrSubCredits=$resultinfo["cf_1189"];
		$SubQuota=$resultinfo["cf_1191"];
		$GoodStandingLastQtr=$resultinfo["cf_1239"];
		$GoodStandingCrrentQtr=$resultinfo["cf_1241"];
		$expression1=$CurrentQrtrSubCredits+$LastQrtrSubCredits;
		$expression2=2*$SubQuota;
		if($expression1 > $expression2 && $GoodStandingLastQtr=='Yes' && $GoodStandingCrrentQtr=='Yes')
		{
			$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1195=? WHERE contactid=?",array("Yes",$financial_auditor));
		}
		
		$sql=$adb->pquery("SELECT cf_1187,cf_1191,cf_1189,cf_1239,cf_1241 FROM vtiger_contactscf WHERE contactid=?",array($tech_auditor));
		$resultinfo = $adb->fetch_array($sql);
		$CurrentQrtrSubCredits=$resultinfo["cf_1187"];
		$LastQrtrSubCredits=$resultinfo["cf_1189"];
		$SubQuota=$resultinfo["cf_1191"];
		$GoodStandingLastQtr=$resultinfo["cf_1239"];
		$GoodStandingCrrentQtr=$resultinfo["cf_1241"];
		$expression1=$CurrentQrtrSubCredits+$LastQrtrSubCredits;
		$expression2=2*$SubQuota;
		if($expression1 > $expression2 && $GoodStandingLastQtr=='Yes' && $GoodStandingCrrentQtr=='Yes')
		{
			$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1195=? WHERE contactid=?",array("Yes",$tech_auditor));
		}
		
		$sql=$adb->pquery("SELECT cf_1187,cf_1191,cf_1189,cf_1239,cf_1241 FROM vtiger_contactscf WHERE contactid=?",array($legalpartner));
		$resultinfo = $adb->fetch_array($sql);
		$CurrentQrtrSubCredits=$resultinfo["cf_1187"];
		$LastQrtrSubCredits=$resultinfo["cf_1189"];
		$SubQuota=$resultinfo["cf_1191"];
		$GoodStandingLastQtr=$resultinfo["cf_1239"];
		$GoodStandingCrrentQtr=$resultinfo["cf_1241"];
		$expression1=$CurrentQrtrSubCredits+$LastQrtrSubCredits;
		$expression2=2*$SubQuota;
		if($expression1 > $expression2 && $GoodStandingLastQtr=='Yes' && $GoodStandingCrrentQtr=='Yes')
		{
			$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1195=? WHERE contactid=?",array("Yes",$legalpartner));
		}
		return ;
		
	}
	function update_good_standing_current_quarter($crmid)
	{
		global $adb;
		$sql=$adb->pquery("SELECT cf_1187,cf_1191,cf_1239,cf_1249,cf_1189 FROM vtiger_contactscf WHERE contactid=?",array($crmid));
		$resultinfo = $adb->fetch_array($sql);
		$current_qtr_sub_credit=$resultinfo["cf_1187"];
		$sub_quota=$resultinfo["cf_1191"];
		$good_standing_last_qtr=$resultinfo["cf_1239"];
		$CurrentQtrBonusCredits=$resultinfo["cf_1249"];
		$LastQrtrSubCredits=$resultinfo["cf_1189"];
		if($current_qtr_sub_credit>$sub_quota)
		{
			if($good_standing_last_qtr=='Yes')
			{
				$expression1=$current_qtr_sub_credit-$sub_quota;
			//	$CurrentQtrBonusCredits=$CurrentQtrBonusCredits+$expression1;
				$CurrentQtrBonusCredits=$expression1;
				$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1249=? WHERE contactid=?",array($CurrentQtrBonusCredits,$crmid));
			}
			else if($good_standing_last_qtr=='No')
			{
				$expression1=$current_qtr_sub_credit-$sub_quota;
				$expression2=$LastQrtrSubCredits-$sub_quota;
				if($expression2<0)
				{
					$expression5=$LastQrtrSubCredits+$current_qtr_sub_credit-$sub_quota;
					if($expression5 < $sub_quota)
					$expression3=$expression5;
					else $expression3=$sub_quota;
					$expression4=$expression3;
					
					
					
					$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1189=? WHERE contactid=?",array($expression4,$crmid));
					$current_qtr_sub_credit=$current_qtr_sub_credit-$sub_quota-$LastQrtrSubCredits;
					$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1187=? WHERE contactid=?",array($current_qtr_sub_credit,$crmid));
				}
				$sql=$adb->pquery("SELECT cf_1189,cf_1187 FROM vtiger_contactscf WHERE contactid=?",array($crmid));
		        $resultinfo = $adb->fetch_array($sql);
				$LastQrtrSubCredits=$resultinfo["cf_1189"];
				$CurrentQrtrSubCredits=$resultinfo["cf_1187"];
				if($LastQrtrSubCredits==$sub_quota)
				{
					$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1239=? WHERE contactid=?",array("Yes",$crmid));
				}
				$expression5=$CurrentQrtrSubCredits-$sub_quota;
				$expression6=$LastQrtrSubCredits-$sub_quota;
				if($expression5+$expression6>0)
				{
					$CurrentQtrBonusCredits=$expression5+$expression6;
					$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1249=? WHERE contactid=?",array($CurrentQtrBonusCredits,$crmid));
					
				}
				
			}
		}
		return;
		
	}
	function update_current_qtr_sub_credit($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner,$toadd)
	{
		global $adb;
		$info=$this->partner_information($prj_analyst);
		$current_qtr_sub_credit=$info["cf_1187"];
		$updated_sub_credit=$current_qtr_sub_credit+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1187=? WHERE contactid=?",array($updated_sub_credit,$prj_analyst));
		
		$sql=$adb->pquery("SELECT cf_1191 FROM vtiger_contactscf WHERE contactid=?",array($prj_analyst));
		$resultinfo = $adb->fetch_array($sql);
		$subquota=$resultinfo["cf_1191"];
		if($updated_sub_credit>$subquota)
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1241=? WHERE contactid=?",array('Yes',$prj_analyst));
		
		
		$info=$this->partner_information($prj_accountant);
		$current_qtr_sub_credit=$info["cf_1187"];
		$updated_sub_credit=$current_qtr_sub_credit+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1187=? WHERE contactid=?",array($updated_sub_credit,$prj_accountant));
		
		$sql=$adb->pquery("SELECT cf_1191 FROM vtiger_contactscf WHERE contactid=?",array($prj_accountant));
		$resultinfo = $adb->fetch_array($sql);
		$subquota=$resultinfo["cf_1191"];
		if($updated_sub_credit>$subquota)
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1241=? WHERE contactid=?",array('Yes',$prj_accountant));
		
		
		$info=$this->partner_information($financial_auditor);
		$current_qtr_sub_credit=$info["cf_1187"];
		$updated_sub_credit=$current_qtr_sub_credit+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1187=? WHERE contactid=?",array($updated_sub_credit,$financial_auditor));
		
		$sql=$adb->pquery("SELECT cf_1191 FROM vtiger_contactscf WHERE contactid=?",array($financial_auditor));
		$resultinfo = $adb->fetch_array($sql);
		$subquota=$resultinfo["cf_1191"];
		if($updated_sub_credit>$subquota)
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1241=? WHERE contactid=?",array('Yes',$financial_auditor));
		
		
		$info=$this->partner_information($tech_auditor);
		$current_qtr_sub_credit=$info["cf_1187"];
		$updated_sub_credit=$current_qtr_sub_credit+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1187=? WHERE contactid=?",array($updated_sub_credit,$tech_auditor));
		
		$sql=$adb->pquery("SELECT cf_1191 FROM vtiger_contactscf WHERE contactid=?",array($tech_auditor));
		$resultinfo = $adb->fetch_array($sql);
		$subquota=$resultinfo["cf_1191"];
		if($updated_sub_credit>$subquota)
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1241=? WHERE contactid=?",array('Yes',$tech_auditor));
		
		
		$info=$this->partner_information($legalpartner);
		$current_qtr_sub_credit=$info["cf_1187"];
		$updated_sub_credit=$current_qtr_sub_credit+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1187=? WHERE contactid=?",array($updated_sub_credit,$legalpartner));
		
		$sql=$adb->pquery("SELECT cf_1191 FROM vtiger_contactscf WHERE contactid=?",array($legalpartner));
		$resultinfo = $adb->fetch_array($sql);
		$subquota=$resultinfo["cf_1191"];
		if($updated_sub_credit>$subquota)
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1241=? WHERE contactid=?",array('Yes',$legalpartner));
		
		
		return;
	}
	
	
	function update_analyst_sub_credit_life_time($prj_analyst,$prj_accountant,$financial_auditor,$tech_auditor,$legalpartner,$toadd)
	{
		global $adb;
		$info=$this->partner_information($prj_analyst);
		$analyst_sub_credit_life_time=$info["cf_1251"];
		$analyst_sub_credit_life_time=$analyst_sub_credit_life_time+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=? WHERE contactid=?",array($analyst_sub_credit_life_time,$prj_analyst));
		
		$info=$this->partner_information($prj_accountant);
		$analyst_sub_credit_life_time=$info["cf_1251"];
		$analyst_sub_credit_life_time=$analyst_sub_credit_life_time+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=? WHERE contactid=?",array($analyst_sub_credit_life_time,$prj_accountant));
		
		$info=$this->partner_information($financial_auditor);
		$analyst_sub_credit_life_time=$info["cf_1251"];
		$analyst_sub_credit_life_time=$analyst_sub_credit_life_time+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=? WHERE contactid=?",array($analyst_sub_credit_life_time,$financial_auditor));
		
		$info=$this->partner_information($tech_auditor);
		$analyst_sub_credit_life_time=$info["cf_1251"];
		$analyst_sub_credit_life_time=$analyst_sub_credit_life_time+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=? WHERE contactid=?",array($analyst_sub_credit_life_time,$tech_auditor));
		
		$info=$this->partner_information($legalpartner);
		$analyst_sub_credit_life_time=$info["cf_1251"];
		$analyst_sub_credit_life_time=$analyst_sub_credit_life_time+$toadd;
		$sql=$adb->pquery("UPDATE vtiger_contactscf SET cf_1251=? WHERE contactid=?",array($analyst_sub_credit_life_time,$legalpartner));
		return;
	}
	
}
?>