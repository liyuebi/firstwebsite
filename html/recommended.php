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
$numTeam = 0;
$numRefAccount = 0;

$result = mysql_query("select * from ClientTable where UserId='$userid'");
if (!$result || mysql_num_rows($result) <= 0) {
	// !!! log error
}
else {
	$row = mysql_fetch_assoc($result);
	$numTeam = $row["ChildCnt"];
	$numRefAccount = $row["RecoCnt"];	
}

$res1 = false;
$res2 = mysql_query("select * from ClientTable where ReferreeId='$userid'");

if ($res2) {
	
	$cnt = mysql_num_rows($res2);
	if ($cnt != $numRefAccount) {
		// !!! log error
	}
	$numRefAccount = $cnt;
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
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){

				if (isNotLoginAndJump()) {
					return;
				}

				$('#main').height($(window).height() - $('#title').height() - $('#bar'
					).height());
				
				$(window).resize(function() {
					$('#main').height($(window).height() - $('#title').height() - $('#bar'
						).height());
				});
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
		<div id="title" align="center" style="background-color: rgba(0, 0, 255, 0.32); height: 50px; font-size: 20; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">
			<h3 style="line-height: 50px;">好友</h3>
		</div>
		<div id="main" class="big_frame" style="overflow-y: scroll;">
		
			<div class="container-fluid" style="margin-top: 5px;">
				<div class="row">
					<div class="col-xs-6 col-md-6"><h4>队友人数： <?php echo $numTeam; ?></h4></div>
					<div class="col-xs-6 col-md-6"><h4>分享人数： <?php echo $numRefAccount; ?></h4></div>
				</div>
			</div>
			
	        <div id="blk_fans" style="display: block; margin-top: 5px;">
		        <?php
				if ($res2) {
					if ($numRefAccount > 0) {
		        ?>
			        <table id="list" class="table table-striped" style="width: 100%; text-align: center;">
				        <tr>
	<!-- 				        <th style="width: 33%;">用户id</th> -->
					        <th style="width: 50%; text-align: center;">昵称</th>
					        <th style="width: 50%; text-align: center;">手机号</th>
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
				    	<p>您现在还没有分享。</p>
			    <?php   
			        }
		        }
		        ?>
	        </div>
		</div>
		<div id="bar" class="footer"> 
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