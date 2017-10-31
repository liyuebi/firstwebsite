<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";
include "../php/constant.php";

$result = false;

$con = connectToDB();
if (!$con)
{
	return false;
}

$result = mysql_query("select * from PostTable order by AddTime desc");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>添加新公告</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap-theme.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function addNew()
			{
				location.href = "editPost.php";
			}
			
			function edit(btn)
			{
				location.href = "editPost.php?idx=" + btn.name; 
			}
			
			function post(btn)
			{
				$.post("../php/poster_ctl.php", {"func":"post","idx":btn.name}, function(data){
					
					if (data.error == "false") {
						alert("发布成功！");
						document.getElementById("status_" + data.idx).innerHTML = "已上线";
						var btn = document.getElementById("btn_" + data.idx);
// 						btn.value = "下线";
						btn.disabled = true;
					}
					else {
						alert('发布失败：' + data.error_msg);
					}
				}, "json");
			}
			
			function unpost(btn)
			{
				$.post("../php/poster_ctl.php", {"func":"unpost","idx":btn.name}, function(data){
					
					if (data.error == "false") {
						alert("下线成功！");
						document.getElementById("status_" + data.idx).innerHTML = "已下线";
						var btn = document.getElementById("btn_" + data.idx);
// 						btn.value = "重新发布";
						btn.disabled = true;
					}
					else {
						alert('下线失败：' + data.error_msg);
					}
				}, "json");				
			}
			
			function deletePost(btn) 
			{
				$.post("../php/poster_ctl.php", {"func":"delete","idx":btn.name}, function(data){
					
					if (data.error == "false") {
						alert("删除成功！");
						document.getElementById("status_" + data.idx).innerHTML = "已删除";
						document.getElementById("status_" + data.idx).style.color = "red";
						var btn = document.getElementById("btn_" + data.idx);
// 						btn.value = "下线";
						if (btn) {
							btn.disabled = true;
						}
					}
					else {
						alert('删除失败：' + data.error_msg);
					}
				}, "json");
	
			}
		</script>
	</head>
	<body>
		<div style="padding: 10px 0 0 10px;" >
			<div>
				<table border="1" style="text-align: center;">
					<tr>
						<th style="text-align: center;">标题</th>
						<th style="text-align: center;">创建时间</th>
						<th style="text-align: center;">修改时间</th>
						<th style="text-align: center;">状态</th>
						<th style="text-align: center;">操作</th>
					</tr>
				<?php
					if ($result) {
						date_default_timezone_set('PRC');
						while ($row = mysql_fetch_array($result)) {
				?>
					<tr>
						<td><?php echo $row["Title"]; ?></td>
						<td><?php echo date("Y.m.d H:i" ,$row["AddTime"]); ?></td>
						<td><?php if ($row["LMT"] > 0) echo date("Y.m.d H:i" ,$row["LMT"]); ?></td>
						<td id="status_<?php echo $row["IndexId"]; ?>">
						<?php 
							if ($row["Status"] == $postStatusWait) 
								echo "准备"; 
							else if ($row["Status"] == $postStatusOnline) 
								echo "已发布";
							else if ($row["Status"] == $postStatusDown) 
								echo "已下线";	  
						?>
						</td>
						<td id="oper_<?php echo $row["IndexId"]; ?>" style="padding: 3px;" >
						<?php
							if ($row["Status"] == $postStatusWait) {
						?>
								<input type="button" class="btn btn-sm btn-default" name="<?php echo $row["IndexId"]; ?>" value="编辑" onclick="edit(this)" />
								<input type="button" class="btn btn-sm btn-default" name="<?php echo $row["IndexId"]; ?>" id="btn_<?php echo $row["IndexId"]; ?>" value="发布" onclick="post(this)" />
						<?php
							}
							else if ($row["Status"] == $postStatusOnline) {
						?>
								<input type="button" class="btn btn-sm btn-default" name="<?php echo $row["IndexId"]; ?>" value="编辑" onclick="edit(this)" />
								<input type="button" class="btn btn-sm btn-default" name="<?php echo $row["IndexId"]; ?>" id="btn_<?php echo $row["IndexId"]; ?>" value="下线" onclick="unpost(this)" />
						<?php
							}
							else if ($row["Status"] == $postStatusDown) {
						?>
								<input type="button" class="btn btn-sm btn-default" name="<?php echo $row["IndexId"]; ?>" value="编辑" onclick="edit(this)" />
								<input type="button" class="btn btn-sm btn-default" name="<?php echo $row["IndexId"]; ?>" id="btn_<?php echo $row["IndexId"]; ?>" value="重新发布" onclick="post(this)" />
						<?php
							}
						?>
							<input type="button" class="btn btn-sm btn-danger" name="<?php echo $row["IndexId"]; ?>" value="删除" onclick="deletePost(this)" />
						</td>
					</tr>
				<?php
						}
					}
				?>
				</table>
			</div>
			<div>
				<hr>
				<input type="button" class="btn btn-lg btn-primary" value="添加新公告" onclick="addNew()" />
			</div>
		</div>
    </body>
</html>