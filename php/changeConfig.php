<?php

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("changeFloor" == $_POST['func']) {
	changeFloorValue();	
}
else if ("changeCeil" == $_POST['func']) {
	changeCeilValue();
}
else if ("changeRwdRate" == $_POST['func']) {
	changeRwdRateValue();
}
else if ("changeTransferFloor" == $_POST['func']) {
	changeTransferFloorValue();
}
else if ("changeNUF" == $_POST['func']) {
	changeNewUserVault();
}
else if ("changeNAF" == $_POST['func']) {
	changeNewAccountVault();	
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

function changeFloorValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("withdrawFloorAmount", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeCeilValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("withdrawCeilAmountOneDay", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeRwdRateValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	$err_msg = '';
	if (!changeConfig("rewardRate", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeTransferFloorValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("transferFloorAmount", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeNewUserVault()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("dyNewUserVault", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

function changeNewAccountVault()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	$err_msg = '';
	if (!changeConfig("dyNewAccountVault", $val, $err_msg)) {
		echo json_encode(array('error'=>'true', 'error_code'=>'1','error_msg'=>$err_msg));
		return;
	}
	echo json_encode(array('error'=>'false'));
}

?>