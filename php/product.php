<?php 

include 'database.php';

if ($_SERVER['REQUEST_METHOD']=="POST") {
	if ("addNew" == $_POST["func"]) {
		addNewProduct();
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
	createProductTable();
	$time = time();
	mysql_query("insert into Product (Price, ProductName, ProductDesc, AddTime) 
		VALUES('$price', '$name', '$desc', '$time')");
}

function getProducts()
{
	$con = connectToDB();
	$result = mysql_query("select * from Product");
	if (!$result) {
		
	}
	else {
		if (0 == mysql_num_rows($result)) {
			
		}
		else {
			$num = mysql_num_rows($result);
			$ret = array();
			while($row = mysql_fetch_array($result))
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
	
	$result = mysql_query("select * from Product where ProductId='$productid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找指定产品失败！'));
		return;
	}
	
	$row = mysql_fetch_array($result);
	$arr = array("productid"=>$row["ProductId"],
					"name"=>$row["ProductName"],
				 	"price"=>$row["Price"],
				 	"icon"=>$row["FirstImg"]);
	echo json_encode(array("error"=>"false", "product"=>$arr));
}

?>