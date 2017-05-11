<?php

session_start();
$userId = $_SESSION["userId"];
$phone = $_SESSION["phonenum"];
$name = $_SESSION["name"];
$idnum = $_SESSION["idnum"]; 

include "../php/constant.php";
$lvlName = $levelName[$_SESSION['lvl'] - 1];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>我的信息</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
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
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
		<h3>我的资料</h3>
		
        <div>
            <table width="100%" align="center">
	            <tr>
		            <td>用户ID</td>
		            <td><?php echo $_SESSION['userId']; ?></td>
	            </tr>
	            <tr>
		            <td>用户等级</td>
		            <td><?php echo $lvlName; ?></td>
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
<!-- 	            <tr> -->
<!-- 		            <td></td> -->
<!-- 		            <td></td> -->
<!-- 	            </tr> -->
            </table>
        </div>
	        
	    <p class="navhref"><a href="editme.php">编辑资料</a></p>
        <p class="navhref"><a href="address.html">地址管理</a></p>
        <p class="navhref"><a href="pwd.php">密码管理</a></p>
    </body>
    <div style="text-align:center;">
    </div>
</html>