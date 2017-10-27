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
	
	$result = mysql_query("select * from CreditTrade where Status='$creditTradeInited' order by CreateTime desc");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>交易所</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function tryCreateTrade()
			{
				location.href = "exchangeCreate.php";
			}			
			
			function tryStartTrade(btn)
			{
				var idx = btn.id;
// 				var url = "exchageStart.php?h=" + idx;
				location.href = "exchangeStart.php?h=" + idx;
			}
			
			function gotoCreditOrder()
			{
				location.href = "exchangeOrder.php";
			}
			
			function goback() 
			{
				location.href = "home.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-4 col-md-4"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-4 col-md-4"><h2 style="display: table-cell; text-align: center; color: white">云量交易</h2></div>
				<div class="col-xs-4 col-md-4"></div>
			</div>
		</div>

		<?php
			if ($result) {
				date_default_timezone_set('PRC');
				while ($row = mysql_fetch_array($result)) {
		?>
					<hr>
					<div>
						<p>交易编号：<?php echo $row["TradeId"]; ?></p>
						<p>卖家昵称：<?php echo $row["SellNickN"] ?></p>
						<p>总交易额：<?php echo $row["Quantity"] ?></p>
						<p>交易创建时间：<?php echo date("Y-m-d H:i:s" ,$row["CreateTime"]); ?></p>
						<p>交易过期时间：<?php echo date("Y-m-d H:i:s", $row["CreateTime"] + 60 * 60 * 24); ?></p>
						<input type="button" id="<?php echo $row["IdxId"]; ?>" class="button button-border button-rounded" style="width: 50%;" value="购买" onclick="tryStartTrade(this)" />
					</div>
					<hr>
		<?php
				}
			}
		?>
		<input type="button" class="button button-border button-rounded button-primary" style="width: 100%;" value="挂单" onclick="tryCreateTrade()" />
		<input type="button" class="button button-border button-rounded button-primary" style="width: 100%;" value="交易订单" onclick="gotoCreditOrder()" />
    </body>
</html>