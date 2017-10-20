<?php
	
$new = 0;
if (isset($_GET['new'])) {
	$new = $_GET['new'];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>添加地址</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			$(document).ready(function(){
				if (!isLogined()) {
 					location.href = "pleaselogin.html";
				}
 				else {
	 				var u = getCookie("editAddress");
	 				if ('0' == u) {
	 					// do nothing
	 				}
	 				else if ('1' == u) {
		 				
		 				document.getElementById("func").value = "editaddress";
		 				var a = getCookie("receiver");
		 				var b = getCookie("rece_phone");
		 				var c = getCookie("rece_add");
// 		 				var d = getCookie("rece_zip");
		 				
		 				document.getElementById("receiver").value = a;
		 				document.getElementById("phonenum").value = b;
		 				document.getElementById("address").value = c;
// 		 				document.getElementById("zipcode").value = d;
		 				document.getElementById("addressid").value = getCookie("editAddressId");
		 				
		 				deleteCookie("editAddress");
		 				deleteCookie("editAddressId");
		 				deleteCookie("receiver");
		 				deleteCookie("rece_phone");
		 				deleteCookie("rece_add");
// 		 				deleteCookie("rece_zip");
	 				}
	 				else if ('2' == u) {
		 				
		 				var a = getCookie("receiver");
		 				var b = getCookie("rece_phone");
		 				var c = getCookie("rece_add");
// 		 				var d = getCookie("rece_zip");
		 				
		 				document.getElementById("receiver").value = a;
		 				document.getElementById("phonenum").value = b;
		 				document.getElementById("address").value = c;
// 		 				document.getElementById("zipcode").value = d;
		 				
		 				deleteCookie("editAddress");
		 				deleteCookie("receiver");
		 				deleteCookie("rece_phone");
		 				deleteCookie("rece_add");
// 		 				deleteCookie("rece_zip");
	 				}
 				}	
			});
			

			function submitAddress()
			{				
				// check phone num
				var text = document.getElementById("phonenum").value;
				text = $.trim(text);
				var val = isPhoneNumValid(text);
				if (!val) {
					document.getElementById("phonenum").focus();
					alert("无效的电话号码，请重新输入!");
					return;
				}
				
				// check address, assume length can't be less than 8 chars
				var add = document.getElementById("address").value;
				add = $.trim(add);
				if (add.length < 6) {
					document.getElementById("address").focus();
					alert("无效的地址，请重新输入!");
					return;
				}
				
				// check name
				var name = document.getElementById("receiver").value;
				name = $.trim(name);
				if (name == '') {
					document.getElementById("receiver").focus();
					alert("无效的收件人姓名，请重新输入!");
					return;
				}
				
				var isDefault = '0';
				if (document.getElementById("default").checked) {
					isDefault = '1';
				}
				
				var func = document.getElementById("func").value;
				var addressId = document.getElementById('addressid').value;
				var data = {"func":func,"addressid":addressId,"receiver":name,"phonenum":text,"address":add,"default":isDefault};
				$.post("../php/address.php", data, function(data){
					
					if (data.error == "false") {
						if (<?php echo $new; ?> != 0) {							
							alert("地址添加成功，现在请设置购物密码！");	
							location.href = "setBuyPwd.php?new=1";								
						}
						else {
							alert("设置成功！");	
							location.href = "address.html";
						}
					}
					else {
						alert("设置失败: " + data.error_msg);
					}
				}, "json");
			}

		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>连物网</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div>
	       <input id="func" type="hidden" name="func" value="addaddress" />
	       <input id="addressid" type="hidden" name="addressid" value="-1" />
           <table width="100%" align="center">
	            <tr>
		            <td width="20%" style="text-align: right;">收件人</td>
		            <td width="80%" style="text-align: left;"><input type="text" id="receiver" value="" placeholder="请填入收件人姓名！" /></td>
	            </tr>
	            <tr>
		            <td style="text-align: right;">收件人电话</td>
		            <td width="80%" style="text-align: left;"><input type="text" id="phonenum" value="" placeholder="请填入收件人电话！" onkeypress="return onlyNumber(event)" /></td>
	            </tr>
	            <tr>
		            <td style="text-align: right;">收件人地址</td>
		            <td width="80%" style="text-align: left;"><input type="text" id="address" value="" placeholder="请填入收件人地址！" /></td>
	            </tr>
            </table>
            <input id="default" type="checkbox" name="default" checked="checked" value="default" /> 设为默认地址
            <hr>
            <input id="btnSave" type="button" name="submit" value="保存" onclick="submitAddress()"/>
        </div>
        
    </body>
    <div style="text-align:center;">
    </div>
</html>