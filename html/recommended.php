<?php
	
include "../php/database.php";
include "../php/constant.php";

$lvl = 0;
$group1 = 0;
$group2 = 0;
$group3 = 0;
$groupId = 0;

$con = connectToDB();
if (!$con) {
	return;
}

session_start();
$userid = $_SESSION["userId"];

$result = mysql_query("select * from User where UserId='$userid'");
if ($result) {
	$row = mysql_fetch_assoc($result);
	$group1 = $row["Group1Cnt"];
	$group2 = $row["Group2Cnt"];
	$group3 = $row["Group3Cnt"];
	$groupId = $row["GroupId"];
}

$res1 = false;
if ($groupId > 0) {
	$res1 = mysql_query("select * from User where GroupId='$groupId' and UserId!='$userid'");
}

$res2 = mysql_query("select * from User where ReferreeId='$userid' and GroupId!='$groupId' and GroupId!=0");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>分享人员</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){

				if (isNotLoginAndJump()) {
					return;
				}
				
				document.getElementById("1").style.color = "red";
 				 				
				var data = 'func=getRecommended';
				$.getJSON("../php/recommend.php", data, function(json){

					if (json.error == "true") {
						
					}	
					else {
						var list = json.list;
						var container = document.getElementById("list");
						var count = 0;
						for (var key in list) {
							
/*
							var time = list[key]["time"];
							var uid = list[key]["id"];
							var phone = list[key]["phone"];
							
							var i = parseInt(time);
							var then = new Date(i * 1000);
							var y = then.getFullYear();
							var m = then.getMonth() + 1;
							var d = then.getDate();
							var h = then.getHours();
							var min = then.getMinutes();
							var str = y + "年" + m + "月" + d + "日" + h + ":" + min;
							
							var n = document.createElement("p");
							n.innerHTML = "推荐用户" + uid + ",手机号为" + phone + ",时间是" + str;
							container.appendChild(n);
							
							++count;
*/
							++count;
							var row = container.insertRow(count);
							var cell1 = row.insertCell(0);
							var cell2 = row.insertCell(1);
							var cell3 = row.insertCell(2);
							cell1.innerHTML = list[key]["id"];
							cell2.innerHTML = list[key]["name"];
							cell3.innerHTML = list[key]["phone"];
						}
						document.getElementById("count").innerHTML = count;
					}				
				});
				
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
			});
			
			function switchToAssociated()
			{
				document.getElementById("blk_asso").style.display = "inline";
				document.getElementById("blk_fans").style.display = "none";
			}
			
			function switchToFans()
			{
				document.getElementById("blk_asso").style.display = "none";
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
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
		<p>团队一：<?php echo $group1; ?>人</p>
		<p>团队二：<?php echo $group2; ?>人</p>
		<?php if ($lvl > $group3StartLvl) { ?>
			<p>团队三：<?php echo $group3; ?>人</p>
		<?php } ?>
		
		<table id="tag_table" class="t2">
			<tr>
				<td id="1" width="50%" >关联账户</th>
				<td id="2" width="50%" >我的蜜粉</th>
			</tr>
		</table>
        
        <div id="blk_asso">
	        <?php
	        if ($res1) {
		        if (mysql_num_rows($res1) > 0) {        
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
	        }
	        ?>
        </div>
        <div id="blk_fans" style="display: none;">
	        <p>您已经推荐了<span id="count">0</span>名蜜粉</p>
	        <table id="list" style="width: 100%; text-align: center;">
		        <tr>
			        <th style="width: 33%;">用户id</th>
			        <th style="width: 33%;">昵称</th>
			        <th style="width: 33%;">手机号</th>
<!-- 			        <td>推荐时间</td> -->
		        </tr>
	        </table>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>