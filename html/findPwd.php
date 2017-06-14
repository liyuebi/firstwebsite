<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>找回密码</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		
<!-- 	<script src="assets/js/scripts.js" ></script> -->
		<script type="text/javascript">
			function getVerificationCode()
			{
				// code to get verification code	
				document.getElementById("btnGet").disabled=true;
				document.getElementById("btnGet").var = 60;
				document.getElementById("btnGet").value="(" + 60 + ")" + "重新获取";
				
				setTimeout("countDown()", 1000);
			}
			
			function countDown()
			{
				var time = document.getElementById("btnGet").var;
				time -= 1;
				document.getElementById("btnGet").var = time;
				if (time > 0) {
					setTimeout("countDown()", 1000);
					document.getElementById("btnGet").value="(" + time + ")" + "再次获取";
				}
				else {
					document.getElementById("btnGet").disabled=false;	
					document.getElementById("btnGet").value = "再次获取";
				}
			}
		</script>
	</head>
	<body>
        <div>
            <h3>找回<?php if($_GET["type"] == "login") { echo "登录"; } else { echo "支付"; } ?>密码</h3>
        </div>
        <div name="display">
			<input type="text" placeholder="请输入验证码！" />
			<input id="btnGet" type="button" value="获取验证码" onclick="getVerificationCode()" />
			<input id="time" type="hidden" />
			<br>
			<input id="btnConfirm" type="button" value="确认" style="margin-top: 5px;" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>