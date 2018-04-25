<?php 

include 'database.php';

if ($_SERVER['REQUEST_METHOD']=="POST") {
	if ("addNew" == $_POST["func"]) {
		addNewProduct();
	}
	else if ("edit" == $_POST['func']) {
		editProduct();
	}
	else if ("addPack" == $_POST['func']) {
		addProductPack();
	}
	else if ("cps" == $_POST['func']) {
		changePackState();
	}
}
else if ($_SERVER['REQUEST_METHOD']=="GET") {
	if ("getProducts" == $_GET["func"]) {
		getProducts();
	}
	else if ("getProductInfo" == $_GET['func']) {
		getProductInfo();
	}
}


function addNewProduct()
{
	if (!isset($_POST['submit'])) {
		exit("非法访问！");
	}
		
	$name = htmlspecialchars($_POST["productname"]);
	$desc = htmlspecialchars($_POST["productdesc"]);
	$price = htmlspecialchars($_POST["productprice"]);
	
	$con = connectToDB();
	if ($con) {
		createProductTable($con);
		$time = time();
		mysqli_query($con, "insert into Product (Price, ProductName, ProductDesc, AddTime) 
			VALUES('$price', '$name', '$desc', '$time')");
	}
}

function editProduct()
{
	$idx = trim(htmlspecialchars($_POST["id"]));
	$name = trim(htmlspecialchars($_POST["name"]));
	$desc = trim(htmlspecialchars($_POST["desc"]));
	$price = trim(htmlspecialchars($_POST["price"]));
	$limit = trim(htmlspecialchars($_POST["limit"]));	
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$res = mysqli_query($con, "select * from Product where ProductId='$idx'");
	if (!$res || mysqli_num_rows($res) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'查找不到对应产品！'));
		return;
	}
	
	$price = floatval($price);
	$res = mysqli_query($con, "update Product set ProductName='$name', ProductDesc='$desc', Price='$price', LimitOneDay='$limit' where ProductId='$idx'");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'修改产品信息失败！'));
		return;
	}
	
	echo json_encode(array('error'=>'false'));
}

function getProducts()
{
	$con = connectToDB();
	$result = mysqli_query($con, "select * from Product");
	if (!$result) {
		
	}
	else {
		if (0 == mysqli_num_rows($result)) {
			
		}
		else {
			$num = mysqli_num_rows($result);
			$ret = array();
			while($row = mysqli_fetch_assoc($result))
			{
				$arr = array("name"=>$row["ProductName"],
							 	"price"=>$row["Price"],
							 	"icon"=>$row["FirstImg"]);
			 	$ret[$row["ProductId"]] = $arr;
			}
			echo json_encode($ret);
		}
	}
	
}

function getProductInfo()
{
	$productid = htmlspecialchars($_GET["productid"]);
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	
	$result = mysqli_query($con, "select * from Product where ProductId='$productid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找指定产品失败！'));
		return;
	}
	
	$row = mysqli_fetch_assoc($result);
	$arr = array("productid"=>$row["ProductId"],
					"name"=>$row["ProductName"],
				 	"price"=>$row["Price"],
				 	"icon"=>$row["FirstImg"]);
	echo json_encode(array("error"=>"false", "product"=>$arr));
}

function addProductPack()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	include_once "admin_func.php";
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$name = trim(htmlspecialchars($_POST["name"]));
	$price = trim(htmlspecialchars($_POST["price"]));
	$rate = trim(htmlspecialchars($_POST["rate"]));
	$cnt = trim(htmlspecialchars($_POST['cnt']));
	$imgfile = $_FILES['file'];

	if ($name == "") {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'产品包名称不能为空！'));
		return;
	}
	if (!isValidNum($price)) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'无效的价格，请重新输入！'));
		return;	
	}
	if (!isVaildDecimal($rate)) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'无效的存储比率，请重新输入！'));
		return;	
	}
	if (!isValidNum($cnt)) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'无效的产品包数量，请重新输入！'));
		return;	
	}
	
	$now = time();
		
	$imgFileName = '';
	if ($imgfile != '') {
		
		$imgType = $imgfile['type'];
		$imgSize = $imgfile['size'];
		$error = $imgfile['error'];
		
		if ($error != 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'上传图片出错，出错码是 ' . $error));
			return;
		}
		
		if ($imgSize > 1024 * 1024) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'图片大小不能超过1MB！'));
			return;
		}
		
		if ($imgType != 'image/jpeg' && $imgType != 'image/png') {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'图片格式不对，请使用jpg或png格式的图片，使用图片的格式是' . $imgType));
			return;
		}
		
		$imgname = $imgfile['name'];
		$pos = strpos($imgname, '.');
		$postname = substr($imgname, $pos);
		
		$imgFileName = 'pack_' . $now . $postname;
		
		include_once "func.php";

		$new_path = dirname(__FILE__) . '/../pPackPic';
		if (createFolderIfNotExist($new_path)) {
			$new_name = $new_path . '/' . $imgFileName;
			move_uploaded_file($imgfile['tmp_name'], $new_name);
		}
		else {
			$imgFileName = '';
		}
	}
	else {
		echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'产品包图片不能为空！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$result = createProductPackTable($con);
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建表失败，请稍后重试！'));
			return;		
		}

		$userid = $_SESSION['userId'];
		
		$res = mysqli_query($con, "select * from ProductPack where PackName='$name'");
		if ($res && mysqli_num_rows($res) > 0) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'产品包名称已经被使用了，请更换一个！'));
			return;
		}	
		
		$res1 = mysqli_query($con, "insert into ProductPack (Price, PackName, SaveRate, DisplayImg, StockCnt, AddTime)	
													values('$price', '$name', '$rate', '$imgFileName', '$cnt', '$now')");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'编辑失败，请稍后重试！','sql_error'=>mysqli_error($con)));
			return;
		}
 	}
	
	echo json_encode(array('error'=>'false'));	
}

function changePackState()
{
	include 'regtest.php';
	include 'constant.php';
	
	session_start();
	include_once "admin_func.php";
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}

	$idx = $_POST["idx"];

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else 
	{
		$result = createProductPackTable($con);
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'建表失败，请稍后重试！'));
			return;		
		}

		$res = mysqli_query($con, "select * from ProductPack where PackId='$idx'");
		if (!$res || mysqli_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'查找产品包失败，请稍后重试！'));
			return;			
		}

		$row = mysqli_fetch_assoc($res);
		$status = $row["Status"];

		$status = ($status + 1) % 2;
		$res = mysqli_query($con, "update ProductPack set Status='$status' where PackId='$idx'");
		if (!$res) {
			echo json_encode(array('error'=>'true','error_code'=>'33','error_msg'=>'更新产品包状态失败，请稍后重试！'));
			return;				
		}
	}


	echo json_encode(array('error'=>'false','idx'=>$idx,'status'=>$status));
}

?>