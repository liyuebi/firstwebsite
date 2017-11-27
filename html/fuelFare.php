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

include "../php/database.php";
include "../php/constant.php";
$con = connectToDB();

$userid = $_SESSION["userId"];
$result = false;

if ($con) {
	
	$result = mysql_query("select * from CreditTrade where Status='$creditTradeInited'");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>油卡充值</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
<!-- 		<link rel="stylesheet" href="../css/buttons.css"> -->
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script src="../js/md5.js"></script>
		<script type="text/javascript">
						
			function tryChargeOilCard()
			{	
				var company = 2;
				var card = document.getElementById("card").value;
				var phonenum = document.getElementById("phonenum").value;
				var amount = $('#amt input:radio:checked').val();
				var paypwd = document.getElementById("pwd").value;
				
				card = $.trim(card);
				phonenum=$.trim(phonenum);
				amount=$.trim(amount);
				paypwd = $.trim(paypwd);
				if (!isPhoneNumValid(phonenum)) {
					alert("无效的电话号码！");
					return;
				}
				if (card == "") {
					alert("无效的油卡号！");
					return;					
				}
				if (amount != "1000" && amount != "2000") {
					alert("无效的金额！");
					return;
				}
				if (paypwd == "") {
					alert("无效的支付密码！");
					return;
				}
				
				var str = "确定为";
				str += "中石化";
				str = str + "油卡号 " + card + " 充值" + amount + ", 将收取手续费10%"; 
				if (confirm(str)) {
					paypwd = md5(paypwd);		
					
					$.post("../php/trade.php", {"func":"fcla", "phonenum":phonenum, "amount":amount,"card":card,"paypwd":paypwd}, function(data){
					
						if (data.error == "false") {
							alert("创建订单成功！");	
							
							document.getElementById("card").value = "";
							document.getElementById("phonenum").value = "";
// 							document.getElementById("amount").value = "";
							document.getElementById("pwd").value = "";
						}
						else {
							alert("创建订单失败: " + data.error_msg);
						}
					}, "json");
				}
			}			
			
			function goback() 
			{
				location.href = "virtuelife.php";
			}
			
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">油费充值</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>

		<div style="margin: 5px 5px 0 5px;">
						
			<input id="card" class="form-control" style="margin-top: 5px" type="text" placeholder="请输入加油卡号！" /> 
			<span class="help-block">仅支持<b class="text-warning"> 中石化 </b>的油卡充值！</span>
			
			<input id="phonenum" class="form-control" style="margin-top: 5px" type="text" placeholder="请输入油卡关联手机号！" onkeypress="return onlyNumber(event)" />
<!-- 			<input id="amount" class="form-control" style="margin-top: 5px" type="text" placeholder="请输入充值金额！" onkeypress="return onlyNumber(event)" />  -->

			<div style="margin-top: 8px;">
				<label class="text-info">充值金额</label>
				<div class="well well-sm" id="amt">
					<label class="radio-inline">
						  <input type="radio" name="amtRadios" id="amtRadios1" value="1000" checked> 1000
					</label>
					<label class="radio-inline">
						  <input type="radio" name="amtRadios" id="amtRadios2" value="2000"> 2000
					</label>
				</div>
			</div>

			<input id="pwd" class="form-control" style="margin-top: 5px" type="text" placeholder="请输入支付密码！" />
			<input type="button" class="btn btn-primary btn-lg btn-block" style="width: 100%; margin-top: 5px" value="确认" onclick="tryChargeOilCard()" />

	        <div>
		        <hr>
		        <h4 class="text-warning" style="">注意事项</h4>
			    <p style="margin: 0;">充值完成后请前往附近网点进行圈存。</p>
	        </div>
		</div>
    </body>
</html>