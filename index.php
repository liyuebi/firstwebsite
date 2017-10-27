<?php

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	$home_url = 'html/home.php';
	header('Location: ' . $home_url);
	exit();
} 
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>连物网</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="css/mystyle.css" />
		<link rel="stylesheet" type="text/css" href="css/buttons.css" />
		
        <script src="js/jquery-1.8.3.min.js" ></script>
        <script src="js/scripts.js" ></script>
        <script src="js/md5.js" ></script>
		<script type="text/javascript">
			
			function submitCheck()
			{
				// check phone num
				var text = document.getElementById("phonenum").value;
				text = $.trim(text);
				var val = isPhoneNumValid(text);
				if (!val) {
					document.getElementById("phonenum").focus();
					return false;
				}
				
				// check secret code
				text = document.getElementById("password").value;
				text = $.trim(text);
				if (0 == text.length) {
					document.getElementById("password").focus();
					return false;
				}
				return true;
			}
			
			function tryLogin()
			{
				// check phone num
				var num = document.getElementById("phonenum").value;
				num = $.trim(num);
// 				var val = isPhoneNumValid(num);
// 				if (!val) {
				if (0 == num.length) {
					document.getElementById("phonenum").focus();
					alert("无效的手机号／昵称！");
					return false;
 				}
				
				// check secret code
				var pwd = document.getElementById("password").value;
				pwd = $.trim(pwd);
				if (0 == pwd.length) {
					document.getElementById("password").focus();
					alert("请输入密码!");
					return false;
				}
				pwd = md5(pwd);
				$.post("php/login.php", {"func":"login", "phonenum":num, "password":pwd}, function(data){
					
					if (data.error == "false") {
						location.href = "html/home.php";
					}
					else {
						alert("登录失败: " + data.error_msg);
					}
				}, "json");
				return true;

			}
		</script>
	</head>
	
	<body class='grey_body'>
		<div class="big_frame" style="padding: 20px; background: url(img/lian-bg.jpg); height: 600px; background-size: cover">
<!--
	        <div width="100%">
	            <img src="img/gongfang.jpg" width="100%" />
	        </div>
-->
	        
<!-- 	        <p align="center" style="font-size: 20px; margin: 2px;">会员登录</p> -->
	        
	        <div>
		        <p align="center" style="font-size: 20px; padding: 50px">
			        <img src="img/lian-logo.jpg" style="width: 96px; margin-bottom: -5px;  display: block"></img>
			        <span style="font-size: 20px; color: #3365e3;">连物网</span>
			    </p>
	        </div>
	        
	        <div> <!-- style="margin-top: 6%;" -->
	            <form method="post" action="php/login.php" onsubmit="return submitCheck();">
		            <input type="hidden" name="func" value="login" />
		            <input id="phonenum" class="form-control" name="phonenum"  placeholder="请输入您的手机号／昵称！" />
		            <!-- style="border-top-style: none; border-left-style: none; border-right-style: none; background-color: transparent" -->
		            <br>
	                <input id="password" class="form-control" type="password" name="password" class="password" placeholder="请输入您的用户密码！">
	                <br>
					<input type="button" class="button button-glow button-border button-rounded button-primary" name="submit" style="width: 100%;" value="登陆" onclick="tryLogin()" />
	            </form>
<!--
	            <div style="text-align: right; margin-top: 4%;" />
		            <a name="forget" style="margin-right: 5%;" href="html/findPwd.php?type=login">忘记密码</a>
	            </div>
	            <div style="text-align: center;">
		        	<p>客服微信：fslqt01</p>    
	            </div>
-->
	        </div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>