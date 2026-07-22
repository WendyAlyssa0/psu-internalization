<?php


$password = "paassword";
$ph = password_hash($password, PASSWORD_DEFAULT);
echo "<h2>Generated Password Hash</h2>";
echo "<textarea rows='5' cols='100'>";
echo $ph;
echo "</textarea>";

?>