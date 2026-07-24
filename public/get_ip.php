<?php
header('Content-Type: text/plain');
echo "=== How to connect your phone ===\n";
echo "1. Make sure your phone and computer are on the same Wi-Fi network.\n";
echo "2. Find your computer's local IP address.\n";

$ip = '';
// Try to get local IP via socket connection
if (function_exists('socket_create')) {
    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock) {
        if (@socket_connect($sock, '8.8.8.8', 53)) {
            @socket_getsockname($sock, $ip);
        }
        @socket_close($sock);
    }
}

if (!$ip) {
    $ip = gethostbyname(gethostname());
}

echo "Your Computer's Local IP: " . $ip . "\n\n";
echo "3. Update the fallback IP in your Flutter app's services:\n";
echo "   - Open staff_app/lib/services/api_service.dart\n";
echo "   - Replace 'http://localhost/psnf/public' at the end of baseUrl with 'http://" . $ip . "/psnf/public'\n\n";
echo "   - Open driver_app/lib/services/api_service.dart\n";
echo "   - Replace 'http://localhost/psnf/public' at the end of baseUrl with 'http://" . $ip . "/psnf/public'\n";
