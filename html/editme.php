<?php

session_start();
$userid = $_SESSION["userId"];
$phone = $_SESSION["phonenum"];
$name = $_SESSION["name"];
$idnum = $_SESSION["idnum"]; 

$new = 0;
if (isset($_GET['new'])) {
	$new = $_GET['new'];
}

$noAddress = false;
if ($new) {
	include "../php/database.php";
	$con = connectToDB();
	if ($con)
	{
		$result = mysql_query("select * from Address where UserId='$userid'");
		if ($result) {
			if (mysql_num_rows($result) > 0) {
			}
			else {
				$noAddress = true;
			}
		}
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>编辑我的信息</title>
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
			});

			function onSubmit()
			{
				var nickname = document.getElementById("nickname").value;
				var name = document.getElementById("name").value;
				var idNum = document.getElementById("idNum").value;
				
				if (!isValidUserName(nickname)) {
					alert("无效的昵称，请使用至少4位字母或数字！");
					document.getElementById("nickname").focus();
					return;					
				} 				
				
				if (name == "") {
					alert("姓名不能为空");
					document.getElementById("name").focus();
					return;
				}
				
				// id num can be empty, but if filled, check its validation
				if (idNum != "" && !isIDNumValid(idNum)) {
					alert("输入的身份证号无效，请重新输入！");
					document.getElementById("idNum").focus();
					return;
				}
				
				var oriNickName = document.getElementById("oriNickName").value;
				var oriName = document.getElementById("oriName").value;
				var oriIdNum = document.getElementById("oriIdNum").value;
				
				// 信息没有更改，退出
				if (nickname == oriNickName && name == oriName && idNum == oriIdNum) {
					history.back(-1);
					return;
				}
				else {
					var data = {"func":"editprofile","name":name,"idnum":idNum,"nickname":nickname};
					if (<?php if($noAddress) echo '1'; else echo '0'; ?>) {
						var receiver = document.getElementById("receiver").value;
						var rece_phone = document.getElementById("receiver_phone").value;
						var rece_add = document.getElementById("receiver_add").value;
						var data = {"func":"editprofile","name":name,"idnum":idNum,"nickname":nickname,"receiver":receiver,"rece_phone":rece_phone,"rece_add":rece_add};
					}
					$.post("../php/login.php", data, function(data){
						
						if (data.error == "false") {
							if (<?php echo $new; ?> != 0) {
								if (data.add_address == "failed") {
									
									alert("个人信息修改成功，但地址信息有误，请修改！");
									
									setCookie("editAddress", '2', 0.5);
									setCookie("receiver", document.getElementById("receiver").value, 0.5);
									setCookie("rece_phone", document.getElementById("receiver_phone").value, 0.5);
									setCookie("rece_add", document.getElementById("receiver_add").value, 0.5);

									location.href = "addAddress.php?new=1";
									return;	
								}
								
								alert("设置成功，现在请设置购物密码！");	
								location.href = "setBuyPwd.php?new=1";								
							}
							else {
								alert("设置成功！");	
								location.href = "me.php";
							}
						}
						else {
							alert("设置失败: " + data.error_msg);
						}
					}, "json");
				}
			}
		</script>
	</head>
	
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
		<h3>请更新资料</h3>
        <div>
            <table width="100%" align="center">
	            <tr>
		            <td width="30%" style="text-align: right;">用户ID</td>
		            <td width="70%" style="text-align: left;"><?php echo $_SESSION['userId']; ?></td>
	            </tr>
	            <tr>
		            <td width="30%" style="text-align: right;">手机号</td>
		            <td width="70%" style="text-align: left;"><?php echo "$phone" ?></td>
	            </tr>
	            <tr>
		            <td style="text-align: right;">昵称</td>
		            <td width="70%" style="text-align: left;"><input type="text" id="nickname" value="<?php echo $_SESSION["nickname"];  ?>" onkeypress="return onlyCharAndNum(event)" /></td>
	            </tr>	            
	            <tr>
		            <td style="text-align: right;">姓名</td>
		            <td width="70%" style="text-align: left;"><input type="text" id="name" value="<?php echo "$name" ?>" /></td>
	            </tr>
	            <tr>
		            <td style="text-align: right;">身份证号</td>
		            <td width="70%" style="text-align: left;"><input type="text" id="idNum" value="<?php echo "$idnum" ?>" /></td>
	            </tr>
            </table>
            
            <input type="hidden" id="oriNickName" value="<?php echo $_SESSION["nickname"]; ?> ">
            <input type="hidden" id="oriName" value="<?php echo "$name" ?>" />
            <input type="hidden" id="oriIdNum" value="<?php echo "$idnum" ?>" />
        </div>
        
        <div>
    		<input type="button" value="保存" onclick="onSubmit()" />
			<input type="button" value="取消" onclick="javascript:history.back(-1);"/>
        </div>
            
        <div id="block_add" style="display: <?php if ($noAddress) echo "block"; else echo "none"; ?>;">
	        <hr>
	        <h4>您还没有任何地址信息，可以同时添加:</h4>
            <table width="100%" align="center">
	            <tr>
		            <td width="30%" style="text-align: right;">收件人</td>
		            <td width="70%" style="text-align: left;"><input type="text" id="receiver" value="" placeholder="请填入收件人姓名！" /></td>
	            </tr>
	            <tr>
		            <td style="text-align: right;">收件人电话</td>
		            <td width="70%" style="text-align: left;"><input type="text" id="receiver_phone" value="" placeholder="请填入收件人电话！" onkeypress="return onlyNumber(event)" /></td>
	            </tr>
	            <tr>
		            <td style="text-align: right;">收件人地址</td>
		            <td width="70%" style="text-align: left;"><input type="text" id="receiver_add" value="" placeholder="请填入收件人地址！" /></td>
	            </tr>
            </table>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>