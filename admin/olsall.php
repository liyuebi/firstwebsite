<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include '../php/constant.php';

$res = false;

include "../php/database.php";
$con = connectToDB();
if ($con) {
	$res = mysqli_query($con, "select * from OfflineShop");
}

?>

<!DOCTYPE html">
<html>
	<head>
		<meta charset="utf-8">
		<title>线下商家</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">

			function changeWdHandleFee(e)
			{
				var rate = document.getElementById("rate_" + e.target.id).value;
				$.post("../php/offlineTrade.php", {"func":"cwrInA","sid":e.target.id,"r":rate}, function(data){

					if (data.error == "false") {
						alert("修改成功！");
					}
					else {
						alert("修改失败：" + data.error_msg);	
						document.getElementById("rate_" + e.target.id).value = document.getElementById("rate_" + e.target.id).dataset.orig;
					}
				}, "json");
			}

			function checkIncomeRecord(e)
			{
				location.href = "olspntrecord.php?sid=" + e.id;
			}

			function updateStatusLabel(id, text)
			{
				var tr = document.getElementById("status_" + id);
				while(tr.hasChildNodes()) //当div下还存在子节点时 循环继续  
			    {  
			        tr.removeChild(tr.firstChild);  
			    }  
			    var label = document.createElement("label");
			    label.innerHTML = text;
			    label.style.color = "red";
			    tr.appendChild(label);
			}

			function onConfirm(btn)
			{
				if (confirm("确认审核通过？")) {
					$.post("../php/offlineTrade.php", {"func":"afo","index":btn.id}, function(data){
						
						if (data.error == "false") {
							updateStatusLabel(btn.id, "已通过");
						}
						else {
							alert("修改审核状态失败：" + data.error_msg);
						}
					}, "json");
				}
			}
			
			function onDeny(btn)
			{	
				if (confirm("确认拒绝审核请求？")) {
					$.post("../php/offlineTrade.php", {"func":"dfo","index":btn.id}, function(data){
						
						if (data.error == "false") {
							updateStatusLabel(btn.id, "已拒绝");
						}
						else {
							alert("修改审核状态失败：" + data.error_msg);
						}
					}, "json");
				}
			}

			function closeShop(btn)
			{
				if (confirm("确认关闭店铺" + btn.id + "?")) {
					$.post("../php/offlineTrade.php", {"func":"cOls","index":btn.id}, function(data){
						
						if (data.error == "false") {
							updateStatusLabel(btn.id, "已关闭");
						}
						else {
							alert("修改审核状态失败：" + data.error_msg);
						}
					}, "json");
				}
			}

			function reopenShop(btn)
			{
				if (confirm("确认重新开放店铺" + btn.id + "?")) {
					$.post("../php/offlineTrade.php", {"func":"roOls","index":btn.id}, function(data){
						
						if (data.error == "false") {
							updateStatusLabel(btn.id, "重新开放");
						}
						else {
							alert("修改审核状态失败：" + data.error_msg);
						}
					}, "json");
				}	
			}
						
			$(document).ready(function(){
				
				$('#licenceModal').on('show.bs.modal', function (event) {
					
					var button = $(event.relatedTarget);
					var who = button.data('who');
					var src = button.data('whatever');
					
					var modal = $(this);
					modal.find('.modal-title').text(who + "的营业执照");
					document.getElementById("licencePic").src = "../olLicensePic/" + src;
				})
			});
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	        <div>
				<table id="tbl" class="table table-striped" border="1" style="max-width: 1300px; text-align: center;">
					<tr>
						<th>商家id</th>
						<th>用户id</th>
						<th>店名</th>
						<th>联系人</th>
						<th>联系电话</th>
						<th>商家地址</th>
						<th>营业执照</th>
						<th>商家状态</th>
						<th>取现费率</th>
						<th>收款总额</th>
						<th>取现总额</th>
						<th>操作</th>
					</tr>
					<?php
						if ($res) {
							while ($row = mysqli_fetch_assoc($res)) {
					?>
					<tr>
						<td><?php  echo $row["ShopId"]; ?></td>
						<td><?php  echo $row["UserId"]; ?></td>
						<td><?php  echo $row["ShopName"]; ?></td>
						<td><?php  echo $row["Contacter"]; ?></td>
						<td><?php  echo $row["PhoneNum"]; ?></td>
						<td><?php  echo $row["Address"]; ?></td>
						<td><?php if ($row["LicencePic"] == "") {?>
								<span class="text-warning">未上传</span>
							<?php } else { ?>
								<input type="button" value="查看" data-toggle="modal" data-target="#licenceModal" data-who="<?php echo $row["ShopId"];?>" data-whatever="<?php echo $row["LicencePic"]; ?>" >
							<?php } ?> 
						</td>
						<td id="status_<?php echo $row["ShopId"]; ?>"><?php 
								switch ($row["Status"]) {
									case $olshopRegistered: 
							?>
									<span class="text-info">新注册</span>
							<?php
										break;
									case $olshopApplied:
							?>
									<span class="text-warning">提交审核</span>
							<?php
										break;
									case $olshopDeclined:
							?>
									<span class="text-warning">审核失败</span>
							<?php
										break;
									case $olshopAccepted:
							?>
									<span class="text-primary">审核通过</span>
							<?php
										break;
									case $olshopClosed:
							?>
									<span>已下线</span>
							<?php 
										break;
									case $olshopSuspended:
							?>
									<span class="text-danger">店铺查封</span>	
							<?php
										break;
									default:
										echo status;
								}
							?>
						</td>
						<td><?php  echo $row["WdFeeRate"]; ?></td>
						<td><?php  echo $row["TradeAmount"]; ?></td>
						<td><?php  echo $row["WithdrawAmount"]; ?></td>
						<td>
							<div class="btn-group">
								<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
								    操作
							    	<span class="caret"></span>
								</a>
								<ul class="dropdown-menu">
									<?php 
										switch ($row["Status"]) {
											case $olshopRegistered: 
												// do nothing
												break;
											case $olshopApplied:
									?>
									<li><a href="#" id=<?php echo $row["ShopId"]; ?> onclick="onConfirm(this)" >通过</a></li>
									<li><a href="#" id=<?php echo $row["ShopId"]; ?> onclick="onDeny(this)" >不合格</a></li>
									<?php
												break;
											case $olshopDeclined:
												// do nothing
												break;
											case $olshopAccepted:
									?>
									<li><a href="#" id=<?php echo $row["ShopId"]; ?> onclick="closeShop(this)" >下线</a></li>
									<?php
												break;
											case $olshopClosed:
											case $olshopSuspended:
									?>
									<li><a href="#" id=<?php echo $row["ShopId"]; ?> onclick="reopenShop(this)" >重新开业</a></li>
									<?php
												break;
											default:
												echo status;
										}
									?>
									<li><a href="#" id="<?php echo $row['ShopId']; ?>" onclick="checkIncomeRecord(this)">收入记录</a></li>
								</ul>
							</div>
						</td>
					</tr>
					<?php
							}
						}
					?>
				</table>
	        </div>
		</div>	
		
		<div class="modal fade" id="licenceModal" tabindex="-1" role="dialog" aria-labelledby="licenceModalLabel">
			<div class="modal-dialog" role="document">
		    	<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="licenceModalLabel">营业执照</h4>
			    	</div>
					<div class="modal-body" style="text-align: center;">
						<img id="licencePic" src="" style="width: 80%; margin: 0 auto"></img>
			    	</div>
			    </div>
			</div>
		</div>
    </body>
</html>