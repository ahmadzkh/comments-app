<?php
      /**
       * @filename submit_comment.php
       * @author Ahmad Zaky Humami\
       * 
       * File ini digunakan untuk menyimpan komentar baru ke dalam database.
       */
      
      // Koneksi ke database
      try {
            $pdo = new PDO('mysql:host=localhost;dbname=comments_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
      }

      // Ambil data JSON dari permintaan
      $data = json_decode(file_get_contents('php://input'), true);
      $username = $data['username'];
      $comment = $data['comment'];

      // Simpan komentar ke database
      $stmt = $pdo->prepare("INSERT INTO comments (username, comment) VALUES (?, ?)");
      $stmt->execute([$username, $comment]);

      // Kembalikan respons
      echo json_encode(['status' => 'success']);
?>