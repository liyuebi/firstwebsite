<?php

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("changeRCL" == $_POST['func']) {
	changeRegiCreditL();
}
else if ("changeRCM" == $_POST['func']) {
	changeRegiCreditM();
}
else if ("changeSCL" == $_POST['func']) {
	changeSaveCreditL();	
}
else if ("changeSCM" == $_POST['func']) {
	changeSaveCreditM();
}
else if ("changeEL" == $_POST['func']) {
	changeExL();
}
else if ("changeEM" == $_POST['func']) {
	changeExM();
}
else if ("changePCL" == $_POST['func']) {
	changePhoneChargeL();
}
else if ("changePCM" == $_POST['func']) {
	changePhoneChargeM();
}
else if ("changeOCL" == $_POST['func']) {
	changeOilCHargeL();	
}
else if ("changeOCM" == $_POST['func']) {
	changeOilCHargeM();
}
else if ("changeDBR" == $_POST['func']) {
	changeDayBonusRate();
}
else if ("changeRBR" == $_POST['func']) {
	changeReferBonusRate();
}
else if ("changeCRR1" == $_POST['func']) {
	changeCollBonusRateRef();
}
else if ("changeCRR2" == $_POST['func']) {
	changeCollBonusRateRei();
}
else {
	tryChangeValue();
}


function changeConfig($name, $val, &$err)
{
	$str = '';
	$fp = fopen("constant.php", 'r');
	if (!$fp) {
		$err = "只读代开文件失败！";
		return false;
	}
	
	while (!feof($fp)) {
		$buf = fgets($fp);
		if (strstr($buf, $name)) {
			$pos1 = strpos($buf, '=');
			$pos2 = strpos($buf, ';');
			
			$sub = substr($buf, $pos1+1, $pos2 - $pos1 - 1);
			$buf = str_replace($sub, $val, $buf);
		}
		$str .= $buf;
	}	
	fclose($fp);
	
	$fp2 = fopen("constant.php", 'w');
	if (!$fp2) {
		$err = "写文件打开文件失败！";
		return false;
	}
	fwrite($fp2, $str);
	fclose($fp2);
	return true;
}

function tryChangeValue()
{
	$func = trim(htmlspecialchars($_POST['func']));
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);

	$paramStr = '';
	if ("changeOFLRF" == $func) {
		$paramStr = "offlineShopRegisterFee";
	}
	else if ("changeWFA" == $func) {
		$paramStr = "withdrawFloorAmount";
	}
	else if ("changeWCA" == $func) {
		$paramStr = "withdrawCeilAmountOneDay";
	}
	else {
		echo json_encode(array('error'=>'true', 'error_code'=>'2','error_msg'=>"找不到处理对应参数的接口！"));
		return;		
	}

	$err_msg = '';
	if (!changeConfig($paramStr, $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeRegiCreditL()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("regiCreditLeast", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeRegiCreditM()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("regiCreditMost", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeSaveCreditL()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("saveCreditLeast", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeSaveCreditM()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("saveCreditMost", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeExL()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("exchangeLeast", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeExM()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("exchangeMost", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changePhoneChargeL()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("phoneChargeLeast", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changePhoneChargeM()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("phoneChargeMost", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeOilCHargeL()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("oilChargeLeast", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeOilCHargeM()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	$err_msg = '';
	if (!changeConfig("oilChargeMost", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeDayBonusRate()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	$err_msg = '';
	if (!changeConfig("dayBonusRate", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeReferBonusRate()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	$err_msg = '';
	if (!changeConfig("referBonusRate", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

/*
 * 修改直推碰撞奖励比例
 */ 
function changeCollBonusRateRef()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	$err_msg = '';
	if (!changeConfig("colliBonusRateRefer", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

/*
 * 修改复投碰撞奖励比例
 */ 
function changeCollBonusRateRei()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	$err_msg = '';
	if (!changeConfig("colliBonusRateReinv", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}


?>