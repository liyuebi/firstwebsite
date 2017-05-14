<?php

// if not login, jump to index page
if (!isset($_COOKIE['isLogin']) || !$_COOKIE['isLogin']) {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

session_start();
if ($_SESSION['buypwd'] == '') {
	
	$url = 'jump.php?source=3';
	header('Location: ' . $url);
	exit;
}

$mycredit = 0;
$dayWithdraw = 0;
$applyCount = 0;

include "../php/constant.php";
$leastCredit = $withdrawFloorAmount;
$mostCredit = $withdrawCeilAmountOneDay;
$handlefee = $withdrawHandleRate;
$weAcc = '';
$isWechatSet = false;
$aliAcc = '';
$isAlipaySet = false;
$bankAcc = '';
$isBankSet = false;

include "../php/database.php";
$con = connectToDB();
if ($con) {
	$userid = $_SESSION["userId"];
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$mycredit = $row["Credits"];
		
		$dayWd = $row["DayWithdraw"];
		$lastWd = $row["LastWithdrawTime"];
		$now = time();
		if (isInTheSameDay($now, $dayWd)) {
			$dayWithdraw = $dayWd;
		}
		
		$res1 = mysql_query("select * from WithdrawApplication where UserId='$userid'");
		if ($res1) {
			while ($row1 = mysql_fetch_array($res1)) {
				if (isInTheSameDay($now, $row1["ApplyTime"])) {
					$applyCount += $row1["ApplyAmount"];
				}
			}
		}
		
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
	$res3 = mysql_query("select * from BankAccount where UserId='$userid'");
	if ($res3) {
		if (mysql_num_rows($res3) > 0) {
			$row3 = mysql_fetch_assoc($res3);
			$bankAcc = $row3["AccName"] . " " . $row3["BankAcc"] . " " . $row3["BankName"] . " " . $row3["BankBranch"];
			$isBankSet = true;
		}
	}
}
$mostCredit = max(0, $mostCredit - $dayWithdraw - $applyCount);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>取现</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			function onConfirm()
			{
				var amount = document.getElementById("amount").value;
				var paypwd = document.getElementById("pwd").value;
				
				var method = $("input[name='method']:checked").val();
				if (method != "1" && method != "2" && method != "3") {
					alert("还没有选择支付方式！");
					return;
				}
				
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amount);
				if (!amountReg.test(amount)) {
					alert("无效的金额，请重新输入！");
					document.getElementById("amount").value = "";
					document.getElementById("amount").focus();
					return;
				}
				
				var least = <?php echo $leastCredit; ?>;
				if (amount < least) {
					alert("每次提取至少" + least + "蜜券,请重新输入！");
					document.getElementById("autual_count").innerHTML = "0";
					document.getElementById("amount").value = "";
					document.getElementById("amount").focus();
					return;
				}
				
				var most = <?php echo $mostCredit; ?>;
				if (amount > most) {
					alert("今天剩余的提现申请额度为" + most + "蜜券，请重新输入！");
					document.getElementById("autual_count").innerHTML = "0";
					document.getElementById("amount").value = "";
					document.getElementById("amount").focus();
					return;
				}
				
				paypwd = md5(paypwd);
				$.post("../php/credit.php", {"func":"withdraw","amount":amount,"paypwd":paypwd,"method":method}, function(data){
					
					if (data.error == "false") {
						alert("申请提交成功！");	
						location.href = "home.php";
					}
					else {
						alert("申请提交失败: " + data.error_msg);
// 						document.getElementById("amount").value = "";
					}
				}, "json");

			}
			
			function calcActualNum()
			{
				var amount = document.getElementById("amount").value;
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amount);
				if (!amountReg.test(amount)) {
					document.getElementById("autual_count").innerHTML = "0";
					return;
				}
				
				var least = <?php echo $leastCredit; ?>;
				if (amount < least) {
					alert("每次提取至少" + least + "蜜券！");
					document.getElementById("autual_count").innerHTML = "0";
					return;
				}
				
				var actualCount = 0;
				var rate = <?php echo $handlefee; ?>;
				actualCount = amount - Math.floor(amount * rate);
				document.getElementById("autual_count").innerHTML = actualCount;
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
						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "2") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "block";
						document.getElementById("bank_block").style.display = "none";
					}
					else if (val == "3") {
						document.getElementById("wechat_block").style.display = "none";
						document.getElementById("alipay_block").style.display = "none";
						document.getElementById("bank_block").style.display = "block";
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
		
        <h3>取现申请</h3>
        
        <div>
	        <p>您可以选择以下三种提现方式：</p>
			<div>
		        <input type="radio" name="method" value="1" /> 微信
		        <input type="radio" name="method" value="2" /> 支付宝
		        <input type="radio" name="method" value="3" /> 银行转账
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
        </div>
        
        <hr>
        
        <div name="display">
	        <p>您现在拥有蜜券：<?php echo $mycredit;?></p>
	        <p>每次提现的最少数量为<?php echo $leastCredit; ?>蜜券,您今日还可以提取<?php echo $mostCredit; ?>蜜券。</p>
	        <input id="amount" class="form-control" type="text" placeholder="请输入提现金额！" onkeypress="return onlyNumber(event)" onblur="calcActualNum()" /> 
			<p>您实际将提取出的蜜券数量是：<span id="autual_count">0</span></p>
	        <input id="pwd" type="password" class="form-control" placeholder="请输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
	        <br>
	        <input type="button" value="提交" onclick="onConfirm()" />
	        <input type="button" value="取消" onclick="javascript:history.back(-1);" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>