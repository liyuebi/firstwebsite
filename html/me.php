<?php

session_start();
$userId = $_SESSION["userId"];
$phone = $_SESSION["phonenum"];

include "../php/constant.php";
include "../php/database.php";

$charity = 0;
$con = connectToDB();
if ($con) {
	$res = mysql_query("select * from Credit where UserId='$userId'");
	if ($res && mysql_num_rows($res) > 0) {
		$row = mysql_fetch_assoc($res);
		$charity = $row["Charity"];
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>我的信息</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" type="text/css" href="../css/buttons.css" />
		
        <script src="../js/jquery-1.8.3.min.js" ></script>
        <script src="../js/scripts.js" ></script>        
		<script type="text/javascript">
			
			$(document).ready(function(){
				
				if (isNotLoginAndJump()) {
					return;
				}
			});
/*
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
*/
		</script>
	</head>
	
	<body>
		<h3 align="center" style="background-color: rgba(0, 0, 255, 0.32); height: 60px; line-height: 60px; font-size: 20; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;"><?php echo $_SESSION['nickname']; ?>（<?php echo "$phone" ?>）</h3>
		
		<a class="link_forward" href="editme.php">
 			<span>编辑资料</span>
		</a>
		<a class="link_forward" href="address.html">
 			<span>地址管理</span>
		</a>		
		<a class="link_forward" href="pwd.php">
 			<span>密码管理</span>
		</a>		
		<a class="link_forward" href="payment.php" style="border-bottom: 0">
 			<span>支付管理</span>
		</a>		
        
        <hr>
        
		<a class="link_forward" href="#">
 			<span>订单管理</span>
		</a>
		
		<a class="link_forward" href="record.php" style="border-bottom: 0">
 			<span>云量记录</span>
		</a>		
				
        <hr>        
        
        <p style="padding-left: 20px;">云粉慈善：献出爱心<b> <?php echo $charity; ?> </b>线上云量</p>
        
        <hr>
        
        <p style="padding-left: 20px;">客服微信：fslqt01</p>
        
        <hr>
        
		<input type="button" class="button button-glow button-border button-rounded button-primary" name="submit" style="width: 100%;" value="退出" onclick="logout()" />
        
		<div class="footer"> 
			<div>
				<ul class="nav nav-pills">
					<li style="display:table-cell; width:1%; float: none"><a style="text-align: center;" href="home.php">首页</a></li>
					<li style="display:table-cell; width:1%; float: none"><a style="text-align: center;" href="recommended.php">朋友</a></li>
					<li class="active" style="display:table-cell; width:1%; float: none"><a style="text-align: center;" href="#">个人中心</a></li>
				</ul>
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>