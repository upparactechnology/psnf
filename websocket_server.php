<?php
/**
 * Simple Pure PHP WebSocket Server for Live Tracking
 * Uses built-in streams (no php_sockets.dll required)
 * Run via CLI: php websocket_server.php
 */

set_time_limit(0);
ob_implicit_flush();

// Bind to loopback by default. Set WS_HOST=0.0.0.0 (or put a wss:// reverse proxy
// in front) to let browsers on other machines subscribe to live tracking.
$host = getenv('WS_HOST') ?: '127.0.0.1';
$maxClients = 500;
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
// Sockets accepted on the internal (loopback-only) port — the ONLY trusted publishers.
// Location updates are published by TransportController after the driver's JWT
// has been verified by the ERP API; browser clients are subscribe-only.
$internal_clients = [];

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
        $new_socket = @stream_socket_accept($public_server, 1);
        if ($new_socket) {
            $header = (string) fread($new_socket, 2048);
            if (count($clients) >= $maxClients || !perform_handshake($header, $new_socket, $host, $port)) {
                fclose($new_socket);
            } else {
                $clients[] = $new_socket;
                echo "New client connected on $port.\n";
            }
        }
        $key = array_search($public_server, $read);
        unset($read[$key]);
    }
    
    // New connection on internal port
    if (in_array($internal_server, $read)) {
        $new_socket = @stream_socket_accept($internal_server, 1);
        if ($new_socket) {
            $clients[] = $new_socket;
            $internal_clients[(int) $new_socket] = true;
            echo "New internal client connected on $internal_port.\n";
        }
        $key = array_search($internal_server, $read);
        unset($read[$key]);
    }
    
    // Read from existing clients
    foreach ($read as $read_sock) {
        // Read data
        $data = @fread($read_sock, 2048);
        
        $is_internal = isset($internal_clients[(int) $read_sock]);

        // Connection closed, or a WebSocket close frame (opcode 0x8) from a browser
        if ($data === false || $data === '' || (!$is_internal && (ord($data[0]) & 0x0F) === 0x8)) {
            $key = array_search($read_sock, $clients);
            unset($clients[$key], $internal_clients[(int) $read_sock]);
            fclose($read_sock);
            echo "Client disconnected.\n";
            continue;
        }

        if (!$is_internal) {
            // Browser (public) clients are subscribe-only: anything they send
            // (pings, junk, forged location frames) is ignored, never rebroadcast.
            continue;
        }

        // Internal publisher: accept only a well-formed JSON object
        $message = trim($data);
        $decoded = json_decode($message, true);
        if (!is_array($decoded) || strlen($message) > 8192) {
            $message = '';
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
            unset($clients[$key], $internal_clients[(int) $read_sock]);
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
function perform_handshake($received_header, $client_conn, $host, $port): bool {
    $headers = array();
    $lines = preg_split("/\r\n/", $received_header);
    foreach ($lines as $line) {
        $line = chop($line);
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[strtolower($matches[1])] = $matches[2];
        }
    }
    if (!isset($headers['sec-websocket-key']) || !preg_match('#^[A-Za-z0-9+/=]{16,64}$#', trim($headers['sec-websocket-key']))) return false;
    $secKey = trim($headers['sec-websocket-key']);
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $buffer  = "HTTP/1.1 101 Switching Protocols\r\n" .
               "Upgrade: websocket\r\n" .
               "Connection: Upgrade\r\n" .
               "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    fwrite($client_conn, $buffer);
    return true;
}
