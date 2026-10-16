<?php

$host = "db";
$banco = "moiras";
$usuario = "moiras_user";
$senha = "moiras_dev_123";

try {
    $conexao = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $conexao->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $erro) {
    die("Erro ao conectar com o banco de dados.");
}
?>