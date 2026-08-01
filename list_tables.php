<?php
$db = new PDO('mysql:host=localhost;dbname=psnf_drm', 'root', '');
$stmt = $db->query('DESCRIBE leave_requests');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
echo "-----\n";
$stmt = $db->query('DESCRIBE leave_applications');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
