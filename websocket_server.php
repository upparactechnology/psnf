<?php
/**
 * Simple Pure PHP WebSocket Server for Live Tracking
 * Uses built-in streams (no php_sockets.dll required)
 * Run via CLI: php websocket_server.php
 */

set_time_limit(0);
ob_implicit_flush();

$host = '0.0.0.0';
$port = 8080;
$internal_port = 8081;

// Public WebSocket listener
$public_server = stream_socket_server("tcp://$host:$port", $errno, $errstr);
if (!$public_server) {
    die("Error starting public server: $errstr ($errno)\n");
}

// Internal Raw TCP listener
$internal_server = stream_socket_server("tcp://127.0.0.1:$internal_port", $errno, $errstr);
if (!$internal_server) {
    die("Error starting internal server: $errstr ($errno)\n");
}

echo "WebSocket Server started on port $port...\n";
echo "Internal Broadcast Server started on 127.0.0.1:$internal_port...\n";

$clients = [$public_server, $internal_server];

while (true) {
    $read = $clients;
    $write = null;
    $except = null;
    
    // Check for available sockets
    if (stream_select($read, $write, $except, 10) < 1) {
        continue;
    }
    
    // New connection on public port
    if (in_array($public_server, $read)) {
        $new_socket = stream_socket_accept($public_server);
        if ($new_socket) {
            $clients[] = $new_socket;
            $header = fread($new_socket, 1024);
            perform_handshake($header, $new_socket, $host, $port);
            echo "New client connected on $port.\n";
        }
        $key = array_search($public_server, $read);
        unset($read[$key]);
    }
    
    // New connection on internal port
    if (in_array($internal_server, $read)) {
        $new_socket = stream_socket_accept($internal_server);
        if ($new_socket) {
            $clients[] = $new_socket;
            echo "New internal client connected on $internal_port.\n";
        }
        $key = array_search($internal_server, $read);
        unset($read[$key]);
    }
    
    // Read from existing clients
    foreach ($read as $read_sock) {
        // Read data
        $data = @fread($read_sock, 2048);
        
        if ($data === false || $data === '') { // Connection closed
            $key = array_search($read_sock, $clients);
            unset($clients[$key]);
            fclose($read_sock);
            echo "Client disconnected.\n";
            continue;
        }
        
        // Check if it's from an internal raw connection
        $is_internal = false;
        $peer_name = stream_socket_get_name($read_sock, true);
        $char = ord($data[0]);
        // Simple heuristic: WebSocket frames start with 0x81 (129)
        if ($char !== 129 && strpos($peer_name, '127.0.0.1') === 0) {
            $is_internal = true;
            $message = $data;
        } else {
            $message = unmask($data);
        }
        
        if (!empty($message)) {
            // Broadcast to all WebSocket clients
            $response = mask($message);
            foreach ($clients as $client) {
                if ($client != $public_server && $client != $internal_server && $client != $read_sock) {
                    @fwrite($client, $response);
                }
            }
        }
        
        if ($is_internal) {
            // Internal connections send one message and disconnect
            $key = array_search($read_sock, $clients);
            unset($clients[$key]);
            fclose($read_sock);
        }
    }
}

// Unmask incoming framed message
function unmask($text) {
    if (strlen($text) < 2) return "";
    $length = ord($text[1]) & 127;
    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }
    $res = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $res .= $data[$i] ^ $masks[$i % 4];
    }
    return $res;
}

// Encode message for transfer to client.
function mask($text) {
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);
    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } else {
        $header = pack('CCNN', $b1, 127, $length);
    }
    return $header . $text;
}

// Handshake
function perform_handshake($received_header, $client_conn, $host, $port) {
    $headers = array();
    $lines = preg_split("/\r\n/", $received_header);
    foreach ($lines as $line) {
        $line = chop($line);
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }
    if (!isset($headers['Sec-WebSocket-Key'])) return;
    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $buffer  = "HTTP/1.1 101 Switching Protocols\r\n" .
               "Upgrade: websocket\r\n" .
               "Connection: Upgrade\r\n" .
               "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    fwrite($client_conn, $buffer);
}
