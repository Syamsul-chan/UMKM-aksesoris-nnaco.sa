<?php

// Wait, I can just write a raw PDO script
$dsn = "mysql:host=localhost;dbname=nnaco_sa";
$user = "root";
$pass = "";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $hash = '$2y$10$tHA4ZJLtTEuz9lE4tdQV6O8g4alm.7NRddmPdDTG04wCukcrhO6.a';
    $stmt = $pdo->prepare("UPDATE users SET password = ?");
    $stmt->execute([$hash]);
    
    echo "Passwords updated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
