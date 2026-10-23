
<?php

require_once "conexao.php";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro.html");
    exit;
}

// Recebe os dados do formulário
$nome = trim($_POST["nome"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";
$confirmarSenha = $_POST["confirmar-senha"] ?? "";

// Verifica se os campos foram preenchidos
if ($nome === "" || $email === "" || $senha === "" || $confirmarSenha === "") {
    exit("Preencha todos os campos. <a href='cadastro.html'>Voltar</a>");
}

// Valida o e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Digite um e-mail válido. <a href='cadastro.html'>Voltar</a>");
}

// Verifica o tamanho da senha
if (strlen($senha) < 6) {
    exit("A senha precisa ter pelo menos 6 caracteres. <a href='cadastro.html'>Voltar</a>");
}

// Confere as senhas
if ($senha !== $confirmarSenha) {
    exit("As senhas não são iguais. <a href='cadastro.html'>Voltar</a>");
}

// Protege a senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {

    // Verifica se o e-mail já está cadastrado
    $consulta = $conexao->prepare(
        "SELECT id FROM usuarios WHERE email = :email"
    );

    $consulta->execute([
        ":email" => $email
    ]);

    if ($consulta->fetch()) {
        exit("Este e-mail já está cadastrado. <a href='login.html'>Ir para o login</a>");
    }

    // Salva o novo usuário
    $sql = "INSERT INTO usuarios (nome, email, senha)
            VALUES (:nome, :email, :senha)";

    $comando = $conexao->prepare($sql);

    $comando->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":senha" => $senhaHash
    ]);

    // Exibe a confirmação do cadastro
    echo "<!DOCTYPE html>";
    echo "<html lang='pt-BR'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Cadastro realizado | MOIRAS</title>";
    echo "<link rel='stylesheet' href='style.css'>";
    echo "</head>";
    echo "<body>";
    echo "<main class='pagina-autenticacao'>";
    echo "<section class='caixa-autenticacao'>";
    echo "<h1>MOIRAS</h1>";
    echo "<h2>Cadastro realizado!</h2>";
    echo "<p>Sua conta foi criada com sucesso.</p>";
    echo "<a href='login.html'>Entrar na minha conta</a>";
    echo "<br><br>";
    echo "<a href='index.html'>Voltar ao início</a>";
    echo "</section>";
    echo "</main>";
    echo "</body>";
    echo "</html>";

} catch (PDOException $erro) {
    error_log("Erro no cadastro do MOIRAS: " . $erro->getMessage());

    http_response_code(500);

    echo "Não foi possível concluir o cadastro.";
    echo "<br><a href='cadastro.html'>Voltar ao cadastro</a>";
}