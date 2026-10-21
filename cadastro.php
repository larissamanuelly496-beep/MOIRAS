<?php
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro.html");
    exit;
}

$nome = trim($_POST["nome"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";
$confirmarSenha = $_POST["confirmar-senha"] ?? "";

if ($nome === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Preencha seu nome e um e-mail válido.");
}

if (strlen($senha) < 6) {
    exit("A senha precisa ter pelo menos 6 caracteres.");
}

if ($senha !== $confirmarSenha) {
    exit("As senhas não são iguais.");
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO usuarios (nome, email, senha)
            VALUES (:nome, :email, :senha)";

    $comando = $conexao->prepare($sql);

    $comando->execute([
        ":nome" => $nome,
        ":email" => $email,
        ":senha" => $senhaHash
    ]);

    echo "Cadastro realizado com sucesso! ";
    echo '<p><a href="login.html">Ir para o login</a></p>';

} catch (PDOException $erro) {
    if ($erro->getCode() === "23000") {
        echo "Este e-mail já está cadastrado.";
    } else {
        error_log($erro->getMessage());
        echo "Não foi possível realizar o cadastro. Tente novamente.";
    }
}
?>