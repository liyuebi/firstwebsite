<?php

session_start();
if (!isset($_COOKIE['isLogin']) || !$_COOKIE['isLogin']) {	
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>支付方式管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />

		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function setWechat()
			{
				var account = document.getElementById("wechat_input").value;
				account = $.trim(account);
				if (account.length <= 0) {
					alert("输入的账号无效，请重新输入！");
					document.getElementById("wechat_input").focus();
					return;
				}
				
				$.post("../php/payaccount.php", {"func":"setwechat","acc":account}, function(data){
					
					if (data.error == "false") {
						alert("设置成功！");	
					}
					else {
						alert("设置微信账号失败: " + data.error_msg);
						document.getElementById("wechat_input").focus();
						return;
					}
				}, "json");
			}
			
			function setAlipay()
			{
				var account = document.getElementById("alipay_input").value;
				
				$.post("../php/payaccount.php", {"func":"setalipay","acc":account}, function(data){
					
					if (data.error == "false") {
						alert("设置成功！");	
					}
					else {
						alert("设置支付宝账号失败: " + data.error_msg);
						document.getElementById("alipay_input").focus();
						return;
					}
				}, "json");
			}
			
			function setBankAccount()
			{
				var name = document.getElementById("bank_user").value;
				var account = document.getElementById("bank_account").value;
				var bank = document.getElementById("bank_name").value;
				var branch = document.getElementById("bank_branch").value;
				
				$.post("../php/payaccount.php", {"func":"setbank","pwd":pwd1}, function(data){
					
					if (data.error == "false") {
						alert("设置成功！");	
					}
					else {
						alert("设置失败: " + data.error_msg);
					}
				}, "json");
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
            <h3>支付方式管理</h3>
            <p>请选择您要使用的支付方式填写</p>
        </div>
        
        <div name="wechar">
	        <hr>
	        <h4>微信</h4>
	        <div id='wechat_unset'>
		        <p>请输入您的微信账号</p>
		        <input id='wechat_input' type="text" placeholder="请输入您的微信账号" />
		        <input type="button" value="确认" onclick="setWechat()" />
	        </div>
	        <div id='wechat_set'>
	        </div>
        </div>
        
        <div name="alipay">
	        <hr>
	        <h4>支付宝</h4>
	        <div id='alipay_unset'>
		        <p>请输入您的支付宝账号</p>
		        <input id='alipay_input' type="text" placeholder="请输入您的支付宝账号" />
		        <input type="button" value="确认" onclick="setAlipay()" />
	        </div>
	        <div id='alipay_set'>
	        </div>
	    </div>
        
        <div name="bank">
	        <hr>
	        <h4>银行账户</h4>
	        <div id='bank_unset'>
		        <p>请输入您的银行卡信息，推荐使用建设银行账户</p>
		        <input id='bank_user' type="text" placeholder="请输入您的账户姓名" />
		        <br>
		        <input id='bank_account' type="text" placeholder="请输入您的银行账号" />
		        <br>
		        <input id='bank_name' type="text" placeholder="请输入您的银行名，如建设银行／中国银行" />
		        <br>
		        <input id='bank_branch' type="text" placeholder="请输入您的开户支行" />
		        <br>
		        <input type="button" value="确认" onclick="setBankAccount()" />
	        </div>
	        <div id='bank_set'>
	        </div>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>