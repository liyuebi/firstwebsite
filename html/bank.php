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
	
	$result = mysql_query("select * from CreditBank where UserId='$userid' order by SaveTime desc");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>存储</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function trySave()
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
				
/*
				if (amount % <?php echo $refererConsumePoint; ?> != 0) {
					alert("充值金额必须是" + <?php echo $refererConsumePoint; ?> + "的倍数！");
					return;
				}
*/
				
				$.post("../php/creditTrade.php", {"func":"saveCredit","amount":amount}, function(data){
					
					if (data.error == "false") {
						alert("存储成功！");	
						location.reload();
					}
					else {
						alert("存储失败: " + data.error_msg);
						document.getElementById("amount").value = "";
					}
				}, "json");
			}
			
		</script>
	</head>
	<body>
		<p align="center">存储</p>
<!-- 		<p align="right">交易记录</p> -->

		<div>
			<p>添加新存储：</p>
			<input id="amount" class="form-control" type="text" placeholder="请输入存储数量，必须是100的倍数！" onkeypress="return onlyNumber(event)" /> 
			<input type="button" class="button button-border button-rounded button-primary" style="width: 80%;" value="确认" onclick="trySave()" />
		</div>
		
		<hr>
		
		<div>
			<p>已有存储：</p>
			<?php
				if ($result) {
					date_default_timezone_set('PRC');
					while ($row = mysql_fetch_array($result)) {
			?>
						<div>
							<p>总额度：<?php echo $row["Quantity"]; ?></p>
							<p>存储时间：<?php echo date("Y-m-d H:i:s", $row["SaveTime"]); ?></p>
							<p>剩余额度：<?php echo $row["Balance"]; ?></p>
<!-- 							<p>交易过期时间：<?php echo 0; ?></p> -->
						</div>
						<hr>
			<?php
					}
				}
			?>
		</div>
    </body>
</html>