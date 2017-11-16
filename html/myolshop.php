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
$userid = $_SESSION['userId'];
$row = false;

if ($con) {
	$result = mysql_query("select * from OfflineShop where UserId='$userid'");
	if (!$result) {
	}
	else if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>线下商家</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle1.0.1.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/jquery.form-3.46.0.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){
			})
			
			function tryRegisterOlShop()
			{
				$.post("../php/offlineTrade.php", {"func":"createOLSAcc"}, function(data){
					
					if (data.error == "false") {
						alert("注册线下商家成功！");	
						location.reload();
					}
					else {
						alert("注册线下商家失败: " + data.error_msg);
						
						return;
					}
				}, "json");				
			}
			
			function startEdit()
			{
				document.getElementById("name").style.display = "block";
				document.getElementById("man").style.display = "block";
				document.getElementById("phone").style.display = "block";
				document.getElementById("add").style.display = "block";
				document.getElementById("file_input").style.display = "block";
				document.getElementById("btns").style.display = "block";
				
				document.getElementById("ori_name").style.display = "none";
				document.getElementById("ori_man").style.display = "none";
				document.getElementById("ori_phone").style.display = "none";
				document.getElementById("ori_add").style.display = "none";
				document.getElementById("btn_edit").style.display = "none";
				document.getElementById("btn_review").style.display = "none";
				
			}
			
			function finishEdit()
			{
				document.getElementById("ori_name").style.display = "block";
				document.getElementById("ori_man").style.display = "block";
				document.getElementById("ori_phone").style.display = "block";
				document.getElementById("ori_add").style.display = "block";
				document.getElementById("btn_edit").style.display = "block";
				document.getElementById("btn_review").style.display = "block";
				
				document.getElementById("name").style.display = "none";
				document.getElementById("man").style.display = "none";
				document.getElementById("phone").style.display = "none";
				document.getElementById("add").style.display = "none";
				document.getElementById("file_input").style.display = "none";
				document.getElementById("btns").style.display = "none";
			}
			
			function filechange(event) 
			{
				var files = event.target.files;
				var file;
				if (files && files.length > 0) {
					file = files[0];
					if (file.size > 3 * 1024 * 1024) {
						alert('图片大小不能超过 3MB！');
						return false;
					}
// 					var url = window.URL || window.webkitURL;
// 					var imgurl = url.createObjectURL(file);
// 					document.getElementById("img").src = imgurl;
// 				 	if (document.getElementById("ori_img")) {
// 					 	document.getElementById("ori_img").style.display = "hidden";
// 				 	}
				}
				return true;
			}
			
			function trySubmit()
			{
				var options = {
					url:	'../php/offlineTrade.php',
					dataType: 'json',
					success: afterSubmit
				};
				$('#post_form').ajaxSubmit(options);
				return false;
			}
			
			function afterSubmit(data)
			{
				if (data.error == 'true') {
					alert("编辑失败：" + data.error_msg);
				}	
				else {
					alert("编辑成功!");
					location.reload();
				}
			}
			
			function applyForReview()
			{
				var idx = document.getElementById("idx").value;
				$.post("../php/offlineTrade.php", {"func":"afr","idx":idx}, function(data){
					
					if (data.error == "false") {
						alert("已提交审核，请耐心等待！");	
						location.reload();
					}
					else {
						alert("审核申请失败: " + data.error_msg);
					}
				}, "json");				
			}
			
			function goback() 
			{
				location.href = "olshop.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h4 style="text-align: center; color: white">我的线下商家</h4></div>
				<div class="col-xs-3 col-md-3"><!-- <input type="button" class="button button-raised button-rounded button-small" style="float: right" value="订单" onclick="gotoCreditOrder()"> --></div>
			</div>
		</div>
		
<!--	// 关闭自己注册线下商家账号的功能，必须由推荐时注册
		<div style="margin: 10px 3px 0 3px; display: <?php if ($row) echo "none"; else echo "block"; ?>">
			<div>
				<label>注册线下商家账号</label>
				<input type="button" class="btn btn-block btn-success" value="注册线下商家" aria-describedby="helpBlock" onclick="tryRegisterOlShop()">
				<span id="helpBlock" class="help-block">注册线下商户需要消耗<?php echo $offlineShopRegisterFee; ?>线上云量。</span>
			</div>
			
			<hr>
			<div>
				<label>线下商家账号打开流程</label>
				<ol>
					<li>支付线上云量获取账号资格</li>
					<li>完善商户信息，上传营业执照照片</li>
					<li>审核通过，开始运营</li>
				</ol>
			</div>
		</div>
-->
		
		<div style="margin: 10px 3px 0 3px; display: <?php if ($row) echo "block"; else echo "none"; ?>">
			
			<?php
				if ($olshopRegistered == $row["Status"]) {
			?>
			<p class="alert alert-danger">请填写商家信息，并提交审核！</p>
			<?php
				}
				else if ($olshopApplied == $row["Status"]) {
			?>
			<p class="alert alert-info">您的信息已提交审核，请耐心等待！</p>
			<?php
				}
				else if ($olshopDeclined == $row["Status"]) {
			?>
			<p class="alert alert-danger">您的商家账户未通过审核，请完善信息，并重新提交审核。可联系客服获得详细信息。</p>
			<?php
				}
			?>
			
			<form id="post_form" action="../php/offlineTrade.php" enctype="multipart/form-data" method="post" onsubmit="return trySubmit();">
				<input name='func' type="hidden" value="editInfo" />
				<input id="idx" name='idx' type="hidden" value='<?php echo $row["ShopId"] ?>' />
				<div class="form-group">
					<label>商家编号：</label>
				    <span class="span3"><?php echo $row['ShopId']; ?></span>
				</div>
				<div class="form-group">
				    <label for="name">商家名称：</label>
				    <span id='ori_name'><?php echo $row["ShopName"]; ?></span>
				    <input id='name' name='name' type="text" class="form-control" value="<?php echo $row["ShopName"]; ?>" placeholder="请输入店铺名称" style="display: none" />
				</div>
				<div class="form-group">
				    <label for="man">联系人：</label>
					<span id='ori_man'><?php echo $row["Contacter"]; ?></span>
					<input id='man' name='man' type="text" class="form-control" value="<?php echo $row["Contacter"]; ?>" placeholder="请输入联系人" style="display: none" />
				</div>
				<div class="form-group">
				    <label for="phone">联系电话：</label>
					<span id='ori_phone'><?php echo $row["PhoneNum"]; ?></span>
					<input id='phone' name='phone' type="text" class="form-control" value="<?php echo $row["PhoneNum"]; ?>" placeholder="请输入联系电话" onkeypress="return onlyNumber(event)" style="display: none" />
				</div>
				<div class="form-group">
				    <label for="phone">地址：</label>
					<span id='ori_add'><?php echo $row["Address"]; ?></span>
					<input id='add' name='add' type="text" class="form-control" value="<?php echo $row["Address"]; ?>" placeholder="请输入店铺地址" style="display: none" />
				</div>
				<div class="form-group">
				    <label>营业执照：</label>
				    <?php
					    if ($row["LicencePic"] != "") {
					?>
						<div class='thumbnail' style="max-width: 40%;">
							<img id="ori_img" class="img-rounded" src="../olLicensePic/<?php echo $row["LicencePic"]; ?>">
						</div>
					<?php
						}
					?>
				    <input id='file_input' type="file" id="file" name='file' value="选择图片" accept="image/jpeg,image/png;" onchange="filechange(event)" style="display: none" />
<!-- 					    <img id="old_img" src="" alt=""> -->
<!-- 				    <p class="help-block"></p> -->
				</div>
				<div id="btns" style="display: none">
					<button id="btn_submit" type="submit" class="btn btn-success" >提交</button>
					<button type="button" class="btn btn-warning" onclick="finishEdit()">取消编辑</button>
				</div>
			</form>
			
			<input id="btn_edit" type="button" class="btn btn-info btn-block" value="编辑" onclick="startEdit()" />
			<input id="btn_review" type="button" class="btn btn-success btn-block" value="提交审查" onclick="applyForReview()" />
			
		</div>
	</body>
</html>