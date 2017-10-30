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

$userid = $_SESSION["userId"];
$result = false;
$con = connectToDB();
if ($con) {
	$result = mysql_query("select * from CreditRecord where UserId='$userid' order by AcceptTime desc, IndexId desc");
}
	
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
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
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
				<div class="col-xs-6 col-md-6"><h3 style="display: table-cell; text-align: center; color: white">云量记录</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
        <div>
	    	<?php
		    	include "../php/constant.php";
		    	date_default_timezone_set('PRC');
		        while ($row = mysql_fetch_array($result)) {
			?>  	
			<div>
				    <p style="margin: 10px 5px;"><?php 
					    echo date("Y-m-d H:i" ,$row["ApplyTime"]);
					    echo "<br>";
					    if ($row["Type"] == $codeBuy) {
// 							echo "您通过云量交易获得" . $row["Amount"] . "线上云量。"; 				    
				    	}
				    	else if ($row["Type"] == $codeSell) {
// 					    	echo "您申请赎回" . $row["Amount"] . "线上云量，收取手续费" . $row["HandleFee"] . "线上云量。";
				    	}
				    	else if ($row["Type"] == $codeDivident) {					    	
					    	echo "您今日领到" . $row["Amount"] . "线上云量。";
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
				    	
				    	echo "<br>";
				    	echo "当前线上云量" . $row["CurrAmount"] . "。";
					    ?>
					</p>
					<hr>
			</div>
			<?php       	
		       	}
	       	?>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>