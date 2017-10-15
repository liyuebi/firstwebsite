<?php

echo $_POST['text'];
echo '<br>';	
echo $_FILES['file']['name'];
echo '<br>';	
echo $_FILES['file']['type'];
echo '<br>';
echo $_FILES['file']['size'];
echo '<br>';
echo $_FILES['file']['tmp_name'];
echo '<br>';
echo $_FILES['file']['error'];
echo '<br>';
echo $_FILES['error'];

$name = $_FILES['file']['name'];
$now = time();
$pos = strpos($name, '.');
$prename = substr($name, 0, $pos);
$postname = substr($name, $pos);
echo $prename;
echo '<br>';
echo $postname;
echo '<br>';

$prename .= '_' . $now;
$new_name = $prename . $postname;
echo $new_name;
$new_name = dirname(__FILE__) . '/../tmp_img/' . $new_name;

move_uploaded_file($_FILES['file']['tmp_name'], $new_name);
	
?>