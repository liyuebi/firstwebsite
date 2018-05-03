<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";
include "../php/constant.php";

$result = false;
$productList = array();

$con = connectToDB();
if (!$con)
{
	return false;
}

$result = mysqli_query($con, "select * from ProductPack");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>订单管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function addPack(btn)
			{
				btn.style.display = "none";
				document.getElementById("blk_add").style.display = "block";
				document.getElementById("blk_ori_img").style.display = "none";
			}

			function finishEdit()
			{
				document.getElementById("btn_add").style.display = "inline";
				document.getElementById("blk_add").style.display = "none";	

				document.getElementById("func").value = "addPack";
				document.getElementById("idx").value = 0;
				document.getElementById("name").value = "";
				document.getElementById("price").value = "";
				document.getElementById("rate").value = "";
				document.getElementById("cnt").value = "";
				document.getElementById("blk_ori_img").style.display = "none";
			}

			function trySubmit()
			{
				var options = {
					url:	'../php/product.php',
					dataType: 'json',
					success: afterSubmit
				};
				$('#post_form').ajaxSubmit(options);
				return false;
			}
			
			function afterSubmit(data)
			{
				if (data.error == 'true') {
					alert("失败：" + data.error_msg);
				}	
				else {
					alert("成功!");
					location.reload();
				}
			}

			function changeState(btn)
			{
				var idx = btn.id;

				$.post("../php/product.php", {"func":"cps", "idx":idx}, function(data) {
					
					if (data.error == "false") {

						if ('0' == data.status){
							btn.innerHTML = "上线";
							document.getElementById("status_"+idx).innerHTML = "已下线";
							document.getElementById("status_"+idx).style.color = "red";
						}
						else {
							btn.innerHTML = "下线";
							document.getElementById("status_"+idx).innerHTML = "已上线";
							document.getElementById("status_"+idx).style.color = "red";
						}
					}
					else {
						alert("更改产品状态失败: " + data.error_msg);
					}
				}, "json");					
			}

			function edit(btn)
			{
				var idx = btn.id;

				document.getElementById("btn_add").style.display = "none";
				document.getElementById("blk_add").style.display = "block";

				document.getElementById("func").value = "editPack";
				document.getElementById("idx").value = idx;
				document.getElementById("name").value = document.getElementById("name_" + idx).innerHTML;
				document.getElementById("price").value = document.getElementById("price_" + idx).innerHTML;
				document.getElementById("rate").value = document.getElementById("rate_" + idx).innerHTML;
				document.getElementById("cnt").value = document.getElementById("cnt_" + idx).innerHTML;
				document.getElementById("blk_ori_img").style.display = "block";
				var picCarrier = document.getElementById("check_" + idx);
				document.getElementById("ori_img").src = "../pPackPic/" + picCarrier.getAttribute('data-whatever');
			}

			$(document).ready(function(){
				
				$('#licenceModal').on('show.bs.modal', function (event) {
					
					var button = $(event.relatedTarget);
					var who = button.data('who');
					var src = button.data('whatever');
					
					var modal = $(this);
					modal.find('.modal-title').text("产品包" + who + "的展示图片");
					document.getElementById("licencePic").src = "../pPackPic/" + src;
				})
			});
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
	        <div>
		        <div>
<!--  					<input type="button" value="切换到导出界面" onclick="goToExport()"  /> -->
<!--  					<hr> -->
					<table id="tbl" class="table table-striped" border="1" style="max-width: 600px; text-align: center;">
						<tr>
							<th>产品包Id</th>
							<th>名称</th>
							<th>价格</th>
							<th>存储比率</th>
							<th>产品图片</th>
							<th>剩余数量</th>
							<th>状态</th>
							<th>操作</th>
						</tr>
						<?php
							if ($result) {
								while($row = mysqli_fetch_assoc($result)) {
						?>
								<tr>
									<td><?php echo $row["PackId"]; ?></td>
									<td id="name_<?php echo $row["PackId"];?>"><?php echo $row["PackName"]; ?></td>
									<td id="price_<?php echo $row["PackId"];?>"><?php echo $row["Price"]; ?></td>
									<td id="rate_<?php echo $row["PackId"];?>"><?php echo $row["SaveRate"]; ?></td>
									<td><input id="check_<?php echo $row["PackId"];?>" type="button" value="查看" data-toggle="modal" data-target="#licenceModal" data-who="<?php echo $row["PackId"];?>" data-whatever="<?php echo $row["DisplayImg"]; ?>" ></td>
									<td id="cnt_<?php echo $row["PackId"];?>"><?php if ($row["StockCnt"] >= 0) echo $row["StockCnt"]; else echo 999; ?></td>
									<td id='status_<?php echo $row["PackId"]; ?>'><?php if (0 == $row["Status"]) echo "未上线"; else if (1 == $row["Status"]) echo "已上线";  ?></td>
									<td>
										<div class="btn-group">
											<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
										    	操作
									    		<span class="caret"></span>
											</a>
											<ul class="dropdown-menu">
												<li><a href="#" id=<?php echo $row["PackId"]; ?> onclick="changeState(this)" ><?php if (0 == $row["Status"]) echo '上线'; else echo '下线'; ?></a></li>
												<li><a href="#" id=<?php echo $row["PackId"]; ?> onclick="edit(this)" >修改</a></li>
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
	        <p>
	        	<input id="btn_add" type="button" value="新增产品包" onclick="addPack(this)"  /> 
	        	<div id="blk_add" style="display: none; border: 1px solid black; width: 60%; padding: 10px; margin-right: 5px;" >
					<form id="post_form" action="../php/product.php" enctype="multipart/form-data" method="post" onsubmit="return trySubmit();">
						<input id="func" name='func' type="hidden" value="addPack" />
						<input id="idx" name='idx' type="hidden" value='0' />
<!-- 						<div class="form-group">
							<label>商家编号：</label>
						    <span class="span3"></span>
						</div> -->
						<div class="form-group">
						    <label for="name">产品包名称：</label>
						    <input id='name' name='name' type="text" class="form-control" value="" placeholder="请输入产品包名称" />
						</div>
						<div class="form-group">
						    <label for="price">产品包价格(线上云量)：</label>
							<input id='price' name='price' type="text" class="form-control" value="" placeholder="请输入价格" />
						</div>
						<div class="form-group">
						    <label for="rate">存储比率：</label>
							<input id='rate' name='rate' type="text" class="form-control" value="" placeholder="请输入存储比率"  />
						</div>
<!-- 						<div class="form-group">
						    <label for="phone">地址：</label>
							<input id='add' name='add' type="text" class="form-control" value="" placeholder="请输入店铺地址" style="display: none" />
						</div> -->
						<div class="form-group">
						    <label for="cnt">产品数量：</label>
							<input id='cnt' name='cnt' type="text" class="form-control" value="" placeholder="请输入数量"  />
						</div>
						<div id="blk_ori_img" >
							<label>原图：</label>
							<img id="ori_img" src="" style="max-width: 300px"></img>
						</div>
						<div class="form-group">
						    <label>图片：</label>
						    <input id='file_input' type="file" id="file" name='file' value="选择图片" accept="image/jpeg,image/png;" />
						</div>
						<div id="btns_edit" >
							<button id="btn_submit" type="submit" class="btn btn-success" >提交</button>
							<button type="button" class="btn btn-warning" onclick="finishEdit()">取消编辑</button>
						</div>
					</form>
	        	</div>
	        </p>
		</div>
		<script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
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

    <script src="../js/jquery.form-3.46.0.js" ></script>
</html>