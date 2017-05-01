<?php

include "../php/constant.php";
include "../php/database.php";

session_start();
if (!$_SESSION["isLogin"]) {	
	$home_url = '../index.html';
	header('Location: ' . $home_url);
	exit();
}

$mycredit = 0;
$neededcredit = $refererConsumePoint;
$userid = $_SESSION["userId"];
$paypwd = $_SESSION["buypwd"];

$con = connectToDB();
if ($con) {
	$db_selected = mysql_select_db("my_db", $con);
	if ($db_selected) {
		$result = mysql_query("select * from Credit where UserId='$userid'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$mycredit = $row["Credits"];
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>推荐</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<!-- CSS -->
		<link rel="stylesheet" href="../css/mystyle.css">
		<link rel="stylesheet" href="../css/buttons.css">
	
		<script src="../js/jquery-1.8.3.min.js"></script>
        <script src="../js/scripts.js" ></script>	
		<script type="text/javascript">
			
			function onRegister()
			{
				var phonenum = document.getElementById("phonenum").value;
				var paypwd = document.getElementById("paypwd").value;
				if (!isPhoneNumValid(phonenum)) {
					alert("无效的电话号码！");
					return;
				}
				if (paypwd == "") {
					alert("无效的支付密码！");
					return;
				}
/*
				if (!isPayPwdValid(pwd1)) {
					alert("无效的密码，请使用6-12位密码，且只包含字母和数字！");
					document.getElementById("pwd1").value = "";
					document.getElementById("pwd2").value = "";
				}
				else {
*/
					$.post("../php/register.php", {"phonenum":phonenum, "paypwd":paypwd}, function(data){
						
						if (data.error == "false") {
							alert("注册成功！");	
							document.getElementById("phonenum").value = "";
							document.getElementById("Captcha").value = "";
							document.getElementById("paypwd").value = "";
						}
						else {
							alert("注册失败: " + data.error_msg);
						}
					}, "json");
// 				}
			}
			
			function getTestKey()
			{
				
			}
			
			function goSetPayPwd()
			{
				location.href = "setBuyPwd.php";
			}
			
			function goCharge()
			{
				location.href = "charge.php";
			}
			
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
		<h3>注册新用户</h3>
		
        <div>    
            <input type="text" class="form-control" id="phonenum" name="phonenum" placeholder="请输入新用户的电话号码" onkeypress="return onlyNumber(event)" />
            <br>
            <input type="Captcha" class="form-control" id="Captcha" name="Captcha" style="width: 70%; display: inline-block;" placeholder="请输入验证码！"/>
            <input type="button" class="button-rounded" name="test" onclick="getTestKey()" style="width: 28%; height: 30px;" value="获取验证码" ／>
			<br>
			<p style="margin-bottom: 0;">注册用户需要使用<?php echo $neededcredit;?>蜜券,您现在拥有蜜券数量为<strong><?php echo $mycredit; ?></strong></p>
			<?php
				if ($paypwd == "") {		
			?>
			<p style="margin-bottom: 0;">您的支付密码还没有设置</p>
			<input type="button" class="button-rounded" style="width: 45%; height: 30px; display: block; margin: 20px 0;" name="submit" value="设置支付密码！" onclick="goSetPayPwd()" />
			<?php
				}
				else if ($neededcredit > $mycredit) {
			?>
			<p>您的余额不足</p>
			<input type="button" class="button-rounded" style="width: 45%; height: 30px; display: block; margin: 20px 0;" name="submit" value="去充值" onclick="goCharge()" />
			<?php
				}
				else {
			?>
			<input type="password" class="form-control" id="paypwd" name="paypwd" placeholder="请输入您的支付密码！" />
			<input type="button" class="button-rounded" style="width: 45%; height: 30px; display: block; margin: 20px 0;" name="submit" value="注册" onclick="onRegister()" />
			<?php
				}
			?>
        </div>
        
        <div>
	        <h4 style="margin-bottom: 0;">注意事项：</h5>
		    <p style="margin: 0;">1. 用户默认登录密码被设置为000000，请用户登录后及时修改成新密码</p>
		    <p style="margin: 0;">2. 请用户登录后完善信息，并确认订单</p>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>