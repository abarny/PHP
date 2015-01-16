<?php

try {
    $pdo = new \PDO(
            'mysql:dbname=td5_gregwar;host=127.0.0.1', 'root', ''
            
    );
} catch (\PDOException $e) {
    header('Content-type: text/plain');
    echo "Impossible de se connecter a la base de donnees\n";
    die($e->getMessage());
}

$pdo->exec('SET CHARSET UTF8');
