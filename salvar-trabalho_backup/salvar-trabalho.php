<?php
session_start();

// Verifica se o usuário está conectado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

// Aceita somente envios pelo formulário
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: adicionar-trabalho.php");
    exit;
}

// Recebe os dados do formulário
$titulo = trim($_POST["titulo"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$categoria = trim($_POST["categoria"] ?? "");

$categoriasPermitidas = [
    "Crochê",
    "Amigurumi",
    "Outros trabalhos"
];

// Valida o título
if ($titulo === "" || mb_strlen($titulo) > 150) {
    exit("Informe um nome válido para o trabalho.");
}

// Valida a descrição
if (mb_strlen($descricao) > 3000) {
    exit("A descrição deve ter no máximo 3000 caracteres.");
}

// Valida a categoria
if (!in_array($categoria, $categoriasPermitidas, true)) {
    exit("Selecione uma categoria válida.");
}

// Verifica se uma foto foi enviada
if (
    !isset($_FILES["foto"]) ||
    $_FILES["foto"]["error"] !== UPLOAD_ERR_OK
) {
    exit("Selecione uma foto válida.");
}

$foto = $_FILES["foto"];

// Limita o tamanho a 5 MB
if ($foto["size"] > 5 * 1024 * 1024) {
    exit("A foto deve ter no máximo 5 MB.");
}

$arquivoTemporario = $foto["tmp_name"];

if (!is_uploaded_file($arquivoTemporario)) {
    exit("O arquivo enviado é inválido.");
}

// Aceita somente imagens JPG, PNG ou WEBP
$tiposPermitidos = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

$tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivoTemporario);

if (!isset($tiposPermitidos[$tipo])) {
    exit("Use uma imagem JPG, PNG ou WEBP.");
}

// Confirma que o arquivo é uma imagem válida
if (@getimagesize($arquivoTemporario) === false) {
    exit("O arquivo enviado não é uma imagem válida.");
}

// Conecta ao banco de dados
require_once __DIR__ . "/conexao.php";

$usuarioId = (int) $_SESSION["usuario_id"];

$pasta = __DIR__ . "/uploads/trabalhos/";

if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
    exit("Não foi possível criar a pasta dos trabalhos.");
}

if (!is_writable($pasta)) {
    exit("A pasta dos trabalhos não permite gravar arquivos.");
}

// Gera um nome exclusivo para a imagem
$extensao = $tiposPermitidos[$tipo];

$nomeArquivo = "trabalho_" . $usuarioId . "_" .
    bin2hex(random_bytes(8)) . "." . $extensao;

$caminhoCompleto = $pasta . $nomeArquivo;
$caminhoBanco = "uploads/trabalhos/" . $nomeArquivo;

try {
    // Salva a imagem no servidor
    if (!move_uploaded_file($arquivoTemporario, $caminhoCompleto)) {
        exit("Não foi possível salvar a foto.");
    }

    // Salva os dados no banco de dados
    $sql = "INSERT INTO trabalhos
            (usuario_id, titulo, descricao, categoria, foto)
            VALUES
            (:usuario_id, :titulo, :descricao, :categoria, :foto)";

    $consulta = $conexao->prepare($sql);

    $consulta->execute([
        ":usuario_id" => $usuarioId,
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":categoria" => $categoria,
        ":foto" => $caminhoBanco
    ]);

    // Retorna ao perfil após salvar
    header("Location: perfil.php?trabalho=salvo");
    exit;

} catch (Throwable $erro) {
    // Remove a imagem caso o cadastro falhe
    if (isset($caminhoCompleto) && is_file($caminhoCompleto)) {
        unlink($caminhoCompleto);
    }

    error_log("Erro ao salvar trabalho: " . $erro->getMessage());

    http_response_code(500);
    exit("Não foi possível salvar o trabalho. Verifique a configuração do servidor.");
}