<?php
      /**
       * @filename database.php
       * @author Ahmad Zaky Humami
       * 
       * File ini digunakan untuk mengelola koneksi ke database.
       */
      
      $servername = "localhost";
      $username = "root"; // ganti dengan username database Anda
      $password = ""; // ganti dengan password database Anda
      $dbname = "comments_db"; // ganti dengan nama database Anda

      // Koneksi ke MySQL
      $conn = new mysqli($servername, $username, $password, $dbname);

      // Cek koneksi
      if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
      }
?>