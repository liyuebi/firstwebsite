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

if ($_SESSION['buypwd'] == '') {
	
	$url = 'jump.php?source=3';
	header('Location: ' . $url);
	exit;
}

$mycredit = 0;
// $dayWithdraw = 0;
// $applyCount = 0;

include "../php/constant.php";
$leastCredit = $transferFloorAmount;
// $mostCredit = $withdrawCeilAmountOneDay;
$handlefee = $transferHandleRate;

include "../php/database.php";
$con = connectToDB();
if ($con) {
	$userid = $_SESSION["userId"];
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$mycredit = $row["Credits"];
		
/*		
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
*/
		
	}
}
// $mostCredit = max(0, $mostCredit - $dayWithdraw - $applyCount);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>线上云量互转</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
			
			function getUserInfo() 
			{
				var val = document.getElementById("rec").value;
				if (val.length == 0) {
					alert("无效的输入，请新输入！");
					document.getElementById("rec").value = "";
					document.getElementById("rec").focus();
					return;
				}
				
				$.post("../php/login.php", {"func":"getProfile","iden":val}, function(data){
							
					if (data.error == "false") {
						if (data.found == "true") {
							document.getElementById("account").innerHTML = data.user.id;
							document.getElementById("name").innerHTML = data.user.name;
							document.getElementById("phone").innerHTML = data.user.num;
						}
						else if (data.found == "false") {
							alert("查不到对应的用户，请检查您填写的手机号或账号！");
							document.getElementById("rec").focus();
							document.getElementById("account").innerHTML = '';
							document.getElementById("name").innerHTML = '';
							document.getElementById("phone").innerHTML = '';
						}
					}
					else {
						alert("查询对方信息出错，请稍后重试！");
						document.getElementById("account").innerHTML = '';
						document.getElementById("name").innerHTML = '';
						document.getElementById("phone").innerHTML = '';
					}
				}, "json");
			}
			
			function calcActualNum()
			{
				var amount = document.getElementById("amount").value;
				var amountReg = /^[1-9]\d*$/;
				if (!amountReg.test(amount)) {
					document.getElementById("autual_count").innerHTML = "0";
					return;
				}
				
				var least = <?php echo $leastCredit; ?>;
				if (amount < least) {
					alert("每次提取至少" + least + "线上云量！");
					document.getElementById("autual_count").innerHTML = "0";
					return;
				}
				
				var actualCount = 0;
				var rate = <?php echo $handlefee; ?>;
				actualCount = amount - Math.floor(amount * rate * 100) / 100;
				document.getElementById("autual_count").innerHTML = actualCount;
			} 
			
			function tryTransfer()
			{
				var amountStr = $.trim(document.getElementById("amount").value);
				var amountReg = /^[1-9]\d*$/;
				var val = amountReg.test(amountStr);
				if (!amountReg.test(amountStr)) {
					alert("输入的数字无效");
					document.getElementById("autual_count").innerHTML = "0";
					return;
				}
				
				var amount = parseInt(amountStr);
				var least = <?php echo $leastCredit; ?>;
				if (amount <= 0) {
					alert("转账数值必须大于0");
					document.getElementById("autual_count").innerHTML = "0";
					return;
				}
				if (amount < least) {
					alert("转账数值必须大于" + least + ",请重新输入！");
					document.getElementById("autual_count").innerHTML = "0";
					document.getElementById("autual_count").focus();
					return;
				}
				var toUser = document.getElementById("account").innerHTML;
				if (toUser == '') {
					alert("请选择收款人！");
					return;
				}
				
				var payPwd = document.getElementById("pwd").value;
				if (payPwd == '') {
					alert("请输入支付密码！");
					document.getElementById("pwd").focus();
					return;
				}
				
				var rate = <?php echo $handlefee; ?>;
				var fee = Math.floor(amount * rate * 100) / 100;
				var actualCount = amount - fee;
				var toPhone = document.getElementById("phone").innerHTML;
				var toName = document.getElementById("name").innerHTML;
				
				var str = "您确定向用户" + toName + "（ID " + toUser + ")，手机号" + toPhone + "转账" + amount + "，手续费为" + fee + "，对方实际收到" + actualCount;
				var go = confirm(str);
				if (go) {
					payPwd = md5(payPwd);
					$.post("../php/credit.php", {"func":"transfer","toUser":toUser,"amount":amount,"pwd":payPwd}, function(data){
							
						if (data.error == "false") {
							if (confirm("转账成功，是否继续转账？")) {
								document.getElementById("rec").value = '';
								document.getElementById("account").innerHTML = '';
								document.getElementById("name").innerHTML = '';
								document.getElementById("phone").innerHTML = '';
								document.getElementById("amount").value = '';
								document.getElementById("autual_count").innerHTML = "0";
								document.getElementById("pwd").value = '';
							}
							else {
								location.href = "home.php";
							}
						}
						else {
							alert("转账失败：" + data.error_msg);
						}
					}, "json");
				}	
				else {
				}
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
            <h3>线上云量互转</h3>
        </div>
        
        <div name="display">
	        <table>
		        <tr>
			        <td width="40%">请输入对方的手机号或账号</td>
			        <td width="60%"><input id="rec" type="text" value="" /><input type="button" value="确认" onclick="getUserInfo()" /></td>
		        </tr>
		        <tr>
			        <td width="30%" style="text-align: right;">账号</td>
			        <td><span id="account"></span></td>
		        </tr>
		        <tr>
			        <td width="30%" style="text-align: right;">姓名</td>
			        <td><span id="name"></span></td>
		        </tr>
		        <tr>
			        <td width="30%" style="text-align: right;">手机号</td>
			        <td><span id="phone"></span></td>
		        </tr>

	        </table>
	        	    
	        <p>您现在拥有线上云量：<?php echo $mycredit;?></p>
	        <p>转账每笔最少为<?php echo $leastCredit; ?>线上云量。</p>    
	        <input class="form-control" id="amount" value="" placeholder="请输入您要转的金额" onkeypress="return onlyNumber(event)" onblur="calcActualNum()" /> 
			<p>您实际将提转出的线上云量数量是：<span id="autual_count">0</span></p>
			
	        <input class="form-control"  id="pwd" type="password" placeholder="请输入支付密码！" onkeypress="return onlyCharAndNum(event)" />
	        <br>
	        <input type="button" value="确认" onclick="tryTransfer()" />
	        <input type="button" value="取消" onclick="javascript:history.back(-1);" />
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>