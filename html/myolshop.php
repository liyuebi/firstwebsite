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
	$result = mysqli_query($con, "select * from OfflineShop where UserId='$userid'");
	if (!$result) {
	}
	else if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
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
		<!-- <script src="../js/jquery.qrcode.min.js" ></script> -->
		<script src="../js/scripts.js" ></script>
		<script src="../js/jquery.form-3.46.0.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
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
				document.getElementById("btns_edit").style.display = "block";
				
				document.getElementById("ori_name").style.display = "none";
				document.getElementById("ori_man").style.display = "none";
				document.getElementById("ori_phone").style.display = "none";
				document.getElementById("ori_add").style.display = "none";
				document.getElementById("bnts_oper").style.display = "none";
				
			}
			
			function finishEdit()
			{
				document.getElementById("ori_name").style.display = "inline";
				document.getElementById("ori_man").style.display = "inline";
				document.getElementById("ori_phone").style.display = "inline";
				document.getElementById("ori_add").style.display = "inline";
				document.getElementById("bnts_oper").style.display = "block";
				
				document.getElementById("name").style.display = "none";
				document.getElementById("man").style.display = "none";
				document.getElementById("phone").style.display = "none";
				document.getElementById("add").style.display = "none";
				document.getElementById("file_input").style.display = "none";
				document.getElementById("btns_edit").style.display = "none";
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
			
			function goToWithdraw()
			{
				var shopId = <?php if ($row) echo $row["ShopId"]; else echo 0; ?>;
				location.href='withdraw.php?s=' + shopId;
			}
			
			function qrCode(btn)
			{
				var isQRGenerated = false;
				var node = document.getElementById("has_qr");
				if (node) {
					if (node.value == '1') {
						isQRGenerated = true;
					}
				}
				
				if (isQRGenerated) {
					// $('#qrModal').modal('show');
					location.href = "olsQRCode.php";
					return;
				}
				else {
					var idx = document.getElementById("idx").value;
					btn.disabled = true;
					$.post("../php/offlineTrade.php", {"func":"cqrc","idx":idx}, function(data){
						
						if (data.error == "false") {
							// alert("二维码生成成功");
							btn.disabled = false;
							btn.value = "查看二维码";
							
							$('#qrPic').attr('src', data.url);
							$('#qrModal').modal('show');
							return;
						}
						else {
							alert("生成二维码失败: " + data.error_msg);
						}
					}, "json");					
				}
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
		
		<?php 
			if (!$row) {
		?>
		<div style="margin: 10px 3px 0 3px;">
			<div>
				<h4 class="text-info">注册线下商家</h4>
				<input type="button" class="btn btn-block btn-success" value="注册线下商家" aria-describedby="helpBlock" onclick="tryRegisterOlShop()">
				<span id="helpBlock" class="help-block">注册线下商户需要消耗<?php echo $offlineShopRegisterFee; ?>分享云量。</span>
			</div>
			
			<hr>
			<div>
				<h4 class="text-warning">线下商家打开流程</h4>
				<ol>
					<li>支付分享云量获取资格</li>
					<li>完善商户信息，上传营业执照照片</li>
					<li>审核通过，开始运营</li>
				</ol>
			</div>
		</div>
		
		<?php
			}
			else {
		?>
		<div style="margin: 10px 3px 0 3px;">
			
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
				else if ($olshopAccepted == $row["Status"]) {
			?>
			<p class="alert alert-success">您已通过审核！</p>
			<?php
				}
				else if ($olshopDeclined == $row["Status"]) {
			?>
			<p class="alert alert-danger">您的商家账户未通过审核，请完善信息，并重新提交审核。详细信息请联系客服。</p>
			<?php
				}
				else if ($olshopClosed == $row["Status"]
							|| $olshopSuspended == $row["Status"]) {
			?>
			<p class="alert alert-danger">您的商家账户已被管理员下线，详细信息请联系客服。</p>
			<?php
				}
			?>
			
			<div id="btns_oper1">
				<input id='has_qr' type="hidden" value="<?php if ($row['QRCode'] != '') echo '1'; else echo '0'; ?>" >
				<?php
					if ($olshopAccepted == $row["Status"]) {
				?>
				<div class="well well-sm" style="display: -webkit-flex; display: flex; justify-content: space-around;">
					<input id="btn_qr" type="button" class="btn btn-primary" style="width: 45%;" value="<?php if ($row['QRCode'] != '') echo '查看二维码'; else echo '生成二维码'; ?>" onclick="qrCode(this)" >
					<input id="btn_withdraw" type="button" class="btn btn-primary" style="width: 45%;" value="申请提现" onclick="goToWithdraw()" />	
				</div>
				<?php
					}
				?>
			</div>
			
			<form id="post_form" action="../php/offlineTrade.php" enctype="multipart/form-data" method="post" onsubmit="return trySubmit();">
				<input name='func' type="hidden" value="editInfo" />
				<input id="idx" name='idx' type="hidden" value='<?php echo $row["ShopId"]; ?>' />
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
				<div id="btns_edit" style="display: none">
					<button id="btn_submit" type="submit" class="btn btn-success" >提交</button>
					<button type="button" class="btn btn-warning" onclick="finishEdit()">取消编辑</button>
				</div>
			</form>
			
			<div id="bnts_oper">
				<input id="btn_edit" type="button" class="btn btn-primary btn-block" value="编辑信息" onclick="startEdit()" />
				<?php
					if ($olshopAccepted != $row["Status"]
							&& $olshopClosed != $row["Status"]
							&& $olshopSuspended != $row["Status"]) {
				?>
				<input id="btn_review" type="button" class="btn btn-success btn-block" value="提交审查" onclick="applyForReview()" />
				<?php
					}
				?>
			</div>
		</div>
		<?php
			}
		?>
		<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel">
			<div class="modal-dialog" role="document">
		    	<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="qrModalLabel">商家二维码</h4>
			    	</div>
					<div class="modal-body" style="text-align: center;">
						<img id="qrPic" src="<?php if ($row['QRCode'] != '') echo '../olqrc/' . $row['QRCode']; ?>" style="width: 80%; margin: 0 auto"></img>
					</div>
		    	</div>
			</div>
		</div>
	</body>
</html>