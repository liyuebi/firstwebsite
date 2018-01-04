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

$userid = $_SESSION["userId"];
$type = $_GET["t"];
$relatedId = $_GET["i"];
$res = false;
$row = false;
$respNickName = "";
$backUrl = "home.php";

if ($type == $complainTCreditTrade) {
	$backUrl = "exchangeOrder.php";
}

$con = connectToDB();
if ($con) {
	
	$res = mysqli_query($con, "select * from CreditTrade where IdxId='$relatedId'");
	if ($res && mysqli_num_rows($res) > 0) {
		$row = mysqli_fetch_assoc($res);
		$respNickName = $row["BuyerNickN"];
		
		if (empty($respNickName)) {
			
			$respId = $row["BuyerId"];
			$res1 = mysqli_query($con, "select * from ClientTable where UserId='$respId'");
			if ($res1 && mysqli_num_rows($res1) > 0) {
				
				$row1 = mysqli_fetch_assoc($res1);
				$respNickName = $row1["NickName"];
			}
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>投诉</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
						
			function issueComplaint()
			{
				var type = <?php echo $type; ?>;
				var issueId = <?php echo $relatedId; ?>;
				var desc = document.getElementById("ques_desc").value;
				desc = $.trim(desc);
				
				if (desc == null || desc == '') {
					alert("请先描述您的投诉问题！");
					return;
				}
				
				$.post("../php/complaint.php", {"func":"fireExComp","desc":desc,"t":type,"idx":issueId}, function(data){
					
					if (data.error == "false") {
						alert("提交投诉成功！\n请和买家协商解决问题！");	
// 						location.href = "exchangeOrder.php";
					}
					else {
						alert("提交投诉失败: " + data.error_msg);
						return;
					}
				}, "json");			
					
			}			
			
			function goback() 
			{
				location.href = "<?php echo $backUrl; ?>";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">发起投诉</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
		<?php 
			if ($row) {
		?>
		<div class="container-fluid" style="margin: 10px 0px;">
<!--
			<div>
				<span><strong>投诉类型：</strong></span> 
				<span class="span3">云量交易</span>
			</div>
			<div>
				<strong>投诉事件：</strong> <?php echo $row["TradeId"]; ?>（交易编号）
			</div>
			<div>
				<strong>投诉对象：</strong> <?php echo $respNickName; ?>
			</div>
			<div>
				<strong>问题描述：</strong>
				<textarea id="ques_content" name="content" placeholder="请描述投诉详情！" cols="40" rows="6" style="font-size: 18px; padding: 5px;" wrap="soft"></textarea>
			</div>
-->
			<dl class="dl-horizontal">
				<dt>投诉类型：</dt>
				<dd><span class="span3">云量交易</span></dd>
			</dl>
			<dl class="dl-horizontal">
				<dt>投诉事件：</dt> 
				<dd><?php echo $row["TradeId"]; ?>（交易编号）</dd>
			</dl>
			<dl class="dl-horizontal">
				<dt>投诉对象：</dt> 
				<dd><?php echo $respNickName; ?></dd>
			</dl>
			<dl class="dl-horizontal">
				<dt>问题描述：</dt>
				<dd><textarea id="ques_desc" name="content" placeholder="请描述投诉详情！" style="resize: none;" cols="40" rows="6" maxlength="60" style="font-size: 18px; padding: 5px;" wrap="soft"></textarea></dd>
			</dl>
			
			<input type="button" class="btn btn-danger btn-block" value="发起投诉" onclick="issueComplaint()" />
		</div>
		<?php
			}
			else {
		?>		
		<div style="text-align: center; height: 300px; margin-top: 20px;">
			<span style="display: inline-block; margin: auto 0; font-size: 20px;"><strong>出错了，请稍后重试！</strong></span>
		</div>
		<?php
			}
		?>
    </body>
</html>
 


