<?php

// if not login, jump to index page
if (!isset($_COOKIE['isLogin']) || !$_COOKIE['isLogin']) {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

session_start();
if ($_SESSION['password'] == '000000') {
	
	$url = 'jump.php?source=2';
	header('Location: ' . $url);
	exit;
}

$mycredit = 0;

include "../php/database.php";
$con = connectToDB();
if ($con) {
	$db_selected = mysql_select_db("my_db", $con);
	if ($db_selected) {
		$userid = $_SESSION["userId"];
		$result = mysql_query("select * from Credit where UserId='$userid'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$mycredit = $row["Credits"];
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>充值</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function onConfirm()
			{
				var amount = document.getElementById("amount").value;
				
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amount);
				if (!amountReg.test(amount)) {
					alert("无效的金额，请重新输入！");
					document.getElementById("amount").value = "";
					document.getElementById("amount").focus();
					return;
				}
				
				$.post("../php/credit.php", {"func":"recharge","amount":amount}, function(data){
					
					if (data.error == "false") {
						alert("申请成功！");	
						location.href = "home.php";
					}
					else {
						alert("申请失败: " + data.error_msg);
						document.getElementById("amount").value = "";
					}
				}, "json");
			}
			
			$(function() {
				$(":radio").click(function(){
					var val = $(this).val();
					if (val == "1") {
						document.getElementById("wechat_block").style.display = "inline";
						document.getElementById("alipay_block").style.display = "none";
						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "2") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "inline";
						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "3") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "none";
						document.getElementById("bank_block").style.display = "inline";
					}
				});
			});
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
        <div>
            <h3>购买蜜券</h3>
        </div>
        <div name="display">
	        <p>您现在拥有蜜券：<?php echo $mycredit;?></p>
	        <input id="amount" class="form-control" type="text" placeholder="请输入购买数量！" onkeypress="return onlyNumber(event)" /> 
<!--
	        <br>
		        <input type="radio" name="method" value="1" checked="true"/> 微信
	        <div id="wechat_block" style="border: 1px black solid; padding: 5px;">
		        <p>请输入您的微信账号:</p>
		        <input id="wechat_account" type="text" placeholder="请输入微信账号" />
	        </div>
	        
	        <input type="radio" name="method" value="2" /> 支付宝
	        <div id="alipay_block" style="border: 1px black solid; padding: 5px;">
		        <p>请输入您的支付宝账号：</p>
		        <input id="alipay_account" type="text" placeholder="请输入支付宝账号" />
	        </div>
	        
	        <input type="radio" name="method" value="3" /> 银行转账
			<div id="bank_block" style="border: 1px black solid; padding: 5px;">
				<p>请输入您的银行账户：</p>
				<input id="bank_account" type="text" placeholder="请输入银行账号" />
				<p>请输入所属银行</p>
				<input id="bank_name" type="text" placeholder="请输入卡号所属的银行，如：中国银行" />
			</div>
-->
	        
	        <input type="button" value="提交申请" onclick="onConfirm()" />
	        <input type="button" value="取消" onclick="javascript:history.back(-1);" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>