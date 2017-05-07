<?php

include "../php/database.php";

session_start();
if (!$_COOKIE['isLogin']) {	
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

$userid = $_SESSION["userId"];
$result = false;
$con = connectToDB();
if ($con) {
	$result = mysql_query("select * from CreditRecord where UserId='$userid' order by AcceptTime desc");
}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>资金纪录</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div>
	    	<?php
		    	include "../php/constant.php";
		    	date_default_timezone_set('PRC');
		        while ($row = mysql_fetch_array($result)) {
			?>  	
			<div>
				    <p><?php 
					    echo date("Y-m-d H:i" ,$row["ApplyTime"]);
					    echo "<br>";
					    if ($row["Type"] == $codeRecharge) {
							echo "您购买了" . $row["Amount"] . "蜜券。"; 				    
				    	}
				    	else if ($row["Type"] == $codeWithdraw) {
					    	echo "您赎回了" . $row["Amount"] . "蜜券，收取手续费" . $row["HandleFee"] . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeDivident) {					    	
					    	echo "您固定分红得到" . $row["Amount"] . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeBonus) {
					    	echo "用户" . $row["WithUserId"] . "购物，您收获了" . $row["Amount"] . "蜜券。"; 
				    	}
				    	else if ($row["Type"] == $codeConsume) {
					    	echo "您购物使用了" . $row["Amount"] . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeCancelPurchase) {
					    	echo "您取消订单，返还了" . $row["Amount"] . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeRecommend) {
					    	echo "您推荐新用户" . $row["WithUserId"] . "，使用了" . $row["Amount"] . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeTransferTo) {
					    	echo "您向用户" . $row["WithUserId"] . "转账了" . $row["Amount"] . "蜜券，收取手续费" . $row["HandleFee"] . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeTransferFrom) {
					    	$actual = $row["Amount"] - $row["HandleFee"];
					    	echo "您收到用户" . $row["WithUserId"] . "的转账" . $row["Amount"] . "蜜券，扣除手续费实际获得" . $actual . "蜜券。";
				    	}
				    	else if ($row["Type"] == $codeDynDivident) {					    	
					    	echo "您动态分红得到" . $row["Amount"] . "蜜券。";
				    	}
				    	
					    echo "<br>";
					    echo "当前剩余蜜券" . $row["CurrAmount"] . "。";
					    ?>
					</p>
			</div>
			<?php       	
		       	}
	       	?>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>