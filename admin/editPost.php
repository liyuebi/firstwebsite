<?php

include "../php/admin_func.php";

if (!checkLoginOrJump()) {
	return;
}

include "../php/database.php";
include "../php/constant.php";

$result = false;
$row = false;
$idx = 0;

if (isset($_GET['idx'])) {
	$idx = $_GET['idx'];
}

$con = connectToDB();
if (!$con)
{
	return false;
}

if ($idx > 0) {
	$result = mysqli_query($con, "select * from PostTable where IndexId='$idx'");	
	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>公告管理</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" type="text/css" href="../css/buttons.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/jquery.form-3.46.0.js" ></script>
		<script type="text/javascript">
			
			function save()
			{
				var title = document.getElementById("post_title").value;
				var content = document.getElementById("post_content").value;
								
				if (title.length <= 0) {
					alert("标题不能为空！");
					return;
				}	
				
				if (content.length <= 0) {
					alert("内容不能为空");
					return;
				}			
				$.post("../php/poster_ctl.php", {"func":"addNew","tle":title,"cont":content}, function(data){
					
					if (data.error == "false") {
						alert("添加成功！");
						location.href = "postmgr.php";
					}
					else {
						alert('添加失败：' + data.error_msg);
					}
				}, "json");
			}
			
			function edit()
			{
				var title = document.getElementById("post_title").value;
				var content = document.getElementById("post_content").value;
				
				if (title.length <= 0) {
					alert("标题不能为空！");
					return;
				}	
				
				if (content.length <= 0) {
					alert("内容不能为空");
					return;
				}
				
				var idx = <?php echo $idx; ?>;
				$.post("../php/poster_ctl.php", {"func":"edit","idx":idx,"tle":title,"cont":content}, function(data){
					
					if (data.error == "false") {
						alert("编辑成功！");
						location.href = "postmgr.php";
					}
					else {
						alert('编辑失败：' + data.error_msg);
					}
				}, "json");
			}
			
			function discard()
			{
				location.href = "postmgr.php";
			}
			
			function filechange(event) 
			{
				var files = event.target.files;
				var file;
				if (files && files.length > 0) {
					file = files[0];
					if (file.size > 1024 * 1024) {
						alert('图片大小不能超过 1MB！');
						return false;
					}
					var url = window.URL || window.webkitURL;
					var imgurl = url.createObjectURL(file);
					document.getElementById("new_pic").src = imgurl;
					document.getElementById("pic").style.display = "none";
					document.getElementById("btn_rmImg").style.display = "inline";
					if (document.getElementById("btn_useOriImg")) {
						document.getElementById("btn_useOriImg").style.display = "none";
					}
					if (document.getElementById("btn_rmOriImg")) {
						document.getElementById("btn_rmOriImg").style.display = "none";
					}
				}
			}
			
			function useOriPic()
			{
				document.getElementById("pic").style.display = "inline";
				if (document.getElementById("btn_useOriImg")) {
					document.getElementById("btn_useOriImg").style.display = "none";
				}
				if (document.getElementById("btn_rmOriImg")) {
					document.getElementById("btn_rmOriImg").style.display = "inline";
				}
				document.getElementById("rmOriImg").value = "0";
			}
			
			function deletePic()
			{
// 				document.getElementById("pic").src = "";
// 				document.getElementById("file").value = "选择图片";

				document.getElementById("pic").style.display = "none";
				if (document.getElementById("btn_useOriImg")) {
					document.getElementById("btn_useOriImg").style.display = "inline";
				}
				if (document.getElementById("btn_rmOriImg")) {
					document.getElementById("btn_rmOriImg").style.display = "none";
				}
				document.getElementById("rmOriImg").value = "1";
			}
			
			function deleteNewPic()
			{
				document.getElementById("file").value = "";
				document.getElementById("new_pic").src = '';
				document.getElementById("pic").style.display = "inline";
				document.getElementById("btn_rmImg").style.display = "none";
				if (document.getElementById("btn_rmOriImg")) {
					document.getElementById("btn_rmOriImg").style.display = "inline";
				}
				document.getElementById("rmOriImg").value = "0";
			}
			
			function trySubmit()
			{
				var options = {
					url:	'../php/poster_ctl.php',
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
					location.href = "postmgr.php";
				}
			}
		</script>
	</head>
	<body style="width: auto;">
		<div style="padding: 10px 0 0 10px;" >
			<form id="post_form" action="../php/poster_ctl.php" enctype="multipart/form-data" method="post" onsubmit="return trySubmit();" >
				<input name='func' type="hidden" value="<?php if ($idx > 0) echo "edit"; else echo "addNew"; ?>" />
				<input name='idx' type="hidden" value="<?php echo $idx; ?>" />
				<input id="post_title" name="title" type="text" class="form-control" size="50" placeholder="请填入公告标题" value="<?php if ($row) echo $row["Title"]; ?>" />
				<hr>
				<textarea id="post_content" name="content" placeholder="请输入公告正文" cols="60" rows="20" style="font-size: 18px; padding: 5px;" wrap="soft"><?php 
					if ($row) {
						$filename = $row["TextFile"];
						if ($filename != '' && $filename != null) {
							$fileNameStr = dirname(__FILE__) . "/../poster/" . $filename;
							if (!file_exists($fileNameStr)) {
								echo "找不到文件！";
							}
							else {
								$file = fopen($fileNameStr, "r");
								if (!$file) {
									echo "打开文件失败！";
									return;
								}
								echo fread($file, filesize($fileNameStr));
								fclose($file);
							}
						}
					}
				?></textarea>
				<hr>
					<img id="pic" src="<?php if ($row && $row['Pic'] != '') echo "/../poster/" . $row["Pic"]; ?>" width="500px"></img>
					<img id="new_pic" src="" width="500px"></img>
					<br>
					<input type="file" id="file" name='file' value="选择图片" accept="image/jpeg,image/png" onchange="filechange(event)" />
					<input type='hidden' id="rmOriImg" name="rmOriImg" value='0' />
					<?php 
						if ($row && $row['Pic'] != '') {
					?>
						<input id="btn_useOriImg" type="button" value="使用原图片" onclick="useOriPic()" style="display: none" />
						<input id="btn_rmOriImg" type="button" value="删除原图片" onclick="deletePic()" />
					<?php
						}
					?>
					<input id="btn_rmImg" type="button" value="<?php if ($row && $row['Pic'] != '') echo "使用原图片"; else echo "删除图片"; ?>" style="display: none;" onclick="deleteNewPic()" />
				<hr>
				<input type="submit" name="submit" class="button button-border button-rounded" value="保存" />
				<input type="button" class="button button-border button-rounded" value="放弃" onclick="discard()" />
			</form>
		</div>
    </body>
</html>