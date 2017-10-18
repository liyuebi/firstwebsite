<?php

session_start();
$userId = $_SESSION["userId"];
$phone = $_SESSION["phonenum"];
$name = $_SESSION["name"];
$idnum = $_SESSION["idnum"]; 

include "../php/constant.php";
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
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
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
		<h3><?php echo $_SESSION['nickname']; ?>（<?php echo "$phone" ?>）</h3>
		
<!--
        <div>
            <table width="100%" align="center">
	            <tr>
		            <td>用户ID</td>
		            <td><?php echo $_SESSION['userId']; ?></td>
	            </tr>
	            <tr>
		            <td>昵称</td>
		            <td><?php echo $_SESSION['nickname']; ?></td>
	            </tr>       
	            <tr>
		            <td>手机号</td>
		            <td><?php echo "$phone" ?></td>
	            </tr>
	            <tr>
		            <td>姓名</td>
		            <td><?php echo "$name" ?></td>
	            </tr>
	            <tr>
		            <td>身份证号</td>
		            <td><?php echo "$idnum" ?></td>
	            </tr>
            </table>
        </div>
-->
	        
	    <p class="navhref"><a href="editme.php">编辑资料</a></p>
        <p class="navhref"><a href="address.html">地址管理</a></p>
        <p class="navhref"><a href="pwd.php">密码管理</a></p>
        <p class="navhref"><a href="payment.php">支付方式管理</a></p>
        
        <hr>
        
        客服微信：fslqt01
        
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