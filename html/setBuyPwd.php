<?php

session_start();
if (!$_COOKIE['isLogin']) {	
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

$userid = $_SESSION["userId"];
$new = 0;
if (isset($_GET['new'])) {
	$new = $_GET['new'];
}

$bDefaultOrder = false;
if ($new) {
	include "../php/database.php";
	$con = connectToDB();
	if ($con)
	{
		$db_selected = mysql_select_db("my_db", $con);
		if ($db_selected) {
			$result = mysql_query("select * from Transcation where UserId='$userid'");
			if ($result) {
				include "../php/constant.php";
				while ($row = mysql_fetch_array($result)) {
					if ($row["Status"] == $OrderStatusDefault) {
						$bDefaultOrder = true;
						break;
					}
				}
			}
		}
	}
}

	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>设置支付密码</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
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
						alert("无效的密码，请使用6-12位密码，且只包含字母和数字！");
						document.getElementById("pwd1").value = "";
						document.getElementById("pwd2").value = "";
					}
					else {
						$.post("../php/login.php", {"func":"setPayPwd","pwd":pwd1}, function(data){
							
							if (data.error == "false") {
								if (<?php if ($bDefaultOrder) echo 1; else echo 0; ?>) {
									alert("支付密码设置成功，请处理未完成订单！");
									location.href = "order.php";
									return;
								}
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
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div>
            <h3>设置支付密码</h3>
        </div>
        
        <div name="display">
	        <p>请使用6-12位字母和数字作为密码</p>
	        <input id="pwd1" type="password" placeholder="请输入支付密码！" onkeypress="return onlyCharAndNum(event)"/>
	        <br>
	        <input id="pwd2" type="password" placeholder="请再次输入支付密码！" onkeypress="return onlyCharAndNum(event)"/>
	        <br>
	        <input type="button" value="确认" onclick="onConfirm()" />
	        <input type="button" value="取消" onclick="javascript:history.back(-1);" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>