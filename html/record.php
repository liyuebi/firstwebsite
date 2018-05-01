<?php

include "../php/database.php";

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

$showTab2 = false;
if (isset($_GET['t']) && 1 == $_GET['t']) {
	$showTab2 = true;
}

$userid = $_SESSION["userId"];
$result = false;
$res = false;
$res1 = false;
$con = connectToDB();
if ($con) {
	$result = mysqli_query($con, "select * from CreditRecord where UserId='$userid' order by AcceptTime desc, IndexId desc");
	$res = mysqli_query($con, "select * from PntsRecord where UserId='$userid' order by ApplyTime desc, IndexId desc");
	$res1 = mysqli_query($con, "select * from ProfitPntRecord where UserId='$userid' order by ApplyTime desc, IndexId desc");
}
	
date_default_timezone_set('PRC');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>云量记录</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js" ></script>
		<script type="text/javascript">
			
			function goback()
			{
				location.href = "me.php";
			}	
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">云量记录</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
        <div class="tabbable" style="margin: 10px 3px;">
	        <ul class="nav nav-tabs">
				<li class="<?php if (!$showTab2) echo 'active'; ?>"><a href="#tab1" data-toggle="tab">线上云量记录</a></li>
				<li class="<?php if ($showTab2) echo 'active'; ?>"><a href="#tab2" data-toggle="tab">线下云量记录</a></li>
				<li class="<?php // if ($showTab2) echo 'active'; ?>"><a href="#tab3" data-toggle="tab">消费云量记录</a></li>
			</ul>
			<div class="tab-content">
		        <div class="tab-pane <?php if (!$showTab2) echo 'active'; ?>" id="tab1">
		    	<?php
			    	include "../php/constant.php";
			        while ($row = mysqli_fetch_assoc($result)) {
				?>  	
				    <p style="margin: 5px 3px;"><?php 
					    echo date("Y-m-d H:i" ,$row["ApplyTime"]);
					    echo "<br>";
					    if ($row["Type"] == $codeBuy) {
	// 							echo "您通过云量交易获得" . $row["Amount"] . "线上云量。"; 				    
				    	}
				    	else if ($row["Type"] == $codeSell) {
	// 					    	echo "您申请赎回" . $row["Amount"] . "线上云量，收取手续费" . $row["HandleFee"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeDivident) {					    	
					    	echo "领取" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeReferer) {
					    	echo "推荐新用户，使用了" . $row["Amount"] . "线上云量。"; 
				    	}
				    	else if ($row["Type"] == $codeReferBonus) {
					    	echo "推荐新用户获得直推奖励" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeColliBonusNew) {
					    	echo "推荐新用户获得对碰奖励" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeColliBonusRe) {
					    	echo "您的队友进行财富存储，您获得了对碰奖励" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeSave) {
					    	echo "您进行财富存储，存储了" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeCreTradeInit) {
					    	echo "您在云量交易挂卖" . $row["Amount"] . "线上云量，收取手续费" . $row["HandleFee"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeCreTradeSucc) {					    	
					    	echo "云量交易完成，退回未交易部分的" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeCreTradeCancel) {					    	
					    	echo "云量交易取消，退回" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeCreTradeRec) {
					    	echo "您通过云量交易购买成功，" . $row["Amount"] . "线上云量到账。";
				    	}
				    	else if ($row["Type"] == $codeTryChargePhone) {
					    	echo "您申请话费充值，使用" . $row["Amount"] . "线上云量，收取手续费" . $row["HandleFee"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeStopChargePhone) {
					    	echo "您话费充值取消，退回" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeRegiOlShop) {
					    	echo "您注册了线下商家，使用" . $row["Amount"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeFromProfit) {
					    	echo "将" . $row["Amount"] . "消费云量转为线上云量。";
				    	}
				    	
				    	echo "<br>";
				    	echo "当前线上云量" . $row["CurrAmount"] . "。";
					    ?>
					</p>
					<hr>
				<?php       	
			    	}
		       	?>
		       	</div>
		       	
		       	<div class="tab-pane <?php if ($showTab2) echo 'active'; ?>" id="tab2">
			    <?php
				    include "../php/constant.php";
			        while ($row = mysqli_fetch_assoc($res)) {
				?>  	
			    	<p style="margin: 5px 3px;">
			    	<?php
					    echo date("Y-m-d H:i" ,$row["ApplyTime"]);
					    echo "<br>";
					    
					    if ($row["Type"] == $code2Divident) {
					    	echo "领取" . $row["Amount"] . "线下云量。";
					    }
					    if ($row["Type"] == $code2Save) {
							echo "您进行云量存储，获得" . $row["Amount"] . "线下云量。"; 				    
				    	}
				    	else if ($row["Type"] == $code2OlShopPay) {
					    	echo "您在线下商家消费，支付" . $row["Amount"] . "线下云量。";
				    	}
				    	else if ($row["Type"] == $code2OlShopReceive) {					    	
					    	echo "收到用户支付的" . $row["RelatedAmount"] . "线下云量。";
					    	echo "<br>";
					    	echo "实际获得" . $row["Amount"] . "线下云量。";
				    	}
				    	else if ($row["Type"] == $code2OlShopBonus) {
					    	echo "推荐的商家收款，您获得奖励" . $row["Amount"] . "线下云量。"; 
				    	}
				    	else if ($row["Type"] == $code2OlShopWdApply) {
					    	echo "申请提现线下云量" . $row["RelatedAmount"] . "，收取手续费线下云量" . $row["HandleFee"] . "。";
				    	}
				    	else if ($row["Type"] == $code2OlSHopWdDecline) {
					    	echo "提现线下云量请求管理员拒绝，退回线下云量" . $row["Amount"] . "。";
				    	}
				    	else if ($row["Type"] == $code2TryCP) {
				    		echo "充话费使用线下云量" . $row["Amount"] . "。";
				    	}
				    	else if ($row["Type"] == $code2CancelCP) {
				    		echo "您取消了话费充值，返还线下云量" . $row["Amount"] . "。";
				    	}
				    	else if ($row["Type"] == $code2FromProfit) {
					    	echo "将" . $row["Amount"] . "消费云量转为线下云量。";
				    	}
				    				    	
				    	echo "<br>";
				    	echo "当前线下云量" . $row["CurrAmount"] . "。";
					?>
			    	</p>
					<hr>
				<?php       	
			    	}
		    	?>
		    	</div>

		    	<div class="tab-pane" id="tab3">
			    <?php
				    include "../php/constant.php";
			        while ($row1 = mysqli_fetch_assoc($res1)) {
				?>  	
					<p style="margin: 5px 3px;">
		    	<?php
				    echo date("Y-m-d H:i" ,$row1["ApplyTime"]);
				    echo "<br>";
			    	if ($row1["Type"] == $code3OlShopReceive) {					    	
				    	echo "收到用户支付的" . $row1["RelatedAmount"] . "消费云量。";
				    	echo "<br>";
				    	echo "实际获得" . $row1["Amount"] . "消费云量。";
			    	}
			    	else if ($row1["Type"] == $code3OlShopBonus) {
				    	echo "推荐的商家收款，您获得奖励" . $row1["Amount"] . "消费云量。"; 
			    	}
			    	else if ($row1["Type"] == $code3OlShopWdApply) {
				    	echo "申请提现消费云量" . $row1["RelatedAmount"] . "，收取手续费消费云量" . $row1["HandleFee"] . "。";
			    	}
			    	else if ($row1["Type"] == $code3OlSHopWdDecline) {
				    	echo "提现消费云量请求管理员拒绝，退回消费云量" . $row1["Amount"] . "。";
			    	}
			    	else if ($row1["Type"] == $code3ToCredit) {
				    	echo "将" . $row1["Amount"] . "消费云量转为线上云量。";
			    	}
			    	else if ($row1["Type"] == $code3ToPnts) {
				    	echo "将" . $row1["Amount"] . "消费云量转为线下云量。";
			    	}

			    	echo "<br>";
			    	echo "当前消费云量" . $row1["CurrAmount"] . "。";
			    ?>
		    		</p>
		    		<hr>
				<?php       	
			    	}
		    	?>
		    	</div>
			</div>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>