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

if ($_SESSION['pwdModiT'] == 0) {
	
	$url = 'jump.php?source=2';
	header('Location: ' . $url);
	exit;
}

$mycredit = 0;
$weAcc = '';
$isWechatSet = false;
$aliAcc = '';
$isAlipaySet = false;
$bankAcc = '';
$isBankSet = false;

include "../php/database.php";
include "../php/constant.php";
$con = connectToDB();
if ($con) {
	
	$userid = $_SESSION["userId"];
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$mycredit = $row["RegiToken"];
	}
	
	$res1 = mysql_query("select * from WechatAccount where UserId='$userid'");
	if ($res1) {
		if (mysql_num_rows($res1) > 0) {
			$row1 = mysql_fetch_assoc($res1);
			$weAcc = $row1["WechatAcc"];
			$isWechatSet = true;
		}
	}
	$res2 = mysql_query("select * from AlipayAccount where UserId='$userid'");
	if ($res2) {
		if (mysql_num_rows($res2) > 0) {
			$row2 = mysql_fetch_assoc($res2);
			$aliAcc = $row2["AlipayAcc"];
			$isAlipaySet = true;
		}
	}
/*
	$res3 = mysql_query("select * from BankAccount where UserId='$userid'");
	if ($res3) {
		if (mysql_num_rows($res3) > 0) {
			$row3 = mysql_fetch_assoc($res3);
			$bankAcc = $row3["AccName"] . " " . $row3["BankAcc"] . " " . $row3["BankName"] . " " . $row3["BankBranch"];
			$isBankSet = true;
		}
	}
*/
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
		<link rel="stylesheet" href="../css/buttons.css">
		
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
				
				var method = $("input[name='method']:checked").val();
				if (method != "1" && method != "2" && method != "3") {
					alert("还没有选择支付方式！");
					return;
				}
				
				if (amount % <?php echo $refererConsumePoint; ?> != 0) {
					alert("充值金额必须是" + <?php echo $refererConsumePoint; ?> + "的倍数！");
					return;
				}
				
				$.post("../php/credit.php", {"func":"recharge","amount":amount,"method":method}, function(data){
					
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
			
			function goToPayment()
			{
				location.href = "payment.php";
			}
			
			$(function() {
				$(":radio").click(function(){
					var val = $(this).val();
					if (val == "1") {
						document.getElementById("wechat_block").style.display = "block";
						document.getElementById("alipay_block").style.display = "none";
// 						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "2") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "block";
// 						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "3") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "none";
// 						document.getElementById("bank_block").style.display = "block";
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
            <h3>购买注册券</h3>
        </div>
        <div name="display" style="padding-bottom: 20px;">
	        <p>您可以选择以下方式支付，充值注册券比例为1:1，充值金额必须是<b> <?php echo $refererConsumePoint; ?> </b>的倍数</p>
	        <div>
		        <div style="float: left; width: 49%; border: 1px solid black;">
			        <p>微信：mifenggf</p>
			        <img src='../img/wechat.jpg' width="100%" />
		        </div>
		        <div style="float: right; width: 49%; border: 1px solid black;">
			        <p>支付宝：17379371413</p>
			        <img src='../img/alipay.jpg' width="100%" />
		        </div>
	<!--
		        <p>银行账号：</p>
		        <ol> 
			        <li>收款人: 李青 
			        <li>收款账号: 621467 2080000039339 
			        <li>所属行: 中国建设银行 
			        <li>开户分行: 江西省上饶市婺源县天佑支行
			    </ol>
	-->
	        </div>
	        <div style="clear: both; padding-top: 3px; ">
		        <hr>
		        <p>您现在拥有注册券：<?php echo $mycredit;?></p>
		        <input id="amount" class="form-control" type="text" placeholder="请输入购买数量！" onkeypress="return onlyNumber(event)" /> 
	        </div>
	        <div style="padding: 10px 0;">
		        <div>
			        <input type="radio" name="method" value="1" /> 微信
			        <input type="radio" name="method" value="2" /> 支付宝
	<!-- 		        <input type="radio" name="method" value="3" /> 银行转账 -->
		        </div>
		        <div id="wechat_block" style="border: 1px black solid; padding: 5px; display: none;">
			        <?php 
				        if ($isWechatSet) {
					?>
					<p>您的微信账号： <b><?php echo $weAcc; ?></b></p>
					<?php
						}
						else {
					?>
					<input type="button" value="设置微信账号" onclick="goToPayment()" />
					<?php
				        }
			        ?>
		        </div>
		        
		        <div id="alipay_block" style="border: 1px black solid; padding: 5px; display: none;">
			        <?php 
				        if ($isAlipaySet) {
					?>
					<p>您的微信账号： <b><?php echo $aliAcc; ?></b></p>
					<?php
						}
						else {
					?>
					<input type="button" value="设置支付宝账号" onclick="goToPayment()" />
					<?php
				        }
			        ?>
				</div>
	        
<!--
			<div id="bank_block" style="border: 1px black solid; padding: 5px; display: none;">
		        <?php 
			        if ($isBankSet) {
				?>
				<p>您的微信账号： <b><?php echo $bankAcc; ?></b></p>
				<?php
					}
					else {
				?>
				<input type="button" value="设置银行账号" onclick="goToPayment()" />
				<?php
			        }
		        ?>
			</div>
-->
	        </div>
	        
			<div style="padding: 10px 0;">        
		        <input type="button" value="提交申请" class="button-rounded" style="width: 48%; height: 30px; float: left;" onclick="onConfirm()" />
		        <input type="button" value="取消" class="button-rounded" style="width: 48%; height: 30px; float: right;" onclick="javascript:history.back(-1);" />
			</div>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>