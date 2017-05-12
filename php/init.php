<?php

include "database.php";

$con = mysql_connect("127.0.0.1:3306", "root", "123456789");
if (!$con)
{
	echo "Could not connect: " . mysql_error();
	return; 
}

if (mysql_query("create database my_db3", $con)) {
	echo "Database created";
	echo "<br>";
}
else {
	echo "Error creating database: " . mysql_error();
	echo "<br>";
}

$db_selected = mysql_select_db("my_db3", $con);
if (!$db_selected) {
	echo "select db failed: " , mysql_error();
	return;
}

$now = time();

createClientTable();
createCreditTable();
createStatisticsTable();
initGeneralStatisTable();

// 创建产品表，推入第一个产品
$ret = createProductTable();
if ($ret) {
	$res = mysql_query("insert into Product (Price, ProductName, ProductDesc, AddTime, FirstImg)
							values('300', '茗菊春皇菊', '源自天然，传播健康', '$now', '2.jpg')");
	if (!$res) {
		echo "insert default product failed: " . mysql_error();
		echo "<br>";
	}
}

?>