<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: perfil.php");
    exit;
}

if (
    !isset($_FILES["foto"]) ||
    $_FILES["foto"]["error"] !== UPLOAD_ERR_OK
) {
    exit("Selecione uma foto para enviar.");
}

$foto = $_FILES["foto"];

// Limite de 5 MB
if ($foto["size"] > 5 * 1024 * 1024) {
    exit("A foto deve ter no máximo 5 MB.");
}

// Aceitar somente imagens JPG, PNG e WEBP
$tiposPermitidos = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

$arquivoTemporario = $foto["tmp_name"];

if (!is_uploaded_file($arquivoTemporario)) {
    exit("O arquivo enviado é inválido.");
}

$tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivoTemporario);

if (!isset($tiposPermitidos[$tipo])) {
    exit("Use uma imagem JPG, PNG ou WEBP.");
}

if (getimagesize($arquivoTemporario) === false) {
    exit("O arquivo não é uma imagem válida.");
}

require_once "conexao.php";

$usuarioId = (int) $_SESSION["usuario_id"];

$pasta = __DIR__ . "/uploads/perfis/";

if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
    exit("Não foi possível criar a pasta das fotos.");
}

$extensao = $tiposPermitidos[$tipo];

// Cada imagem recebe um nome exclusivo
$nomeArquivo = "usuario_" . $usuarioId . "_" .
    bin2hex(random_bytes(8)) . "." . $extensao;

$caminhoCompleto = $pasta . $nomeArquivo;
$caminhoBanco = "uploads/perfis/" . $nomeArquivo;

try {
    $conexao->beginTransaction();

    // Procurar o perfil deste usuário
    $consulta = $conexao->prepare(
        "SELECT id, foto
         FROM perfis
         WHERE usuario_id = :usuario_id
         LIMIT 1
         FOR UPDATE"
    );

    $consulta->execute([
        ":usuario_id" => $usuarioId
    ]);

    $perfil = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$perfil) {
        $conexao->rollBack();

        http_response_code(400);
        exit(
            "Seu perfil ainda não foi criado. " .
            "Precisamos criar o registro na tabela perfis antes de salvar a foto."
        );
    }

    if (!move_uploaded_file($arquivoTemporario, $caminhoCompleto)) {
        $conexao->rollBack();

        exit("Não foi possível salvar a foto no servidor.");
    }

    // Atualizar somente o perfil do usuário conectado
    $atualizar = $conexao->prepare(
        "UPDATE perfis
         SET foto = :foto
         WHERE usuario_id = :usuario_id"
    );

    $atualizar->execute([
        ":foto" => $caminhoBanco,
        ":usuario_id" => $usuarioId
    ]);

    $conexao->commit();

    // Evitar que a foto antiga fique sendo exibida pelo navegador
    header("Location: perfil.php?foto=salva&v=" . time());
    exit;

} catch (Throwable $erro) {
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }

    if (isset($caminhoCompleto) && is_file($caminhoCompleto)) {
        unlink($caminhoCompleto);
    }

    error_log("Erro ao salvar foto no MOIRAS: " . $erro->getMessage());

    http_response_code(500);
    exit("Não foi possível salvar a foto. Verifique a configuração do perfil e do banco de dados.");
}