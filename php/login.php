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

include_once 'database.php';

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
else if ("logoutA" == $_POST['func']) {
	logoutAdmin();
}
else if ("updateN" == $_POST['func']) {
	updateIdxDisplayCntInAdmin();
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
else if ("initacc" == $_POST['func']) {
	initAccount();
}
else if ("editprofile" == $_POST['func']) {
	editProfile();
}
else if ("getProfile" == $_POST['func']) {
	getProfile();
}
else if ("switchAccount" == $_POST['func']) {
	switchAccount();
}
else if ("admChP" == $_POST['func']) {
	adminChangePwd();
}
else if ("admAddA" == $_POST['func']) {
	adminAddAccount();
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
		$result = createClientTable($con);		
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'暂时不能登录，请稍后重试！'));	
			return;
		}
		else {
			$bUseNickName = false;
			$result = mysqli_query($con, "select * from ClientTable where PhoneNum='$phonenum'");
			if (!$result || 0 == mysqli_num_rows($result)) {
				$result = mysqli_query($con, "select * from ClientTable where NickName='$phonenum'");				
				$bUseNickName = true;
			}
			
			if (!$result || 0 == mysqli_num_rows($result)) {
				echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'账号或密码出错，请重新输入！'));	
				return;		
			}
			else {
				if ($bUseNickName) {
					if (mysqli_num_rows($result) > 1) {
						echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'您使用的昵称别人也使用了，请使用手机号登录！'));	
						return;								
					}
				}
				
				$row = mysqli_fetch_assoc($result);
				if (!password_verify($password, $row["Password"])) {
					echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'账号或密码出错，请重新输入！'));	
					return;		
				}
				
				session_start();
				include "func.php";
				setSession($row);
				
				$userid = $row["UserId"];
				$now = time();
				mysqli_query($con, "update ClientTable set LastLoginTime='$now' where UserId='$userid'");
			}
		}
		
		include "creditTrade.php";
		updateUserExchangeOrder();
	}
	
	echo json_encode(array('error'=>'false'));
	mysqli_close($con);
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
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'连接失败，请稍后重试！'));
		return;
	}
	else 
	{		
		createAdminTable($con);
		
		$result = mysqli_query($con, "select * from AdminTable where Name='$name'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'账号或密码出错，请重新输入！'));	
			return;		
		}
		else if (0 == mysqli_num_rows($result)) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'账号或密码出错，请重新输入！'));	
			return;		
		}
		
		$row = mysqli_fetch_assoc($result);
		if (!password_verify($password, $row["Password"])) {
			echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'账号或密码出错，请重新输入！'));	
			return;		
		}
		
		include "admin_func.php";
		session_start();
		setAdminSession($row);
		
		$userid = $row["AdminId"];
		$now = time();
		mysqli_query($con, "update AdminTable set LastLoginTime='$now' where AdminId='$userid'");

		getIndexDisplayCnt($con);
	}
	
	$arr = array('error'=>'false');
	echo json_encode($arr);
}

function logoutAdmin()
{	
	session_start();
	$_SESSION['adminLogin'] = false;

	include "admin_func.php";
	deleteAdminCookie();
}

function updateIdxDisplayCntInAdmin()
{
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'连接失败，请稍后重试！'));
		return;
	}
	else {
		include "admin_func.php";
		getIndexDisplayCnt($con);
	}
	echo json_encode(array('error'=>'false'));
}

function adminChangePwd()
{
	session_start();
	
	include_once "admin_func.php";
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$oldpwd = trim(htmlspecialchars($_POST["opd"]));
	$newpwd = trim(htmlspecialchars($_POST["npd"]));
	
	$adminUid = $_SESSION['adminUid'];
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysqli_query($con, "select * from AdminTable where AdminId='$adminUid'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'您的账号有问题，请稍后重试！'));
		return;		
	}
	$row = mysqli_fetch_assoc($res);
	$pwd = $row["Password"];
	
	if (!password_verify($oldpwd, $pwd)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'原密码输入错误，请重新输入！'));	
		return;		
	}
	
	$pwd = password_hash($newpwd, PASSWORD_DEFAULT);
	$res1 = mysqli_query($con, "update AdminTable set Password='$pwd' where AdminId='$adminUid'");
	if (!$res1) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'修改密码失败，请稍后重试！'));
		return;
	}
	
	$_SESSION['pwd'] = $pwd;
	echo json_encode(array('error'=>'false'));
}

function adminAddAccount()
{
	session_start();
	
	include_once "admin_func.php";
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$account = trim(htmlspecialchars($_POST["acc"]));
	$pwd = trim(htmlspecialchars($_POST["pd"]));
	
	// 检查权限

	if ($account == "") {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'账户不能为空！'));
		return;		
	}	
	if ($pwd == "") {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'密码不能为空！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$pwd = password_hash($pwd, PASSWORD_DEFAULT);
	$res = mysqli_query($con, "insert into AdminTable (Name, Password, Priority)
							values('$account', '$pwd', '6')");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'添加账户失败，请稍后重试！'));
		return;		
	}
	
	echo json_encode(array('error'=>'false'));
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
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$userid = $_SESSION["userId"];
		$paypwd = password_hash($paypwd, PASSWORD_DEFAULT);
		$result = mysqli_query($con, "update ClientTable set PayPwd='$paypwd' where UserId='$userid'");
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
	
	if (!password_verify($oripwd, $_SESSION["buypwd"])) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的原支付密码有误！'));
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
		$userid = $_SESSION["userId"];
		$newpwd = password_hash($newpwd, PASSWORD_DEFAULT);
		$now = time();
		$result = mysqli_query($con, "update ClientTable set PayPwd='$newpwd', LastPPwdModiTime='$now' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'设置失败，请稍后重试！'));	
			return;
		}
		
		$_SESSION["buypwd"] = $newpwd;
		$_SESSION['ppwdModiT'] = $now;
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

	if (!password_verify($oripwd, $_SESSION["password"])) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'输入的原登录密码有误！'));
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
		$userid = $_SESSION["userId"];
		$newpwd = password_hash($newpwd, PASSWORD_DEFAULT);
		$now = time();
		$result = mysqli_query($con, "update ClientTable set Password='$newpwd', LastPwdModiTime='$now' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'设置失败，请稍后重试！'));	
			return;
		}
		
		$_SESSION["password"] = $newpwd;
		$_SESSION['pwdModiT'] = $now;
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
	$oriNickName = $_SESSION["nickname"];
// 	$oriName = $_SESSION["name"];
// 	$oriIdNum = $_SESSION["idnum"];
	
// 	$name = trim(htmlspecialchars($_POST['name']));
// 	$idNum = trim(htmlspecialchars($_POST['idnum']));
	$nickname = trim(htmlspecialchars($_POST['nickname']));
	
	include 'regtest.php';
	if (strlen($nickname) < 2) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的昵称格式，请重新填写！'));
		return;		
	}
	
/*
	if ($name == "") {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'姓名不能为空，请重新填写！'));
		return;
	}
	
	if ($idNum != "" && !isValidIdNum($idNum)) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'输入的身份证号无效，请重新填写！'));
		return;		
	}
	else if ($oriIdNum != "" && $idNum == "") {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'输入的身份证号不能为空！'));
		return;				
	}
*/
	
	// 如果未做修改，认为正确，直接返回
	if (/* $name == $oriName && $idNum == $oriIdNum && */ $nickname == $oriNickName) {
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
		$result = mysqli_query($con, "select * from ClientTable where NickName='$nickname' && UserId!='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'更新信息失败，请稍后重试！',"sql_error"=>mysqli_error($con)));	
			return;
		}
		else if (mysqli_num_rows($result) > 0) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'你输入的昵称已有人使用，请重新输入！',"sql_error"=>mysqli_error($con)));	
			return;			
		}
				
		$result = mysqli_query("update ClientTable set NickName='$nickname' where UserId='$userid'");
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'35','error_msg'=>'更新信息失败，请稍后重试！',"sql_error"=>mysqli_error($con)));	
			return;
		}
		
		$_SESSION['nickname'] = $nickname;
// 		$_SESSION["name"] = $name;
// 		$_SESSION["idnum"] = $idNum;
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
	
			$newAddressId = 0;
			$ret = addOneAddress($con, $userid, $rece, $rece_phone, $rece_add, true, $newAddressId, $str);
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
		if (isValidCellPhoneNum($ident)) {
			$res = mysqli_query($con, "select * from ClientTable where PhoneNum='$ident'");
			if ($res && mysqli_num_rows($res) > 0) {
				$row = mysqli_fetch_assoc($res);
				
				$uid = $row["UserId"];
				$num = $row["PhoneNum"];
				$name = $row["Name"];
				echo json_encode(array('error'=>'false','found'=>'true','user'=>array('id'=>$uid,'num'=>$num,'name'=>$name)));
				return;
			}
		}
		
		$res = mysqli_query($con, "select * from ClientTable where UserId='$ident'");
		if ($res && mysqli_num_rows($res) > 0) {
			$row = mysqli_fetch_assoc($res);
			
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

function initAccount()
{
	include 'regtest.php';
	include 'func.php';
	include "constant.php";
		
	$nickname = trim(htmlspecialchars($_POST["nickname"]));
	$loginPwd = trim(htmlspecialchars($_POST["pwd"]));
	$paypwd = trim(htmlspecialchars($_POST["ppwd"]));
	$receiver = trim(htmlspecialchars($_POST["receiver"]));
	$phonenum = trim(htmlspecialchars($_POST["phonenum"]));
	$address = trim(htmlspecialchars($_POST["address"]));

	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	if (strlen($nickname) < 2) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的昵称格式，请重新填写！'));
		return;		
	}
	
	$msg = '';
	if (!isValidAddress($receiver, $phonenum, $address, $msg)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>$msg));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$userid = $_SESSION["userId"];
	
	$result = mysqli_query("select * from ClientTable where NickName='$nickname' && UserId!='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'更新信息失败，请稍后重试！',"sql_error"=>mysqli_error($con)));	
		return;
	}
	else if (mysqli_num_rows($result) > 0) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'你输入的昵称已有人使用，请重新输入！',"sql_error"=>mysqli_error($con)));	
		return;			
	}

	
	$newAddressId = 0;
    $ret = addOneAddress($con, $userid, $receiver, $phonenum, $address, false, $newAddressId, $msg);
    if (!$ret) {
	    echo json_encode(array('error'=>'true','error_code'=>'33','error_msg'=>$msg));
	    return;
    }	

	$time = time();
	$loginPwd = password_hash($loginPwd, PASSWORD_DEFAULT);
	$paypwd = password_hash($paypwd, PASSWORD_DEFAULT);
	$res = mysqli_query($con, "update ClientTable set NickName='$nickname', Password='$loginPwd', PayPwd='$paypwd', DefaultAddressId='$newAddressId', AccInited='1', LastPwdModiTime='$time', LastPPwdModiTime='$time' where UserId='$userid'");
	if (!$res) {
	    echo json_encode(array('error'=>'true','error_code'=>'34','error_msg'=>'初始化账号出错！'));
	    return;	
	}
	
	$_SESSION['nickname'] = $nickname;
	$_SESSION['password'] = $loginPwd;
	$_SESSION['buypwd'] = $paypwd;
	$_SESSION['pwdModiT'] = $time;
	$_SESSION['ppwdModiT'] = $time;
	$_SESSION['accInited'] = 1;
	
	$res1 = mysqli_query($con, "select * from Transaction where UserId='$userid' and Type='1' and Status='$OrderStatusDefault'");
	if (!$res1 || mysqli_num_rows($res1) <= 0) {
		// !!! log error
	}
	else {
		$row1 = mysqli_fetch_assoc($res1);
		$orderId = $row1["OrderId"];
		$res2 = mysqli_query($con, "update Transaction set AddressId='$newAddressId', Receiver='$receiver', PhoneNum='$phonenum', Address='$address', Status='$OrderStatusBuy', ConfirmTime='$time' where OrderId='$orderId'");
		if (!$res2) {
			// !!! log error
		}
	}
	
	echo json_encode(array('error'=>'false'));
}

function switchAccount()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$userid = $_SESSION["userId"];
	$toUserId = trim(htmlspecialchars($_POST["to"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysqli_query($con, "select * from ClientTable where UserId='$toUserId'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'要切换的账户异常，请稍后重试！'));
		return;
	}
	$row = mysqli_fetch_assoc($res);
	
	include "func.php";
	setSession($row);
	
	echo json_encode(array('error'=>'false'));
	return;
}

function check_table_is_exist($con, $sql, $find_table)
{
	$row = mysqli_query($con, $sql);
	$database = array();
	$finddatabase=$find_table;
	while($result=mysqli_fetch_array($row,MYSQLI_ASSOC))
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