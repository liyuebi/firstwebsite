<?php

include "../php/constant.php";
include "../php/database.php";

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	// no code here, just continue;		
} 
else {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

$mycredit = 0;
$userid = $_SESSION["userId"];
$paypwd = $_SESSION["buypwd"];

$result = false;
$res = false;
$hasPack = false;

$con = connectToDB();
if ($con) {
	$result = mysqli_query($con, "select * from Credit where UserId='$userid'");
	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$mycredit = $row["ShareCredit"];
	}

	$res = mysqli_query($con, "select * from ProductPack where Status>0");
	if ($res && mysqli_num_rows($res) > 0) {
		$hasPack = true;
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>推荐</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<!-- CSS -->
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" href="../css/mystyle1.0.1.css">
		<link rel="stylesheet" href="../css/buttons.css">
	
		<script src="../js/jquery-3.2.1.min.js"></script>
        <script src="../js/scripts.js" ></script>	
        <script src="../js/md5.js" ></script>
        <script src="../js/bootstrap-3.3.7/bootstrap.min.js"></script>
		<script type="text/javascript">
			
			var packId = -1;
			var isBuyingPack = true;
			var hasPack = <?php if ($hasPack) echo "true"; else echo "false"; ?>;

			function onRegister()
			{
				var phonenum = document.getElementById("phonenum").value;
				var num = document.getElementById("investnum").value;
				var paypwd = document.getElementById("paypwd").value;
				var isOlShop = false; // document.getElementById("ols_check").checked;

				phonenum=$.trim(phonenum);
				num=$.trim(num);
				if (!isPhoneNumValid(phonenum)) {
					alert("无效的电话号码！");
					return;
				}
				if (paypwd == "") {
					alert("无效的支付密码！");
					return;
				}

				var fromPack = 0;
				if (hasPack && isBuyingPack) {
					num = '0';
					fromPack = 1;
				}
				
				if (isOlShop && !confirm("确定要推荐为线下商家账号，需要额外使用<?php echo $offlineShopRegisterFee; ?>分享云量？")) {
					return;
				}
				if (isOlShop) {
					isOlShop = 1;
				}
				else {
					isOlShop = 0;
				}

				paypwd = md5(paypwd);
				$.post("../php/register.php", {"phonenum":phonenum, "quantity":num, "pack":fromPack, "pId":packId, "olShop":isOlShop, "paypwd":paypwd}, function(data){
					
					if (data.error == "false") {
						alert("注册成功！");// \n新用户的ids是" + data.new_user_id);	
						document.getElementById("phonenum").value = "";
						document.getElementById("investnum").value = "";
						document.getElementById("paypwd").value = "";
					}
					else {
						alert("注册失败: " + data.error_msg);
					}
				}, "json");
			}
			
			function getTestKey()
			{
					
			}

			function ChooseItem(item)
			{
				document.getElementById("label_sel").style.display = "none";
				document.getElementById("blk_seled").style.display = "block";	
				document.getElementById("sel_name").innerHTML = item.getAttribute('data-name');

				var parent = item.parentNode;
				var brothers = parent.children;
				for (var i = 0; i < brothers.length; ++i) {
					brothers[i].style.border = "";
				}
				item.style.border = "2px solid blue";

				packId = item.getAttribute('data-who');
			}

			function showPackBlk(show)
			{
				if (show) {
					document.getElementById("blk_amt").style.display = "none";
					document.getElementById("blk_pack").style.display = "block";
				}
				else {
					document.getElementById("blk_amt").style.display = "block";
					document.getElementById("blk_pack").style.display = "none";
				}
			}
			
			function goSetPayPwd()
			{
				location.href = "setBuyPwd.php";
			}
			
			function goCharge()
			{
				location.href = "exchange.php";
			}
			
			function goback()
			{
				location.href = "home.php";
			}

			$(document).ready(function(){

				$("#nav-tabs li").on("click",function(){
 
		            isBuyingPack = $(this).index() == 0;
		        });
		    });
			
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">分享云粉</h3></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>
		
        <div style="margin: 10px 3px 0 3px;"> 
	        <div>
		        <!-- <h4 class="text-warning" style="">推荐</h4> -->
		        <p>   
		            <input type="text" class="form-control" id="phonenum" name="phonenum" placeholder="请输入新用户的电话号码" onkeypress="return onlyNumber(event)" />
		        </p>

				<?php if ($hasPack) { ?>		        
		        <ul class="nav nav-tabs" id="nav-tabs">
		        	<li class="active"><a href="#pack" data-toggle="tab">云粉产品区</a></li>
					<li>
				    	<a href="#amt" data-toggle="tab">使用分享云量</a>
					</li>
				</ul>
				<?php } ?> 
			    <div class="tab-content well">  
			     	<div class="tab-pane fade<?php if (!$hasPack) echo " in active"; ?>" id="amt">  
		            <!-- <p id="blk_amt"> -->
		            	<input type="text" class="form-control" id="investnum" name="investnum" placeholder="请输入存储数额" onkeypress="return onlyNumber(event)" />
		            	<span class="help-block">注册用户可存储分享云量资产（必须是<strong>100</strong>的倍数）</b>，您可使用的分享云量为 <strong><?php echo $mycredit; ?></strong></span>
		            </div>
				<?php if ($hasPack) { ?>		        
		            <div class="tab-pane fade in active" id="pack">
		            	<label id="label_sel">请选择产品包：</label>
		            	<div id="blk_seled" style="display: none">
			            	<label id="label_selected" >选中：<span id="sel_name" class="text-primary"></span></label>
			            </div>
		            	<div id="blk_sel_pack" style="display: -webkit-flex; display: flex; flex-wrap: wrap; justify-content: space-between;">
		            		<?php 
		            		while ($row = mysqli_fetch_assoc($res)) {
		            		?>
		            			<div id="<?php echo $row["PackId"]; ?>" onclick="ChooseItem(this)" style=" background: #e1dede; margin: 1px 0.7%; padding: 5px; width: 48%;" data-who="<?php echo $row["PackId"]; ?>" data-name="<?php echo $row['PackName']; ?>">
									<div class="img_container" align="center" style="text-align: center; max-width: 98%;">
										<img src="<?php if ($row["DisplayImg"] != "") echo "../pPackPic/" . $row["DisplayImg"]; ?>" style="max-width: 100%;"></img>
									</div>
									<h4 class="text-warning"><?php echo $row["PackName"]; ?></h4>
									<div style="display: -webkit-flex; display: flex; justify-content: space-between;">
										<span class="text-info">价格：<?php echo $row["Price"]; ?> 分享云量</span>
										<span class="text-info">存储比例：<?php echo $row["SaveRate"]; ?></span>
									</div>
									<div>
										<span class="text-info">库存：<?php if ($row["StockCnt"] == -1)  echo "999"; else echo $row["StockCnt"]; ?></span>
									</div>
		            			</div>
		            		<?php
		            		}
		            		?>
		            	</div>
		            	<!-- <button class="btn btn-info" style="display: block">选择产品包</button> -->
		            	<!-- <button class="btn btn-info" style="display: none">更改产品包</button> -->
		            </div>
		        <?php } ?>
		        </div>
	<!--             <input type="Captcha" class="form-control" id="Captcha" name="Captcha" style="width: 70%; display: inline-block;" placeholder="请输入验证码！"/> -->
	<!--             <input type="button" class="button-rounded" name="test" onclick="getTestKey()" style="width: 28%; height: 30px;" value="获取验证码" ／> -->
	<!-- 			<br> -->
				<p>
					
<!-- 				<p class="checkbox">
				    <label>
				    	<input type="checkbox" id="ols_check"> 注册为线下商家账号
				    </label>
				    <span class="help-block">注册线下商家账号需要额外使用分享云量<strong><?php echo $offlineShopRegisterFee; ?></strong></span>
				</p> -->
					<label>确认注册：</label>
					<?php
						if ($paypwd == "") {		
					?>
					<p class="text-danger" style="margin-bottom: 0;">您的支付密码还没有设置</p>
					<input type="button" class="button-rounded" style="width: 45%; height: 30px; display: block; margin: 20px 0;" name="submit" value="设置支付密码！" onclick="goSetPayPwd()" />
					<?php
						}
						else {
					?>
					<input type="password" class="form-control" id="paypwd" name="paypwd" placeholder="请输入您的支付密码！" />
					<input type="button" class="btn btn-primary btn-block" style="margin: 10px 0;" name="submit" value="注册" onclick="onRegister()" />
					<?php
						}
					?>
				</p>
			</div>
			
	        <div>
		        <hr>
		        <h4 class="text-warning" style="">注意事项</h4>
			    <p style="margin: 0;">1. 用户默认登录密码被设置为000000，请用户登录后及时修改成新密码</p>
			    <p style="margin: 0;">2. 请用户登录后完善信息</p>
			    <!-- <p style="margin: 0;">3. 注册为线下商家账号后，请去 <strong>线下商家->我的商家</strong> 完善商家信息。</p> -->
			    <p style="margin: 0;">3. 须注册商家号请前往“线下商家->我的商家”注册</p>
			    <?php if ($hasPack) { ?>
			    <p style="margin: 0;">4. 购买产品包赠送相应数额分享云量存储额的指定比率额度成为云粉。</p>
			    <?php } ?>
	        </div>
        </div>
    </body>
</html>
