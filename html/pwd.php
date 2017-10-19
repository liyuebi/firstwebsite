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

		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">

		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onBtnLoginPwdClicked()
			{
				location.href = "changeLoginPwd.html";	
			}
			
			function onBtnPayPwdClicked()
			{
				var value = document.getElementById("btnPayPwd").value;
				if (value == "修改支付密码") {
					location.href = "changeBuyPwd.html";
				}	
				else {
					location.href = "setBuyPwd.php";
				}
			}
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div>
            <h3>密码管理</h3>
        </div>
        
        <div name="display">
	        <input type="button" value="修改登录密码" class="button-rounded" style="width: 50%; height: 30px;" onclick="onBtnLoginPwdClicked()" />
	        <br>
	        <input type="button" value="<?php echo $text;?>" id="btnPayPwd" class="button-rounded" style="width: 50%; height: 30px; margin-top: 10px;" onclick="onBtnPayPwdClicked()" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>