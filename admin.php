<?php

if (isset($_COOKIE['adminLogin']) && $_COOKIE['adminLogin']) {	
	$home_url = 'admin/adminhome.php';
	header('Location: ' . $home_url);
}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>蜜蜂工坊管理系统</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="js/md5.js" ></script>
		<script type="text/javascript">
			function login()
			{
				var user = document.getElementById("user").value;
				var pwd = document.getElementById("pwd").value;
				pwd = $.trim(pwd);
				pwd = md5(pwd);
				var data = "func=loginAdmin,user=" + user +",pwd="+pwd;
				$.post("php/login.php", {"func":"loginAdmin","user":user,"pwd":pwd}, function(data){
					if (data.error == "false") {
						location.href = "admin/adminhome.php";
					}
					else {
						alert('登录失败：' + data.error_msg);
					}
				}, "json");
			}
		</script>
	</head>
	<body>
        <div align="center" style="margin: 30px;">
	        <h2>蜜蜂工坊后台管理系统</h2>
	        <table>
		        <tr>
			        <td>用户名</td>
			        <td><input id="user" type="text" placeholder="请输入管理员账号" /></td>
		        </tr>
		        <tr>
			        <td>密码</td>
			        <td><input id="pwd" type="password" placeholder="请输入密码" /></td>
		        </tr>
	        </table>
            <input type="button" value="登录" onclick="login()" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>