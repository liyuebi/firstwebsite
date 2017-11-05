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

$paypwd = $_SESSION["buypwd"];
$text = "设置支付密码";

if ($paypwd != "") {
	$text = "修改支付密码";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>密码管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />

		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onBtnLoginPwdClicked()
			{
				location.href = "changeLoginPwd.html";	
			}
			
			function onBtnPayPwdClicked()
			{
				var value = document.getElementById("btnPayPwd").innerHTML;
				if (value == "修改支付密码") {
					location.href = "changeBuyPwd.html";
				}	
				else {
					location.href = "setBuyPwd.php";
				}
				return false;
			}
			
			function goback()
			{
				location.href = "me.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">密码管理</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
        
		<a class="link_forward" href="changeLoginPwd.html">
 			<span>修改登录密码</span>
		</a>		
		<a class="link_forward" onclick="onBtnPayPwdClicked()" style="border-bottom: 0">
 			<span id="btnPayPwd"><?php echo $text;?></span>
		</a>
    </body>
    <div style="text-align:center;">
    </div>
</html>