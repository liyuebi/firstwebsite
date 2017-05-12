<?php

include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("setwechat" == $_POST['func']) {
}
else if ("setalipay" == $_POST['func']) {
}
else if ("setbank" == $_POST['func']) {
}

function setWechat()
{
	
}

function setAlipay()
{
	
}

function setBank()
{
	
}

?>