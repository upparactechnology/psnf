<?php
$db = new PDO('mysql:host=localhost;dbname=psnf_drm', 'root', '');
$stmt = $db->query('SELECT * FROM attendance');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
