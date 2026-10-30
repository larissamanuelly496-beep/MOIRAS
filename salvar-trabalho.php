<?php
session_start();

require_once "conexao.php";

// Verifica se o usuário está logado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

// Aceita somente envio pelo formulário
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: adicionar-trabalho.php");
    exit;
}

// Recebe os dados
$usuario_id = (int) $_SESSION["usuario_id"];
$titulo = trim($_POST["titulo"] ?? "");
$categoria = trim($_POST["categoria"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");

// Valida os campos
if ($titulo === "" || $categoria === "") {
    exit("Preencha o título e a categoria.");
}

if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
    exit("Erro ao enviar a foto. Tente novamente.");
}

$foto = $_FILES["foto"];

// Limite de 5 MB
if ($foto["size"] > 5 * 1024 * 1024) {
    exit("A foto deve ter no máximo 5 MB.");
}

// Verifica o tipo real da imagem
$imagem = getimagesize($foto["tmp_name"]);

if ($imagem === false) {
    exit("O arquivo enviado não é uma imagem válida.");
}

$tiposPermitidos = [
    IMAGETYPE_JPEG => "jpg",
    IMAGETYPE_PNG => "png",
    IMAGETYPE_WEBP => "webp"
];

$tipoImagem = $imagem[2];

if (!isset($tiposPermitidos[$tipoImagem])) {
    exit("Formato não permitido. Use JPG, PNG ou WEBP.");
}

// Cria a pasta para guardar as fotos
$pasta = __DIR__ . "/uploads/trabalhos/";

if (!is_dir($pasta)) {
    if (!mkdir($pasta, 0755, true)) {
        exit("Não foi possível criar a pasta de fotos.");
    }
}

// Gera um nome único para a foto
$nomeFoto = bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$tipoImagem];

$caminhoCompleto = $pasta . $nomeFoto;
$caminhoBanco = "uploads/trabalhos/" . $nomeFoto;

// Move a foto para a pasta definitiva
if (!move_uploaded_file($foto["tmp_name"], $caminhoCompleto)) {
    exit("Não foi possível salvar a foto no servidor.");
}

try {
    $sql = "INSERT INTO trabalhos
            (usuario_id, titulo, descricao, categoria, foto)
            VALUES
            (:usuario_id, :titulo, :descricao, :categoria, :foto)";

    $stmt = $conexao->prepare($sql);

    $stmt->execute([
        ":usuario_id" => $usuario_id,
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":categoria" => $categoria,
        ":foto" => $caminhoBanco
    ]);

    header("Location: perfil.php?trabalho=salvo");
    exit;

} catch (PDOException $e) {
    // Remove a foto caso o cadastro no banco falhe
    if (file_exists($caminhoCompleto)) {
        unlink($caminhoCompleto);
    }

    error_log("Erro ao salvar trabalho: " . $e->getMessage());
    exit("Não foi possível publicar o trabalho. Verifique os dados e tente novamente.");
}
