<?php

session_start();
$userId = $_SESSION["userId"];
$phone = $_SESSION["phonenum"];
$name = $_SESSION["name"];
$idnum = $_SESSION["idnum"]; 

$new = 0;
if (isset($_GET['new'])) {
	$new = $_GET['new'];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>编辑我的信息</title>
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

			function onSubmit()
			{
				var name = document.getElementById("name").value;
				var idNum = document.getElementById("idNum").value;
				
				if (name == "") {
					alert("姓名不能为空");
					document.getElementById("name").focus();
					return;
				}
				
				// id num can be empty, but if filled, check its validation
				if (idNum != "" && !isIDNumValid(idNum)) {
					alert("输入的身份证号无效，请重新输入！");
					document.getElementById("idNum").focus();
					return;
				}
				
				var oriName = document.getElementById("oriName").value;
				var oriIdNum = document.getElementById("oriIdNum").value;
				
				// 信息没有更改，退出
				if (name == oriName && idNum == oriIdNum) {
					history.back(-1);
					return;
				}
				else {
					$.post("../php/login.php", {"func":"editprofile","name":name,"idnum":idNum}, function(data){
						
						if (data.error == "false") {
							if (<?php echo $new; ?> != 0) {
								alert("设置成功，现在请设置购物密码！");	
								location.href = "changeBuyPwd.html?new=1";								
							}
							else {
								alert("设置成功！");	
								location.href = "me.php";
							}
						}
						else {
							alert("设置失败: " + data.error_msg);
						}
					}, "json");
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
            <h3>请更新资料</h3>
            <table width="100%" align="center">
	            <tr>
		            <td>手机号</td>
		            <td><?php echo "$phone" ?></td>
	            </tr>
	            <tr>
		            <td>姓名</td>
		            <td><input type="text" id="name" value="<?php echo "$name" ?>" /></td>
	            </tr>
	            <tr>
		            <td>身份证号</td>
		            <td><input type="text" id="idNum" value="<?php echo "$idnum" ?>" /></td>
	            </tr>
<!-- 	            <tr> -->
<!-- 		            <td></td> -->
<!-- 		            <td></td> -->
<!-- 	            </tr> -->
            </table>
            <input type="hidden" id="oriName" value="<?php echo "$name" ?>" />
            <input type="hidden" id="oriIdNum" value="<?php echo "$idnum" ?>" />
        </div>
	        
		<input type="button" value="保存" onclick="onSubmit()" />
		<input type="button" value="取消" onclick="javascript:history.back(-1);"/>
    </body>
    <div style="text-align:center;">
    </div>
</html>