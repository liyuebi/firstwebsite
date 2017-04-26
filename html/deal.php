<?php
session_start();
if (!$_SESSION["isLogin"]) {	
	$home_url = '../index.html';
	header('Location: ' . $home_url);
	exit();
}
$isPayPwdSet = $_SESSION["buypwd"] != "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>订单确认</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js"></script>		
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			$(document).ready(function(){

				var productId = getCookie("willbuy");
				var productCount = getCookie("willbuyCount");
				
				var data1 = 'func=getProductInfo&productid=' + productId;
				$.getJSON("../php/product.php", data1, function(json){
					if (json.error == "true") {
						
					}
					else {
						var link = document.getElementById("product_link");
						link.innerHTML = json.product["name"];
						link.href = "productdetail.php?product_id=" + json.product["productid"];
					}
				});			
				
				var count = document.getElementById("productCount");
				count.innerHTML = "x " + productCount;
				
// 				var e = document.getElementById("info");
				
				var data = 'func=getAddresses';
				$.getJSON("../php/address.php", data, function(json){
					
					var defaultId = json.defAdd;
					var addresses = json.addresses;
					var container = document.getElementById("selectAdd");
					var isThereAddresses = false;
					
					for (var key in addresses) {
						isThereAddresses = true;
						
						var radio = document.createElement("input");
						radio.type = "radio";
						radio.name = "radio";
						radio.var = key;
						container.appendChild(radio);
						
						if (radio.addEventListener) {
							radio.addEventListener('click', selectAddress, false);
						}
						else if (u.attachEvent) {
							radio.attachEvent('onclick', selectAddress);
						}
						
						var text = addresses[key]["receiver"] + " " + addresses[key]["phone"] + " " + addresses[key]["address"] + " " + addresses[key]["zipcode"];
						var k = document.createElement("p");
						k.innerHTML = text;
						k.style.display = "inline";
						k.style.margin = "0 0 5px 5px";
						k.id = "address" + key;
						container.appendChild(k);
						
						var br = document.createElement("br");
						container.appendChild(br);
						
						if (key == defaultId) {
							
							k.style.fontWeight = "bold";
							radio.checked = true;
							document.getElementById("selectAddId").value = defaultId;
						}
					}
					
					if (!isThereAddresses) {
						document.getElementById("selectAddBlock").style.display = "none";
						document.getElementById("selectedAddBlock").style.display = "none";
						document.getElementById("noAddBlock").style.display = "inline";
					}
				});
			});
			
			function changeAddress()
			{
				document.getElementById("selectAddBlock").style.display = "inline";
				document.getElementById("selectedAddBlock").style.display = "none";
				document.getElementById("noAddBlock").style.display = "none";
			}
			
			function selectAddress(e)
			{
				var key = e.target.var;
				var radios = document.getElementsByName("radio");
				if (e.target.checked) {
					for (var i = 0; i < radios.length; ++i)
					{
						radios[i].checked = false;
					}
				}
				
				e.target.checked = true;
				document.getElementById("selectAddId").value = key;
			}
			
			function useAddress()
			{
				var i = document.getElementById("selectedAddress");
				if (null == i)
				{
					return;
				}
				
				var key = document.getElementById("selectAddId").value;
				if (key == "0") {
					alert("请选择地址！");
					return;
				}
				var text = document.getElementById("address" + key).innerHTML;
				i.innerHTML = text;
				
				document.getElementById("addId").value = key;
				
				document.getElementById("selectAddBlock").style.display = "none";
				document.getElementById("selectedAddBlock").style.display = "inline";
				document.getElementById("noAddBlock").style.display = "none";
			}
			
			function useNewAddress()
			{
				location.href = "address.html";
			}
			
			function pay()
			{
				var productId = getCookie("willbuy");
				var count = getCookie("willbuyCount");
				var addressId = document.getElementById("addId").value;
				var paypwd = document.getElementById("paypwd").value;
				$.post("../php/trade.php", {"func":"purchase","productId":productId,"count":count,"addressId":addressId,"paypwd":paypwd}, function(data){
							
					if (data.error == "false") {
						alert("购买成功！");	
// 						location.href = "pwd.php";
					}
					else {
						alert("设置失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function onGotoPayPwd()
			{
				location.href = "pwd.php";
			}
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div id="info" style="border-bottom: 1px solid black;">
	        
	        <h3>产品信息</h3>
			<a id="product_link"></a>	        
<!--
	        		 				var r = document.createElement("a");
		 				r.href = "deal.html?product_id=" + key;
		 				r.innerHTML = json[key]["name"];
		 				r.style.display = "block";
		 				r.style.marginLeft = "5px";
		 				r.style.marginTop = "10px";
		 				q.appendChild(r);
-->
			<p id="productCount"></p>
        </div>
        
        <div id="address" style="border-bottom: 1px solid black;">
	        <input type="hidden" id="addId" value="0" />
	        <h3>请选择地址</h3>
	        <div id="selectedAddBlock" style="display: none">
		        <p id="selectedAddress" style="font-weight: bold; margin: 0 0 5px 5px"></p>
		        <input type="button" value="更换地址" onclick="changeAddress()" />
	        </div>
			<div  id="selectAddBlock" style="display: inline">
				<div id="selectAdd">
				</div>
				<input type="hidden" id="selectAddId" value="0" />
				<input type="button" value="使用选中地址" onclick="useAddress()" />
				<input type="button" value="更改地址" onclick="useNewAddress()" />
				<input type="button" value="使用新地址" onclick="useNewAddress()" />
			</div>
			<div id="noAddBlock" style="display: none">
				<p>您还没有任何地址信息，请先添加地址！</p>
				<input type="button" value="添加地址" onclick="useNewAddress()" />
			</div>
        </div>
        
		<div>
			<div id="blockPay" style="display: <?php if ($isPayPwdSet) echo "inline"; else echo "none"; ?> ;">
				<h3>确认付款</h3>
				请输入支付密码：
				<br>
				<input id="paypwd" type="password" onkeypress="return onlyCharAndNum(event)" />
				<br>
				<input type="button" value="付款" onclick="pay()" />
			</div>
			<div id="blockSetPayPwd" style="display: <?php if (!$isPayPwdSet) echo "inline"; else echo "none"; ?> ;">
				<p>您还没有设置支付密码，请先设置</p>
				<input type="button" value="去设置" onclick="onGotoPayPwd()" />
			</div>
		</div>
    </body>
    <div style="text-align:center;">
    </div>
</html>