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
	$result = mysql_query("select * from PostTable where IndexId='$idx'");	
	if ($result && mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
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
		</script>
	</head>
	<body style="width: auto;">
		<div style="padding: 10px 10px 0 5px; height: 100%; display:inline; float: left; border-right: 1px solid black;">
			<ul style="list-style: none; padding: 0">
<!-- 				<li><a href="companymgr.html">企业管理</a></li> -->
				<li><a href="productmgr.php">产品管理</a></li>
				<li><a href="usermgr.php">用户管理</a></li>
				<li><a href="ordermgr.php">订单管理</a></li>
				<li><a href="rechargemgr.php">充值管理</a></li>
				<li><a href="withdrawmgr.php">取现管理</a></li>
				<li><a href="configmgr.php">配置管理</a></li>
				<li><a href="statistics.php">统计数据</a></li>
				<li><a href="configRwdRate.php">配置动态拨比</a></li>
				<li><a href="postmgr.php">公告管理</a></li>
				<li><a href="adminmgr.php">管理员账号维护</a></li>
			</ul>
		</div>
		<div style="display: inline; float: left; padding: 10px 0 0 10px;" >
			<div>
				<input id="post_title" type="text" class="form-control" size="60" placeholder="请填入公告标题" value="<?php if ($row) echo $row["Title"]; ?>" />
				<br>
				<textarea id="post_content" placeholder="请输入公告文本" cols="60" rows="20" style="font-size: 18px; padding: 5px;" wrap="soft"><?php 
					if ($row) {
						$filename = $row["TextFile"];
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
				?></textarea>
			</div>
			<div style="margin-top: 10px;">
				<input type="button" class="button button-border button-rounded" value="保存" onclick="<?php if ($idx > 0) echo "edit()"; else echo "save()"; ?>" />
				<input type="button" class="button button-border button-rounded" value="放弃" onclick="discard()" />
			</div>
		</div>
    </body>
</html>