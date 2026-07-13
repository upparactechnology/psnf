<?php
header('Content-Type: text/html');
echo "<html><body><pre>";
echo shell_exec("python facerecognization/backend/get_ip.py 2>&1");
echo "</pre></body></html>";
