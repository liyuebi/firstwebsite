<?php 

include 'database.php';

if (!isset($_POST['func'])
 	&& !isset($_GET['func'])) {
	exit('非法访问！');
}

if ($_SERVER['REQUEST_METHOD']=="POST") {
	if ("addaddress" == $_POST['func']) {
		addAddress();	
	}
	else if ("editaddress" == $_POST['func']) {
		editAddress();
	}
	else if ("deleteaddress" == $_POST['func']) {
		deleteAddress();
	}
}
else if ($_SERVER['REQUEST_METHOD']=="GET") {
	if ("getAddresses" == $_GET['func'] ) {
		getAddresses();
	}
	else if ("changeDefaultAdd" == $_GET['func']) {
		changeDefaultAddress();
	}
}

function getAddresses()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$userId = $_SESSION["userId"];
	
	$result = mysql_query("select * from Address where UserId = '$userId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'提取地址信息出错，请稍后重试！'));
		return;
	}
	else {
		$num = mysql_num_rows($result);
		$ret = array();
		while($row = mysql_fetch_array($result))
		{
			$arr = array("receiver"=>$row["Receiver"],
						 	"phone"=>$row["PhoneNum"],
						 	"address"=>$row["Address"],
						 	"zipcode"=>$row["ZipCode"]);
		 	$ret[$row["AddressId"]] = $arr;
		}
		
		$defaultAdd = 0;
		$res1 = mysql_query("select * from User where UserId='$userId'");
		if ($res1) {
			$row1 = mysql_fetch_assoc($res1);
			$defaultAdd = $row1['DefaultAddressId'];
		}
		
		$ret1 = array("error"=>"false", "defAdd"=>$defaultAdd, "addresses"=>$ret);
		echo json_encode($ret1);
	}
}
	
function addAddress() 
{	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$userId = $_SESSION['userId'];
	$receiver = trim(htmlspecialchars($_POST["receiver"]));
	$phonenum = trim(htmlspecialchars($_POST["phonenum"]));
	$address = trim(htmlspecialchars($_POST["address"]));
// 	$zipcode = $_POST["zipcode"];
	$isdefault = trim(htmlspecialchars($_POST["default"]));
	
	/*
		if ($isdefault) {
			echo $receiver . ' ' . $phonenum . ' ' . $address . ' ' . $zipcode . ' true ' . $isdefault;
		}
		else {
			echo $receiver . ' ' . $phonenum . ' ' . $address . ' ' . $zipcode . ' false ' . $isdefault;
		}
	*/
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	include "func.php";
	$msg = '';
	
	if (!isValidAddress($receiver, $phonenum, $address, $msg)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>$msg));
		return;
	}
	
	$bDefault = $isdefault != '0';
	
    $ret = addOneAddress($con, $userId, $receiver, $phonenum, $address, $bDefault, $msg);
    if (!$ret) {
	    echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>$msg));
	    return;
    }	
    
    echo json_encode(array('error'=>'false'));
}

function editAddress()
{	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	$userId = $_SESSION['userId'];
	$addId = trim(htmlspecialchars($_POST["addressid"]));
	$receiver = trim(htmlspecialchars($_POST["receiver"]));
	$phonenum = trim(htmlspecialchars($_POST["phonenum"]));
	$address = trim(htmlspecialchars($_POST["address"]));
// 	$zipcode = $_POST["zipcode"];
	$isdefault = trim(htmlspecialchars($_POST["default"]));
	
	if ($addId == -1) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的地址数据！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$result = mysql_query("select * from Address where UserId='$userId' and AddressId='$addId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的地址数据，请稍后重试！'));
		return;
	}
	else {
		mysql_query("update Address set Receiver='$receiver', PhoneNum='$phonenum', Address='$address' where UserId='$userId' and AddressId='$addId'");
		
		$bDefault = $isdefault != '0';
		if ($bDefault) {
			mysql_query("update User set DefaultAddressId='$addId' where UserId='$userId'");
		}
		echo json_encode(array('error'=>'false'));
	}
}

function deleteAddress()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	$userId = $_SESSION['userId'];
	$addId = trim(htmlspecialchars($_POST["addId"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$result = mysql_query("delete from Address where UserId='$userId' and AddressId='$addId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'删除失败，请稍后重试！'));
		return;
	}
	
	echo json_encode(array('error'=>'false', 'id'=>$addId));
	return;
}

function changeDefaultAddress()
{
	$ret = true;
	$error_msg = "";
	$defaultAdd = intval($_GET['defaultId']);
	if ($defaultAdd == 0) {
		$ret = false;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	session_start();
	$userId = $_SESSION['userId'];
	$result = mysql_query("select * from Address where UserId='$userId' and AddressId='$defaultAdd'");
	if (!$result) {
		$ret = false;
		$error_msg = mysql_error();
	}
	else {	
		$result = mysql_query("update User set DefaultAddressId='$defaultAdd' where UserId='$userId'");
		if (!$result) {
			$ret = false;
			$error_msg = mysql_error();
		}
	}
	
	if ($ret) {
		echo json_encode(array("error"=>"false"));
	}
	else {
		echo json_encode(array("error"=>"true","error_msg"=>$error_msg + $defaultAdd));
	}
	
}

?>