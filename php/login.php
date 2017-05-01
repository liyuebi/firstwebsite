<?php
	
/*
echo(__LINE__);
echo "<p>";
echo(__FILE__);
echo "<p>";
echo(PHP_VERSION);
echo "<p>";
echo(PHP_OS);
echo "<p>";
*/

include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("login" == $_POST['func']) {
	login();
}
else if ("logout" == $_POST['func']) {
	logout();	
}
else if ("loginAdmin" == $_POST['func']) {
	loginAdmin();	
}
else if ("setPayPwd" == $_POST['func']) {
	setPayPwd();
}
else if ("changePayPwd" == $_POST['func']) {
	changePayPwd();
}
else if ("changeLoginPwd" == $_POST['func']) {
	changeLoginPwd();
}
else if ("editprofile" == $_POST['func']) {
	editProfile();
}
else if ("getProfile" == $_POST['func']) {
	getProfile();
}

function login()
{	
	$phonenum = trim(htmlspecialchars($_POST['phonenum']));
	$password = trim(htmlspecialchars($_POST['password']));
		
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'连接失败，请稍后重试！'));
		return;
	}
	else 
	{		
		mysql_select_db("my_db", $con);
		$result = createUserTable();		
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'暂时不能登录，请稍后重试！'));	
			return;
		}
		else {
			$result = mysql_query("select * from User where PhoneNum='$phonenum' and Password='$password'");
			if (!$result) {
				echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找用户失败，请稍后重试！'));	
				return;
			}
			else if (0 == mysql_num_rows($result)) {
				echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'电话号码或验证码出错，请重新输入！'));	
				return;
			}
			else {
				$row = mysql_fetch_assoc($result);
				
				session_start();
				$_SESSION["userId"] = $row['UserId'];
				$_SESSION['phonenum'] = $phonenum;
				$_SESSION['name'] = $row['Name'];
				$_SESSION['password'] = $password;
				$_SESSION['buypwd'] = $row["PayPwd"];
				$_SESSION["idnum"] = $row['IDNum'];
				$_SESSION['isLogin'] = true;
				
				include "func.php";
				setUserCookie($row['Name'], $row['UserId']);
				
				// jump back to home page
// 				$home_url = '../html/home.php';
// 				header('Location: ' . $home_url);
			}
		}
	}
	
	echo json_encode(array('error'=>'false'));
	mysql_close($con);
}

function logout()
{
	session_start();
	$_SESSION['isLogin'] = false;
	
	include "func.php";
	deleteUserCookie();
}

function loginAdmin()
{	
	$name = trim(htmlspecialchars($_POST['user']));
	$password = trim(htmlspecialchars($_POST['pwd']));
	
	$con = connectToDB();
	if (!$con)
	{
		die("Could not connect: " . mysql_error());
	}
	else 
	{		
// 		echo("success<br>'");
		
		mysql_select_db("my_db", $con);
		$result = mysql_query("select * from Admin where Name='$name' and Password='$password'");
		if (!$result) {
			echo "Query Admin User failed<br>";
		}
		else if (0 == mysql_num_rows($result)) {
			echo "Admin User not exists!<br>";
		}
		else {
// 			echo "Admin User exists! <br>";
			$arr = array('isLogin'=>'true');
			echo json_encode($arr);
		}
		mysql_close($con);
	}
	
	exit;
}

function setPayPwd()
{
	$paypwd = trim(htmlspecialchars($_POST['pwd']));

	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if ($_SESSION["buypwd"] != null
		|| $_SESSION["buypwd"] != '') {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'支付密码已经设置过了！'));
		return;
	}

	include 'regtest.php';
	if (!isValidPayPwd($paypwd)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'格式错误！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		mysql_select_db("my_db", $con);
		$userid = $_SESSION["userId"];
		$result = mysql_query("update User set PayPwd='$paypwd' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'设置失败，请稍后重试！'));	
			return;
		}
		
		$_SESSION["buypwd"] = $paypwd;
		echo json_encode(array('error'=>'false'));
	}

	return;
}

function changePayPwd()
{
	$oripwd = trim(htmlspecialchars($_POST['ori']));
	$newpwd = trim(htmlspecialchars($_POST['new']));
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if ($oripwd != $_SESSION["buypwd"]) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的原支付密码有误！'));
		return;
	}
	
	include 'regtest.php';
	if (!isValidPayPwd($newpwd)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'新密码格式有误！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		mysql_select_db("my_db", $con);
		$userid = $_SESSION["userId"];
		$result = mysql_query("update User set PayPwd='$newpwd' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'设置失败，请稍后重试！'));	
			return;
		}
		
		$_SESSION["buypwd"] = $newpwd;
		echo json_encode(array('error'=>'false'));
	}

	return;
}

function changeLoginPwd()
{
	$oripwd = trim(htmlspecialchars($_POST['ori']));
	$newpwd = trim(htmlspecialchars($_POST['new']));
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	if ($oripwd != $_SESSION["password"]) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的原登录密码有误！'));
		return;
	}
	
	include 'regtest.php';
	if (!isValidLoginPwd($newpwd)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'新密码格式有误！'));
		return;
	}
	
	if ($newpwd == '000000') {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'不能使用初始默认密码作为新的密码，请重新输入！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		mysql_select_db("my_db", $con);
		$userid = $_SESSION["userId"];
		$result = mysql_query("update User set Password='$newpwd' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'设置失败，请稍后重试！'));	
			return;
		}
		
		$_SESSION["password"] = $newpwd;
		echo json_encode(array('error'=>'false'));
	}
	return;
}

function editProfile()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	$userid = $_SESSION["userId"];
	$oriName = $_SESSION["name"];
	$oriIdNum = $_SESSION["idnum"];
	
	$name = trim(htmlspecialchars($_POST['name']));
	$idNum = trim(htmlspecialchars($_POST['idnum']));
	
	if ($name == "") {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'姓名不能为空，请重新填写！'));
		return;
	}
	
	include 'regtest.php';
	if ($idNum != "" && !isValidIdNum($idNum)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'输入的身份证号无效，请重新填写！'));
		return;		
	}
	else if ($oriIdNum != "" && $idNum == "") {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'输入的身份证号不能为空！'));
		return;				
	}
	
	// 如果未做修改，认为正确，直接返回
	if ($name == $oriName && $idNum == $oriIdNum) {
		echo json_encode(array('error'=>'false'));
		return;						
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		mysql_select_db("my_db", $con);
		$result = mysql_query("update User set Name='$name', IDNum='$idNum' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'更新信息失败，请稍后重试！',"sql_error"=>mysql_error()));	
			return;
		}
		
		$_SESSION["name"] = $name;
		$_SESSION["idnum"] = $idNum;
	}
	
	// 如果是新用户修改信息，可能会让他同时添加地址，确认地址信息的对错
	if (isset($_POST['receiver'])) {
		$rece = trim(htmlspecialchars($_POST['receiver']));
		$rece_phone = trim(htmlspecialchars($_POST['rece_phone']));
		$rece_add = trim(htmlspecialchars($_POST['rece_add'])); 
		
		if ($rece != '' || $rece_phone != '' || $rece_add != '') {
		
			include "func.php";
			$str = '';
			
			if (!isValidAddress($rece, $rece_phone, $rece_add, $str)) {
				echo json_encode(array('error'=>'false','add_address'=>'failed','error_msg'=>'1',"1"=>$rece_phone,"2"=>$rece));
				return;
			}
	
			$ret = addOneAddress($con, $userid, $rece, $rece_phone, $rece_add, true, $str);
			if (!$ret) {
				echo json_encode(array('error'=>'false','add_address'=>'failed','error_msg'=>'2'));
			}
		}
	}
	
	echo json_encode(array('error'=>'false'));
	return;		
}

function getProfile()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$ident = trim(htmlspecialchars($_POST["iden"]));
// 	$found = false;
	
	include "regtest.php";
	if (!isValidNum($ident)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的账号或手机号无效！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		mysql_select_db("my_db", $con);
		
		if (isValidCellPhoneNum($ident)) {
			$res = mysql_query("select * from User where PhoneNum='$ident'");
			if ($res && mysql_num_rows($res) > 0) {
				$row = mysql_fetch_assoc($res);
				
				$uid = $row["UserId"];
				$num = $row["PhoneNum"];
				$name = $row["Name"];
				echo json_encode(array('error'=>'false','found'=>'true','user'=>array('id'=>$uid,'num'=>$num,'name'=>$name)));
				return;
			}
		}
		
		$res = mysql_query("select * from User where UserId='$ident'");
		if ($res && mysql_num_rows($res) > 0) {
			$row = mysql_fetch_assoc($res);
			
			$uid = $row["UserId"];
			$num = $row["PhoneNum"];
			$name = $row["Name"];
			echo json_encode(array('error'=>'false','found'=>'true','user'=>array('id'=>$uid,'num'=>$num,'name'=>$name)));
			return;
		}
	}
	
	echo json_encode(array('error'=>'false','found'=>'false'));
	return;
}

function check_table_is_exist($sql, $find_table)
{
	$row = mysql_query($sql);
	$database = array();
	$finddatabase=$find_table;
	while($result=mysql_fetch_array($row,MYSQL_ASSOC))
	{
		$database[]=$result['DataBase'];
	}
	unset($result,$row);
	if (in_array($find_table, $database))
	{
		return true;
	}
	else
	{
		return false;
	}
}
?>