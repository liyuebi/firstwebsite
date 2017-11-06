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
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
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
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">存储</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>

		<div>
			<h4>添加新存储：</h4>
			<input id="amount" class="form-control" type="text" placeholder="请输入存储数量，必须是100的倍数！" onkeypress="return onlyNumber(event)" /> 
			<input type="button" class="btn btn-info btn-lg btn-block" style="width: 100%; margin-top: 5px" value="确认" onclick="trySave()" />
		</div>
		
		<hr>
		
		<div>
			<h4>已有存储：</h4>
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