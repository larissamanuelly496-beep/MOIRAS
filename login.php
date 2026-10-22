<?php

session_start();

require_once "conexao.php";

// Só permite o envio do formulário
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

// Recebe os dados
$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";

// Valida os campos
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === "") {
    exit(
        "Preencha o e-mail e a senha corretamente. " .
        "<a href='login.html'>Voltar</a>"
    );
}

try {

    // Busca o usuário pelo e-mail
    $sql = "SELECT id, nome, email, senha
            FROM usuarios
            WHERE email = :email
            LIMIT 1";

    $comando = $conexao->prepare($sql);

    $comando->execute([
        ":email" => $email
    ]);

    $usuario = $comando->fetch(PDO::FETCH_ASSOC);

    // Confere a senha
    if ($usuario && password_verify($senha, $usuario["senha"])) {

        session_regenerate_id(true);

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
        $_SESSION["usuario_email"] = $usuario["email"];

        header("Location: perfil.php");
        exit;
    }

    echo "E-mail ou senha incorretos. ";
    echo "<a href='login.html'>Tentar novamente</a>";

} catch (PDOException $erro) {

    // Guarda detalhes técnicos no log do servidor
    error_log($erro->getMessage());

    http_response_code(500);

    echo "Não foi possível realizar o login. ";
    echo "Verifique a conexão e a tabela de usuários.";
    echo "<br><a href='login.html'>Voltar ao login</a>";
}
?>
