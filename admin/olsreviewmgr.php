<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";

$result = false;
$res1 = false;
$productList = array();

$con = connectToDB();
if (!$con)
{
	return false;
}

include "../php/constant.php";
$result = mysqli_query($con, "select * from OfflineShop where Status='$olshopApplied' order by ReadyForCheckTime");
// 	$result = mysqli_query($con, "select * from Transaction");
// $res1 = mysqli_query($con, "select * from Transaction  where Status='$OrderStatusDefault'");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>线下商家审核</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">
			
			function onConfirm(btn)
			{
				if (confirm("确认审核通过？")) {
					btn.disabled = true;
					$.post("../php/offlineTrade.php", {"func":"afo","index":btn.id}, function(data){
						
						if (data.error == "false") {
							document.getElementById("status_" + btn.id).innerHTML = "已上线";
							document.getElementById("status_" + btn.id).style.color = "red";	
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
					btn.disabled = true;
					$.post("../php/offlineTrade.php", {"func":"dfo","index":btn.id}, function(data){
						
						if (data.error == "false") {
							document.getElementById("status_" + btn.id).innerHTML = "已拒绝";
							document.getElementById("status_" + btn.id).style.color = "red";	
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
				<table id="tbl" border="1">
					<tr>
						<th>提审时间</th>
						<th>用户id</th>
						<th>商家id</th>
						<th>店名</th>
						<th>联系人</th>
						<th>联系电话</th>
						<th>商家地址</th>
						<th>营业执照</th>
						<th>操作</th>
						<th>状态</th>
					</tr>
					<?php
						include "../php/constant.php";
						date_default_timezone_set('PRC');
						while($row = mysqli_fetch_assoc($result)) {
					?>
							<tr>
								<td><?php echo date("Y.m.d H:i" ,$row["ReadyForCheckTime"]); ?></td>
								<td><?php echo $row["UserId"]; ?></td>
								<td><?php echo $row["ShopId"]; ?></td>
								<td><?php echo $row["ShopName"]; ?></td>
								<td><?php echo $row["Contacter"]; ?></td>
								<td><?php echo $row["PhoneNum"]; ?></td>
								<td><?php echo $row["Address"]; ?></td>
								<td><input type="button" value="查看营业执照" data-toggle="modal" data-target="#licenceModal" data-who="<?php echo $row["ShopId"]; ?>" data-whatever="<?php echo $row["LicencePic"]; ?>" /></td>
								<td>
									<input type="button" value="通过" id=<?php echo $row["ShopId"]; ?> onclick="onConfirm(this)" />
									<input type="button" value="不合格" id=<?php echo $row["ShopId"]; ?> onclick="onDeny(this)" />
								</td>
								<td id='status_<?php echo $row["ShopId"]; ?>'><?php if ($olshopApplied == $row["Status"]) echo "待审核"; ?></td>
							</tr>
					<?php
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