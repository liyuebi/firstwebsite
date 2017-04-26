<?php
session_start();

// if not login, jump to index page
if (!$_SESSION["isLogin"]) {
	$home_url = '../index.html';
	header('Location: ' . $home_url);
	exit();
}
else {
	if ($_SESSION['buypwd'] == '') {
		
		$url = 'jump.php?source=3';
		header('Location: ' . $url);
		exit;
	}
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

include "../php/constant.php";
$leastCredit = $withdrawFloorAmount;
$handlefee = $withdrawHandleRate;

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
		<script type="text/javascript">
			
			function onConfirm()
			{
				var amount = document.getElementById("amount").value;
				var paypwd = document.getElementById("pwd").value;
				
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
				
				$.post("../php/credit.php", {"func":"withdraw","amount":amount,"paypwd":paypwd}, function(data){
					
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
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div>
            <h3>取现申请</h3>
        </div>
        
        <div name="display">
	        <p>您现在拥有蜜券：<?php echo $mycredit;?></p>
	        <p>每次提现的最少数量为<?php echo $leastCredit; ?>蜜券</p>
	        <input id="amount" type="text" placeholder="请输入充值金额！" onkeypress="return onlyNumber(event)" onblur="calcActualNum()" /> 
			<p>您实际将提取出的蜜券数量是：<span id="autual_count">0</span></p>
	        <input id="pwd" type="password" placeholder="请输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
	        <br>
	        <input type="button" value="提交" onclick="onConfirm()" />
	        <input type="button" value="取消" onclick="javascript:history.back(-1);" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>