<?php
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === "") {
    exit("Preencha o e-mail e a senha corretamente.");
}

try {
    $sql = "SELECT id, nome, email, senha
            FROM usuarios
            WHERE email = :email
            LIMIT 1";

    $comando = $conexao->prepare($sql);
    $comando->execute([":email" => $email]);

    $usuario = $comando->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        session_start();

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_email"] = $usuario["email"];

        header("Location: perfil.php");
        exit;
    }

    echo "E-mail ou senha incorretos.";
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    echo "Não foi possível realizar o login. Tente novamente.";
}
?>