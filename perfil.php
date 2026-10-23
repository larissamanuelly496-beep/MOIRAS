<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

require_once "conexao.php";

$usuarioId = (int) $_SESSION["usuario_id"];

$nome = htmlspecialchars(
    $_SESSION["usuario_nome"] ?? "Artesão",
    ENT_QUOTES,
    "UTF-8"
);

$email = htmlspecialchars(
    $_SESSION["usuario_email"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

// Foto padrão
$caminhoFoto = "fotos/jasss.jpg";

try {
    $consulta = $conexao->prepare(
        "SELECT foto
         FROM perfis
         WHERE usuario_id = :usuario_id
         LIMIT 1"
    );

    $consulta->execute([
        ":usuario_id" => $usuarioId
    ]);

    $perfil = $consulta->fetch(PDO::FETCH_ASSOC);

    if (
        $perfil &&
        !empty($perfil["foto"]) &&
        is_file(__DIR__ . "/" . $perfil["foto"])
    ) {
        $caminhoFoto = $perfil["foto"];
    }
} catch (PDOException $erro) {
    error_log("Erro ao carregar perfil: " . $erro->getMessage());
}

$caminhoFotoSeguro = htmlspecialchars(
    $caminhoFoto,
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

        <!-- PERFIL DO USUÁRIO -->
        <section class="cabecalho-perfil">

            <div class="area-foto-perfil">

                <img
                    src="<?= $caminhoFotoSeguro ?>"
                    alt="Foto de perfil de <?= $nome ?>"
                    class="foto-perfil"
                    id="imagem-perfil"
                >

                <form
                    action="salvar-foto.php"
                    method="POST"
                    enctype="multipart/form-data"
                    id="formulario-foto"
                >
                    <label for="escolher-foto" class="botao-foto">
                        Escolher foto
                    </label>

                    <input
                        type="file"
                        name="foto"
                        id="escolher-foto"
                        accept=".jpg,.jpeg,.png,.webp"
                        hidden
                        required
                    >
                </form>

                <p id="mensagem-foto" aria-live="polite"></p>

            </div>

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

        <!-- TRABALHOS DO USUÁRIO -->
        <section class="trabalhos-perfil">

            <h2>Meus trabalhos</h2>

            <p>
                Compartilhe suas criações e mostre ao mundo
                a beleza da sua arte feita à mão!
            </p>

            <div class="lista-categorias">

                <article class="categoria">
                    <span></span>
                    <h3>Crochê</h3>

                    <button
                        type="button"
                        class="botao-adicionar-trabalho"
                        data-categoria="Crochê"
                    >
                        + Adicionar trabalho
                    </button>
                </article>

                <article class="categoria">
                    <span></span>
                    <h3>Amigurumi</h3>

                    <button
                        type="button"
                        class="botao-adicionar-trabalho"
                        data-categoria="Amigurumi"
                    >
                        + Adicionar trabalho
                    </button>
                </article>

                <article class="categoria">
                    <span></span>
                    <h3>Outros trabalhos</h3>

                    <button
                        type="button"
                        class="botao-adicionar-trabalho"
                        data-categoria="Outros trabalhos"
                    >
                        + Adicionar trabalho
                    </button>
                </article>

            </div>

        </section>

    </main>

    <script>
        // EDIÇÃO DA BIOGRAFIA
        const botaoEditar = document.getElementById("editar-perfil");
        const biografia = document.querySelector(".bio-perfil");

        botaoEditar.addEventListener("click", function () {
            const novaBio = prompt(
                "Escreva uma nova biografia:",
                biografia.textContent.trim()
            );

            if (novaBio !== null && novaBio.trim() !== "") {
                biografia.textContent = novaBio.trim();
            }
        });

        // ESCOLHA E ENVIO DA FOTO
        const escolherFoto = document.getElementById("escolher-foto");
        const imagemPerfil = document.getElementById("imagem-perfil");
        const formularioFoto = document.getElementById("formulario-foto");
        const mensagemFoto = document.getElementById("mensagem-foto");

        escolherFoto.addEventListener("change", function () {
            const arquivo = escolherFoto.files[0];

            if (!arquivo) {
                return;
            }

            const tiposPermitidos = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (!tiposPermitidos.includes(arquivo.type)) {
                mensagemFoto.textContent =
                    "Escolha uma imagem JPG, PNG ou WEBP.";

                escolherFoto.value = "";
                return;
            }

            if (arquivo.size > 5 * 1024 * 1024) {
                mensagemFoto.textContent =
                    "A foto deve ter no máximo 5 MB.";

                escolherFoto.value = "";
                return;
            }

            const leitor = new FileReader();

            leitor.onload = function (evento) {
                imagemPerfil.src = evento.target.result;
            };

            leitor.readAsDataURL(arquivo);

            mensagemFoto.textContent = "Salvando sua foto...";

            formularioFoto.submit();
        });

        // BOTÕES DAS CATEGORIAS
        document.querySelectorAll(".botao-adicionar-trabalho")
            .forEach(function (botao) {
                botao.addEventListener("click", function () {
                    const categoria = botao.dataset.categoria;

                    alert(
                        "Categoria escolhida: " + categoria +
                        "\n\nA próxima etapa será criar o formulário " +
                        "para cadastrar e salvar seu trabalho."
                    );
                });
            });
    </script>

</body>
</html>