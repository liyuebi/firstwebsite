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

function changeConfig($name, $val)
{
	$str = '';
	$fp = fopen("constant.php", 'r');
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
	fwrite($fp2, $str);
	fclose($fp2);
}

function changeFloorValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	changeConfig("withdrawFloorAmount", $val);
	echo json_encode(array('error'=>'false'));
}

function changeCeilValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	changeConfig("withdrawCeilAmountOneDay", $val);
	echo json_encode(array('error'=>'false'));
}

function changeRwdRateValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = floatval($val);
	
	changeConfig("rewardRate", $val);
	echo json_encode(array('error'=>'false'));
}

function changeTransferFloorValue()
{
	$val = trim(htmlspecialchars($_POST['val']));
	$val = intval($val);
	
	changeConfig("transferFloorAmount", $val);
	echo json_encode(array('error'=>'false'));
}

?>