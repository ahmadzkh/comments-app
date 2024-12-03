<?php
      /**
       * @filename fetch_comments.php
       * @author Ahmad Zaky Humami
       * 
       * File ini digunakan untuk mengambil komentar dari database dan mengembalikannya dalam format JSON.
       */
      
      // Koneksi ke database
      try {
            $pdo = new PDO('mysql:host=localhost;dbname=comments_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
      }

      // Ambil komentar dari database
      $stmt = $pdo->query("SELECT username, comment FROM comments ORDER BY id ASC"); // Ganti 'id' dengan nama kolom yang sesuai
      $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Kembalikan komentar dalam format JSON
      header('Content-Type: application/json');
      echo json_encode($comments);
?>