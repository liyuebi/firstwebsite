<?php

include "../php/database.php";
include "../php/constant.php";

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

$userid = $_SESSION["userId"];

$row = false;
$productId = $_GET["product_id"];
$productName = ' ';
$productPrice = ' ';
$productDesc = ' ';
$con = connectToDB();
$countlimit = 0;
if (!$con) {
	return;
}

$leftCount = 0;

$result = mysqli_query($con, "select * from Product where ProductId='$productId'");
if ($result && mysqli_num_rows($result)>0) {
	$row = mysqli_fetch_assoc($result);
	$productName = $row["ProductName"];
	$productPrice = $row["Price"];
	$productDesc = $row["ProductDesc"];
	$countlimit = $row["LimitOneDay"];
}

$dayBought = getDayBoughtCount($con, $userid, $productId);
$leftCount = max(0, $countlimit - $dayBought);

$lvlBought = getLevelBoughtCnt($con, $userid, $_SESSION['lvl'], 0); // use 0 as productId for reinvest

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>产品详情</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		<link rel="stylesheet" type="text/css" href="../css/buttons.css" />
		
		<script src="../js/jquery-1.8.3.min.js"></script>		
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function reduce()
			{
				var node = document.getElementById("count");
				var count = parseInt(node.value);				
				if (count < 1) {
					node.value = 1;
				}
				else if (count == 1) {
					
				}
				else {
					count -= 1;
					node.value = count;
				}
			}
			
			function increase()
			{
				var node = document.getElementById("count");
				var count = parseInt(node.value);
				count += 1;
				node.value = count;
				
			}
			
			function buyItem(e) {
				
				var productId = document.getElementById("product_id").value;
				var countStr = document.getElementById("count").value;
				var count = parseInt(countStr);
				
				if (!isValidNum(count) || count <= 0) {
					alert("选择的数量输入，请重新选择！");
					return;
				}
				if (<?php echo $countlimit; ?> > 0 && count > <?php echo $leftCount; ?>) {
					alert("选择的数量超过今天剩余可以购买的数量，请重新选择！");
					return;
				}
				
				<?php 
					if ($levelReinvestTime[$_SESSION['lvl'] - 1] <= 0) {
						echo "alert('在当前级别不能购买此产品！');";
						echo "return;";						
					}
					if ($levelReinvestTime[$_SESSION['lvl'] - 1] <= $lvlBought) {
						echo "alert('在当前级别您已不能再购买此产品！');";
						echo "return;";
					}
				?>
				
				if (count > <?php echo ($levelReinvestTime[$_SESSION['lvl'] - 1] - $lvlBought); ?>) {
					alert("选择的数量超过当前级别剩余可购买数，请重新选择！");
					return;
				}
				
				setCookie("willbuy", productId, 0.5);
				setCookie("willbuyCount", count, 0.5);
				location.href = "deal.php";
			}
		</script>
	</head>
	
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div id="product_info" style="border-bottom: 1px solid black;">
	        <div style="text-align: center">
		        <img src="../img/product_display/1204575.png" height="200px" />
	        </div>
	        <h3 style="margin-left: 5px;"><?php echo $productName; ?></h3>
	        <h4 style="margin-left: 5px;"><?php echo $productPrice; ?></h4>
	        <?php 
		    if ($countlimit > 0) {
			?>
			<h5 style="margin-left: 5px;">每天可购买<?php echo $countlimit;?>件，您今天还可以购买<?php echo $leftCount; ?>件。</h5>
			<?php
		    }
	        ?>
			<h5 style="margin-left: 5px;">级别在<?php echo $levelName[$_SESSION['lvl'] - 1] ?>可以购买此产品 <?php echo $levelReinvestTime[$_SESSION['lvl'] - 1]; ?> 次，您已购买过 <?php echo $lvlBought; ?> 次。</h5>
	        <input id="product_id" type="hidden" value="<?php echo $productId; ?>" />
	        <input type="button" value="-" onclick="reduce()" />
	        <input id="count" type="text" value="1" />
	        <input type="button" value="+" onclick="increase()" />
	        <br>
	        <input type="button" class="button button-highlight button-rounded" value="购买" style="margin: 10px 0 10px 0;" onclick="buyItem()" />
        </div>
        <div id="detail_info">
	        <h3>商品详情</h3>
	        <p><?php echo $productDesc; ?></p>
        </div>
        <div class="product_frame" width="100px" height="100px"></div>
    </body>
    
</html>