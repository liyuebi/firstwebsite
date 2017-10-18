<?php
	
include "../php/database.php";
include "../php/constant.php";

$lvl = 0;

$con = connectToDB();
if (!$con) {
	return;
}

session_start();
$userid = $_SESSION["userId"];

$result = mysql_query("select * from ClientTable where UserId='$userid'");
if ($result) {
	$row = mysql_fetch_assoc($result);
}

$numAssoAccount = 0;
$numRefAccount = 0;

$res1 = false;
$res2 = mysql_query("select * from ClientTable where ReferreeId='$userid'");

if ($res2) {
	$numRefAccount = mysql_num_rows($res2);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>分享人员</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){

				if (isNotLoginAndJump()) {
					return;
				}
/*
				document.getElementById("1").style.color = "red";
				
				$('table#tag_table td').click(function(){
					$(this).css('color','red');//点击的设置字色为红色
// 					$(this).css('border-bottom','soild red 1px');//点击的设置为绿色
					$('#tag_table td').not(this).css('color','black');//其他的全部设置为黑色
// 					$('#tag_table td').not(this).css('border-bottom','none');//其他的全部设置为红色

					if ($(this).attr("id") == "1" ) {
						switchToAssociated();
					}
					else {
						switchToFans();
					}
				});
*/
			});
			
			function switchToAssociated()
			{
// 				document.getElementById("blk_asso").style.display = "inline";
				document.getElementById("blk_fans").style.display = "none";
			}
			
			function switchToFans()
			{
// 				document.getElementById("blk_asso").style.display = "none";
				document.getElementById("blk_fans").style.display = "inline";
			}
			
			function switchUser(btn)
			{
				var toUserId = btn.id;
				$.post("../php/login.php", {"func":"switchAccount","to":toUserId}, function(data){
						
					if (data.error == "false") {			
						alert("切换账号成功！");
						location.href = "home.php";
					}
					else {
						alert("切换失败: " + data.error_msg);
					}
				}, "json");
			}
		</script>
	</head>
	<body>
		
		<p>队伍人数： <?php echo 0; ?>人</p>
		<p>直推人数： <?php echo $numRefAccount; ?>人</p>
		
<!--
		<table id="tag_table" class="t2">
			<tr>
				<td id="1" width="50%" >关联账户</th>
				<td id="2" width="50%" >我的蜜粉</th>
			</tr>
		</table>
        
        <div id="blk_asso">
	        <?php
	        if ($res1) {
		        if ($numAssoAccount > 0) {        
	        ?>
        	        <table style="width: 100%; text-align: center;">
				        <tr>
					        <th style="width: 33%;">用户id</th>
					        <th style="width: 33%;">昵称</th>
					        <th style="width: 33%;">切换账号</th>
				        </tr>
			<?php
			        while ($row1 = mysql_fetch_array($res1)) {   
	        ?>
	        			<tr>
					        <td ><?php echo $row1["UserId"]; ?></td>
					        <td ><?php echo $row1["NickName"]; ?></td>
					        <td ><input type="button" value="切换" id=<?php echo $row1["UserId"]; ?>  onclick="switchUser(this)" /></td>
	        			</tr>
	        <?php
		        	}
		    ?>
		        	</table>
 		    <?php
		        }
		        else {
		    ?>
		    	<p>您现在还没有关联账户。</p>
		    <?php
		        }   
	        }
	        ?>
        </div>
-->
        <div id="blk_fans" style="display: block;">
	        <?php
			if ($res2) {
				if ($numRefAccount > 0) {
	        ?>
		        <table id="list" style="width: 100%; text-align: center;">
			        <tr>
<!-- 				        <th style="width: 33%;">用户id</th> -->
				        <th style="width: 50%;">昵称</th>
				        <th style="width: 50%;">手机号</th>
			        </tr>
			<?php
				while ($row2 = mysql_fetch_array($res2)) {
			?>
					<tr>
<!--  						<td><?php echo $row2["UserId"]; ?></td> -->
						<td><?php if ($row2["NickName"] != "") echo $row2["NickName"]; else echo "没有设置"; ?></td>
						<td><?php echo $row2["PhoneNum"]; ?></td>
					</tr>
			<?php
				}
			?>
		        </table>
	        <?php
		        }
		        else {
		    ?>
			    	<p>您现在还没有蜜粉。</p>
		    <?php   
		        }
	        }
	        ?>
        </div>
        
		<div class="footer"> 
			<div>
				<ul class="nav nav-pills" >
					<li style="display:table-cell; width:1%; float: none"><a style="text-align: center;" href="home.php">首页</a></li>
					<li style="display:table-cell; width:1%; float: none" class="active"><a style="text-align: center;" href="#">朋友</a></li>
					<li style="display:table-cell; width:1%; float: none"><a style="text-align: center;" href="me.php">个人中心</a></li>
				</ul>
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>