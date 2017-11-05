<?php

include "../php/constant.php";
include "../php/database.php";

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	// no code here, just continue;		
} 
else {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

$mycredit = 0;
$neededcredit = $refererConsumePoint;
$userid = $_SESSION["userId"];
$paypwd = $_SESSION["buypwd"];

$con = connectToDB();
if ($con) {
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$mycredit = $row["Credits"];
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
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" href="../css/mystyle.css">
		<link rel="stylesheet" href="../css/buttons.css">
	
		<script src="../js/jquery-1.8.3.min.js"></script>
        <script src="../js/scripts.js" ></script>	
        <script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			function onRegister()
			{
				var phonenum = document.getElementById("phonenum").value;
				var num = document.getElementById("investnum").value;
				var paypwd = document.getElementById("paypwd").value;
				phonenum=$.trim(phonenum);
				num=$.trim(num);
				if (!isPhoneNumValid(phonenum)) {
					alert("无效的电话号码！");
					return;
				}
				if (paypwd == "") {
					alert("无效的支付密码！");
					return;
				}

				paypwd = md5(paypwd);
				$.post("../php/register.php", {"phonenum":phonenum, "quantity":num, "paypwd":paypwd}, function(data){
					
					if (data.error == "false") {
						alert("注册成功！");// \n新用户的ids是" + data.new_user_id);	
						document.getElementById("phonenum").value = "";
						document.getElementById("investnum").value = "";
						document.getElementById("paypwd").value = "";
					}
					else {
						alert("注册失败: " + data.error_msg);
					}
				}, "json");
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
				location.href = "exchange.php";
			}
			
			function goback()
			{
				location.href = "home.php";
			}
			
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">分享云粉</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
        <div style="margin-top: 5px;">    
            <input type="text" class="form-control" id="phonenum" name="phonenum" placeholder="请输入新用户的电话号码" onkeypress="return onlyNumber(event)" />
            <br>
            <p style="margin-bottom: 0;">注册用户可存储线上云量资产（必须是100的倍数）</b>，您的剩余线上云量为 <strong><?php echo $mycredit; ?></strong></p>
            <input type="text" class="form-control" id="investnum" name="investnum" placeholder="请输入存储数额" onkeypress="return onlyNumber(event)" />
<!--             <input type="Captcha" class="form-control" id="Captcha" name="Captcha" style="width: 70%; display: inline-block;" placeholder="请输入验证码！"/> -->
<!--             <input type="button" class="button-rounded" name="test" onclick="getTestKey()" style="width: 28%; height: 30px;" value="获取验证码" ／> -->
<!-- 			<br> -->
			<?php
				if ($paypwd == "") {		
			?>
			<p style="margin-bottom: 0;">您的支付密码还没有设置</p>
			<input type="button" class="button-rounded" style="width: 45%; height: 30px; display: block; margin: 20px 0;" name="submit" value="设置支付密码！" onclick="goSetPayPwd()" />
			<?php
				}
				else if ($neededcredit > $mycredit) {
			?>
			<p>您的注册券余额不足</p>
			<input type="button" class="button-rounded" style="width: 45%; height: 30px; display: block; margin: 20px 0;" name="submit" value="前往云量交易" onclick="goCharge()" />
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
		    <p style="margin: 0;">2. 请用户登录后完善信息</p>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>