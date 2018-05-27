<?php
	
include "../php/database.php";
$con = connectToDB();
if (!$con)
{
	return false;
}

session_start();
$userid = $_SESSION["userId"];

$profit = 0;

$result = mysqli_query($con, "select * from Credit where UserId='$userid'");	
if ($result && mysqli_num_rows($result) > 0) {
	$row = mysqli_fetch_assoc($result);
	$profit = $row["ProfitPnt"];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>消费云量管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/md5.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">

			$(document).ready(function(){
				if (isNotLoginAndJump()) {
					return;
				}
			})
			
			function toCredit()
			{
				var cnt = document.getElementById("num").value;
				cnt=$.trim(cnt);
				var paypwd = document.getElementById("paypwd").value;
				paypwd=$.trim(paypwd);

				if (cnt == "" || !isValidNum(cnt)) {
					alert("无效的数量，请重新输入");
					document.getElementById("num").focus();
					return;
				}

				if (!confirm("您确定要将" + cnt + "消费云量转换成等值的线上云量吗？")) {
					return;
				}

				paypwd = md5(paypwd);
				$.post("../php/credit.php", {"func":"pToC", "num":cnt, "paypwd":paypwd}, function(data){
					
					if (data.error == "false") {
						alert("转移成功！");// \n新用户的ids是" + data.new_user_id);	
						location.reload();
					}
					else {
						alert("转移失败: " + data.error_msg);
					}
				}, "json");
			}

			function toShareCredit()
			{
				var cnt = document.getElementById("num").value;
				cnt=$.trim(cnt);
				var paypwd = document.getElementById("paypwd").value;
				paypwd=$.trim(paypwd);

				if (cnt == "" || !isValidNum(cnt)) {
					alert("无效的数量，请重新输入");
					document.getElementById("num").focus();
					return;
				}

				if (!confirm("您确定要将" + cnt + "消费云量转换成等值的分享云量吗？")) {
					return;
				}

				paypwd = md5(paypwd);
				$.post("../php/credit.php", {"func":"pToS", "num":cnt, "paypwd":paypwd}, function(data){
					
					if (data.error == "false") {
						alert("转移成功！");	
						location.reload();
					}
					else {
						alert("转移失败: " + data.error_msg);
					}
				}, "json");

			}

			function toPnt()
			{
				var cnt = document.getElementById("num").value;
				cnt=$.trim(cnt);
				var paypwd = document.getElementById("paypwd").value;
				paypwd=$.trim(paypwd);

				if (cnt == "" || !isValidNum(cnt)) {
					alert("无效的数量，请重新输入");
					document.getElementById("num").focus();
					return;
				}

				if (!confirm("您确定要将" + cnt + "消费云量转换成等值的线下云量吗？")) {
					return;
				}

				paypwd = md5(paypwd);
				$.post("../php/credit.php", {"func":"pToP", "num":cnt, "paypwd":paypwd}, function(data){
					
					if (data.error == "false") {
						alert("转移成功！");// \n新用户的ids是" + data.new_user_id);	
						location.reload();
					}
					else {
						alert("转移失败: " + data.error_msg);
					}
				}, "json");
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
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h4 style="text-align: center; color: white">消费云量</h4></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
        <div align="center" class="alert alert-info">
            <h3 class="text-info">消费云量: <?php echo $profit; ?></h3>
        </div>
        
        <div>
        	<input type="text" class="form-control" id="num" placeholder="请输入转移数额" onkeypress="return onlyNumber(event)" />
        	<input type="password" class="form-control" id="paypwd" name="paypwd" placeholder="请输入您的支付密码！" style="margin-top: 10px;" />
        	<div class="container-fluid">
        		<div class="row">
        			<div class="col-md-4" style="padding: 10px;">
		        		<button class="btn btn-primary btn-block" onclick="toCredit()">转为线上云量</button>
		        	</div>
        			<div class="col-md-4" style="padding: 10px;">
		        		<button class="btn btn-info btn-block" onclick="toShareCredit()">转为分享云量</button>
		        	</div>
		        	<div class="col-md-4" style="padding: 10px;">
		        		<button class="btn btn-success btn-block" onclick="toPnt()">转为线下云量</button>
		        	</div>
		        </div>
        	</div>
        </div>
    </body>
</html>