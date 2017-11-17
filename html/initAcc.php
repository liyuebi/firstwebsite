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

if ($_SESSION['accInited'] > 0) {
	$home_url = 'home.php';
	header('Location: ' . $home_url);
	exit();	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>完善个人信息</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle1.0.1.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){
			});
			

			function onSubmit()
			{				
				var nickname = document.getElementById("nickname").value;
				if (nickname.length < 2) {
					alert("无效的昵称，请至少使用2个文字或字母或数字！");
					document.getElementById("nickname").focus();
					return;					
				} 
				
				var pwd1 = document.getElementById("pwd2").value;
				var pwd2 = document.getElementById("pwd3").value;
				if (pwd1 != pwd2) {
					alert("两次输入的登录密码不一致，请重新输入！");
					document.getElementById("pwd2").value = "";
					document.getElementById("pwd3").value = "";
					return;
				}				
				if (pwd1 == "000000") {
					alert("请不要使用默认密码作为您新的登录密码！");
					document.getElementById("pwd2").value = "";
					document.getElementById("pwd3").value = "";
					return;
				}

				var ppwd1 = document.getElementById("ppwd1").value;
				var ppwd2 = document.getElementById("ppwd2").value;
				if (ppwd1 != ppwd2) {
					alert("两次输入的支付密码不一致，请重新输入！");
					document.getElementById("ppwd1").value = "";
					document.getElementById("ppwd2").value = "";
					return;
				}
				if (!isPayPwdValid(ppwd1)) {
					alert("无效的支付密码，请使用6-18位密码，且只包含字母和数字！");
					document.getElementById("ppwd1").value = "";
					document.getElementById("ppwd2").value = "";
					return;
				}
				
				// check phone num
				var text = document.getElementById("phonenum").value;
				text = $.trim(text);
				var val = isPhoneNumValid(text);
				if (!val) {
					document.getElementById("phonenum").focus();
					alert("无效的收件人号码，请重新输入!");
					return;
				}
				
				// check address, assume length can't be less than 8 chars
				var add = document.getElementById("address").value;
				add = $.trim(add);
				if (add.length < 6) {
					document.getElementById("address").focus();
					alert("无效的地址，请重新输入!");
					return;
				}
				
				// check name
				var name = document.getElementById("receiver").value;
				name = $.trim(name);
				if (name == '') {
					document.getElementById("receiver").focus();
					alert("无效的收件人姓名，请重新输入!");
					return;
				}
				
				pwd1 = md5(pwd1);
				ppwd1 = md5(ppwd1);
				var data = {"func":"initacc","nickname":nickname,"pwd":pwd1,"ppwd":ppwd1,"receiver":name,"phonenum":text,"address":add};
				$.post("../php/login.php", data, function(data){
					
					if (data.error == "false") {						
						alert("账号设置成功！");	
						location.href = "home.php";								
					}
					else {
						alert("设置失败: " + data.error_msg);
					}
				}, "json");
			}

		</script>
	</head>
	<body>
		<h3 align="center" style="background-color: rgba(0, 0, 255, 0.32); height: 50px; line-height: 50px; font-size: 20; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">完善个人信息</h3>
		<div style="margin: 0 3px;">
			<div>
				<h4 class="text-info">个人信息：</h4>
				<input type="text" class="form-control" style="width: 70%;" id="nickname" placeholder="请输入昵称！" value="<?php echo $_SESSION["nickname"];  ?>" onkeypress="return onlyCharAndNum(event)" />
			</div>
			
			<hr>
			
			<div>
				<h4 class="text-info">修改登录密码：</h4>
				<p>请使用6-12位字母和数字作为密码</p>
		        <input type="password" id="pwd2" name="" class="form-control" style="width: 70%;" placeholder="请输入新的登录密码！" onkeypress="return onlyCharAndNum(event)"/>
		        <br>
		        <input type="password" id="pwd3" name="" class="form-control" style="width: 70%;" placeholder="请再次输入新的登录密码！" onkeypress="return onlyCharAndNum(event)"/>
			</div>
			
			<hr>
			
			<div>
				<h4 class="text-info">设置支付密码：</h4>
		        <p>请使用6-18位字母和数字作为密码</p>
		        <input id="ppwd1" type="password" class="form-control" style="width: 70%;" placeholder="请输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
		        <br>
		        <input id="ppwd2" type="password" class="form-control" style="width: 70%;" placeholder="请再次输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
			</div>
			
			<hr>
			
	        <div>
		        <h4 class="text-info">默认地址信息：</h4>
	            <div class="form-horizontal">
		            <div class="form-group">
						<label for="receiver" class="col-sm-2 control-label">收件人</label>
						<div class="col-sm-10">
							<input type="email" class="form-control" id="receiver" placeholder="请填入收件人姓名">
    					</div>
					</div>
		            <div class="form-group">
						<label for="phonenum" class="col-sm-2 control-label">收件人电话</label>
						<div class="col-sm-10">
							<input type="email" class="form-control" id="phonenum" placeholder="请填入收件人电话" onkeypress="return onlyNumber(event)">
    					</div>
					</div>
		            <div class="form-group">
						<label for="address" class="col-sm-2 control-label">收件人地址</label>
						<div class="col-sm-10">
							<input type="email" class="form-control" id="address" placeholder="请填入收件人地址">
    					</div>
					</div>
	            </div>
	            
	            
	        </div>
	        
	         <hr>
	         <input type="button" value="保存" class="btn btn-success btn-block" style="margin-bottom: 10px;" onclick="onSubmit()" />
<!-- 	        <input id="btnSave" type="button" name="submit" value="保存" onclick="submitAddress()"/> -->
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>