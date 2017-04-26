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
	mysql_select_db("my_db", $con);
	
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
	if (!isset($_POST["submit"])) {
		exit("非法访问！<br>");
	}
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$userId = $_SESSION['userId'];
	$receiver = $_POST["receiver"];
	$phonenum = $_POST["phonenum"];
	$address = $_POST["address"];
	$zipcode = $_POST["zipcode"];
	// $isdefault = $_POST["default"];
	
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
	
	mysql_select_db("my_db", $con);
	$result = createAddressTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'创建地址表失败，请稍后重试！'));
		return;
	}
	else {
		$result = mysql_query("insert into Address (UserId, Receiver, PhoneNum, Address, ZipCode)
		 	VALUES('$userId', '$receiver', '$phonenum', '$address', '$zipcode')");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'插入地址失败，请稍后重试！'));
			return;
		}
		else {
			echo json_encode(array('error'=>'false'));
			$home_url = '../html/address.html';
			header('Location: ' . $home_url);
		}
	}
}

function editAddress()
{
	if (!isset($_POST["submit"])) {
		exit("非法访问！<br>");
	}
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if ($_POST["addressid"] == -1) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的地址数据！'));
		return;
	}

	$userId = $_SESSION['userId'];
	$addId = $_POST["addressid"];
	$receiver = $_POST["receiver"];
	$phonenum = $_POST["phonenum"];
	$address = $_POST["address"];
	$zipcode = $_POST["zipcode"];
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	mysql_select_db("my_db", $con);
	$result = mysql_query("select * from Address where UserId='$userId' and AddressId='$addId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找不到对应的地址数据，请稍后重试！'));
		return;
	}
	else {
		mysql_query("update Address set Receiver='$receiver', PhoneNum='$phonenum', Address='$address', ZipCode='$zipcode' where UserId='$userId' and AddressId='$addId'");
		
		echo json_encode(array('error'=>'false'));
		$home_url = '../html/address.html';
		header('Location: ' . $home_url);
	}
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

	mysql_select_db("my_db", $con);
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