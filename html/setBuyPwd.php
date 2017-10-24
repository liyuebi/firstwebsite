<?php

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

// $userid = $_SESSION["userId"];
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>设置支付密码</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			function onConfirm()
			{
				var pwd1 = document.getElementById("pwd1").value;
				var pwd2 = document.getElementById("pwd2").value;
				if (pwd1 != pwd2) {
					alert("两次输入的密码不一致，请重新输入！");
					document.getElementById("pwd1").value = "";
					document.getElementById("pwd2").value = "";
				}
				else {
					if (!isPayPwdValid(pwd1)) {
						alert("无效的密码，请使用6-18位密码，且只包含字母和数字！");
						document.getElementById("pwd1").value = "";
						document.getElementById("pwd2").value = "";
					}
					else {
						pwd1 = md5(pwd1);
						$.post("../php/login.php", {"func":"setPayPwd","pwd":pwd1}, function(data){
							
							if (data.error == "false") {
								alert("设置成功！");	
								location.href = "home.php";
							}
							else {
								alert("设置失败: " + data.error_msg);
							}
						}, "json");
					}
				}
			}
			
			function goback() 
			{
				location.href = "pwd.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="display: table-cell; text-align: center; color: white">设置支付密码</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
        
        <div name="display">
	        <p>请使用6-18位字母和数字作为密码</p>
	        <input id="pwd1" type="password" class="form-control" style="width: 70%;" placeholder="请输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
	        <br>
	        <input id="pwd2" type="password" class="form-control" style="width: 70%;" placeholder="请再次输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
	        <br>
	        <input type="button" value="确认" class="button-rounded" style="width: 48%; height: 30px; float: left;" onclick="onConfirm()" />
	        <input type="button" value="取消" class="button-rounded" style="width: 48%; height: 30px; float: right;" onclick="javascript:history.back(-1);" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>