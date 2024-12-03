<?php
      /**
       * @filename websocket_server.php
       * @author Ahmad Zaky Humami
       * 
       * File ini digunakan untuk mengelola server WebSocket untuk komentar real-time.
       */
      
      // Kode untuk mengatur server WebSocket
      $host = '127.0.0.1';
      $port = 8081;

      // Buat socket
      $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
      if ($socket === false) {
            die("socket_create() gagal: " . socket_strerror(socket_last_error()));
      }

      // Bind socket ke alamat dan port
      if (socket_bind($socket, $host, $port) === false) {
            die("socket_bind() gagal: " . socket_strerror(socket_last_error($socket)));
      }

      // Mulai mendengarkan koneksi
      if (socket_listen($socket, 5) === false) {
            die("socket_listen() gagal: " . socket_strerror(socket_last_error($socket)));
      }

      $clients = [];

      // Koneksi ke database (ganti dengan kredensial yang sesuai)
      try {
            $pdo = new PDO('mysql:host=localhost;dbname=comments_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
      }

      while (true) {
            $changedSockets = $clients;
            $changedSockets[] = $socket;

            // Tunggu socket yang berubah
            $write = null; // Parameter kedua harus berupa variabel
            $except = null; // Parameter ketiga juga harus berupa variabel
            socket_select($changedSockets, $write, $except, 0, 10);

            // Jika ada koneksi baru
            if (in_array($socket, $changedSockets)) {
                  $newSocket = socket_accept($socket);
                  $clients[] = $newSocket;

                  // Lakukan handshake
                  $headers = socket_read($newSocket, 1024);
                  $key = null;
                  if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches)) {
                        $key = $matches[1];
                  }
                  $acceptKey = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
                  $handshake = "HTTP/1.1 101 Switching Protocols\r\n" .
                              "Upgrade: websocket\r\n" .
                              "Connection: Upgrade\r\n" .
                              "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
                  socket_write($newSocket, $handshake, strlen($handshake));

                  unset($changedSockets[array_search($socket, $changedSockets)]);
            }

            // Proses data dari klien
            foreach ($changedSockets as $changedSocket) {
                  $data = socket_read($changedSocket, 1024);
                  if ($data === false) {
                        unset($clients[array_search($changedSocket, $clients)]);
                        continue;
                  }

                  // Proses data komentar
                  $message = json_decode($data, true);
                  $username = $message['username'];
                  $comment = $message['comment'];

                  // Simpan komentar ke database
                  $pdo->prepare("INSERT INTO comments (username, comment) VALUES (?, ?)")->execute([$username, $comment]);

                  // Kirim kembali ke semua klien
                  foreach ($clients as $client) {
                        if ($client !== $changedSocket) {
                        // Kirim komentar ke klien lain
                        $response = json_encode(['username' => $username, 'comment' => $comment]);
                        $encodedResponse = base64_encode($response); // Encode response
                        socket_write($client, $encodedResponse, strlen($encodedResponse));
                        }
                  }
            }
      }

      // Tutup socket
      socket_close($socket);
?>