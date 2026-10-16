<?php
session_start();

// Verifica se a pessoa está conectada
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

// Protege os dados exibidos na página
$nome = htmlspecialchars(
    $_SESSION["usuario_nome"],
    ENT_QUOTES,
    "UTF-8"
);

$email = htmlspecialchars(
    $_SESSION["usuario_email"],
    ENT_QUOTES,
    "UTF-8"
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Perfil | MOIRAS</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header>
        <h1>MOIRAS</h1>
        <p>Arte feita à mão, histórias compartilhadas.</p>
    </header>

    <nav class="menu">
        <a href="index.html">Início</a>
        <a href="index.html#explorar">Explorar</a>
        <a href="perfil.php">Meu perfil</a>
    </nav>

    <main class="pagina-perfil">

        <section class="cabecalho-perfil">

            <div class="foto-perfil"></div>

            <h2><?= $nome ?></h2>

            <p class="usuario-perfil">
                <?= $email ?>
            </p>

            <p class="bio-perfil">
                Este é o seu espaço na MOIRAS!
                Compartilhe sua criatividade e mostre
                ao mundo a beleza da sua arte feita à mão.
            </p>

            <div class="especialidades">
                <span>Artesanato</span>
                <span>Criatividade</span>
                <span>Feito à mão</span>
            </div>

            <button type="button" id="editar-perfil">
                Editar perfil
            </button>

        </section>

        <section class="trabalhos-perfil">

            <h2>Meus trabalhos</h2>

            <p>
                Aqui você poderá apresentar suas criações
                e compartilhar suas histórias.
            </p>

            <div class="lista-categorias">

                <article class="categoria">
                    <span></span>
                    <h3>Crochê</h3>
                </article>

                <article class="categoria">
                    <span></span>
                    <h3>Amigurumi</h3>
                </article>

                <article class="categoria">
                    <span></span>
                    <h3>Outros trabalhos</h3>
                </article>

            </div>

        </section>

    </main>

</body>
</html>