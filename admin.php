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
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<title>蜜蜂工坊管理系统</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
<!-- 		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" /> -->
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/adminlte/AdminLTE.min.css" />
		
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
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
	<body class="hold-transition login-page">
<!--         <div align="center" style="margin: 30px;">     -->
            <div class="login-box">
				<div class="login-logo">
			    	<a href="#"><b>蜜蜂工坊后台管理系统</b></a>
				</div>
				<!-- /.login-logo -->
				<div class="login-box-body">
					<p class="login-box-msg">请登录</p>
			
					<div class="form-group has-feedback">
			        	<input type="email" id="user" class="form-control" placeholder="请输入管理员账号">
						<span class="glyphicon glyphicon-user form-control-feedback"></span>
			    	</div>
					<div class="form-group has-feedback">
						<input type="password" id="pwd" class="form-control" placeholder="请输入密码">
						<span class="glyphicon glyphicon-lock form-control-feedback"></span>
			    	</div>
					<div class="row">
				        <!-- /.col -->
				        <div class="col-xs-12">
					        <button type="button" class="btn btn-primary btn-block btn-flat" onclick="login()">登录</button>
				        </div>
				        <!-- /.col -->
			    	</div>
				</div>
	        </div>
<!--         </div> -->
    </body>
</html>