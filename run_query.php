<?php
$conn = new mysqli('127.0.0.1', 'root', '');
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
echo "Connected successfully\n";
$schema = file_get_contents('database/schema.sql');
$conn->multi_query($schema);
while ($conn->next_result()) {;}
$seed = file_get_contents('database/seed.sql');
$conn->multi_query($seed);
while ($conn->next_result()) {;}
echo "Seed executed\n";
?>
