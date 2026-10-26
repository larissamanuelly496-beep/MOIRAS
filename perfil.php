<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . "/conexao.php";

$usuarioId = (int) $_SESSION["usuario_id"];

$nomeOriginal = $_SESSION["usuario_nome"] ?? "Artesão";
$nome = htmlspecialchars($nomeOriginal, ENT_QUOTES, "UTF-8");

$email = htmlspecialchars(
    $_SESSION["usuario_email"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

// CARREGAR FOTO DE PERFIL
$caminhoFoto = "";

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

    if ($perfil && !empty($perfil["foto"])) {
        $arquivoFoto = __DIR__ . "/" . $perfil["foto"];

        if (is_file($arquivoFoto)) {
            $caminhoFoto = $perfil["foto"];
        }
    }
} catch (PDOException $erro) {
    error_log("Erro ao carregar perfil: " . $erro->getMessage());
}

$caminhoFotoSeguro = htmlspecialchars(
    $caminhoFoto,
    ENT_QUOTES,
    "UTF-8"
);

// INICIAL PARA PERFIL SEM FOTO
$nomeLimpo = trim($nomeOriginal);

$inicial = function_exists("mb_substr")
    ? mb_strtoupper(mb_substr($nomeLimpo, 0, 1, "UTF-8"), "UTF-8")
    : strtoupper(substr($nomeLimpo, 0, 1));

if ($inicial === "") {
    $inicial = "M";
}

$inicialSegura = htmlspecialchars(
    $inicial,
    ENT_QUOTES,
    "UTF-8"
);

// CARREGAR TRABALHOS DO USUÁRIO
$trabalhos = [];

try {
    $consultaTrabalhos = $conexao->prepare(
        "SELECT id, titulo, descricao, categoria, foto, criado_em
         FROM trabalhos
         WHERE usuario_id = :usuario_id
         ORDER BY criado_em DESC, id DESC"
    );

    $consultaTrabalhos->execute([
        ":usuario_id" => $usuarioId
    ]);

    $trabalhos = $consultaTrabalhos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {
    error_log("Erro ao carregar trabalhos: " . $erro->getMessage());
}

function escapar($valor) {
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

function caminhoFotoTrabalho($foto) {
    $caminho = ltrim((string) $foto, "/");

    // Aceitar somente arquivos dentro da pasta de trabalhos.
    if (
        strpos($caminho, "uploads/trabalhos/") !== 0 ||
        strpos($caminho, "..") !== false
    ) {
        return "";
    }

    if (!is_file(__DIR__ . "/" . $caminho)) {
        return "";
    }

    return $caminho;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Meu Perfil | MOIRAS</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .lista-trabalhos-publicados {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 22px;
            margin-top: 30px;
            text-align: left;
        }

        .cartao-trabalho {
            overflow: hidden;
            background: #fff;
            border: 1px solid #eadff4;
            border-radius: 18px;
            box-shadow: 0 5px 18px rgba(70, 30, 100, .08);
        }

        .foto-trabalho {
            display: block;
            width: 100%;
            height: 230px;
            object-fit: cover;
            background: #f3eafa;
        }

        .informacoes-trabalho {
            padding: 18px;
        }

        .informacoes-trabalho h3 {
            margin-top: 0;
            color: #603080;
        }

        .categoria-trabalho {
            display: inline-block;
            padding: 6px 12px;
            background: #f0e4fa;
            color: #603080;
            border-radius: 18px;
            font-size: 13px;
        }

        .descricao-trabalho {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .botoes-categorias {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .botao-adicionar-trabalho {
            display: inline-block;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            background: #603080;
            color: white;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .botao-adicionar-trabalho:hover {
            background: #7945a0;
        }

        .mensagem-sem-trabalhos {
            margin: 30px 0;
            padding: 25px;
            background: #faf6ff;
            border: 1px dashed #cdb3e5;
            border-radius: 18px;
        }

        .mensagem-status {
            margin: 20px auto;
            padding: 12px;
            max-width: 600px;
            border-radius: 10px;
            background: #f0e4fa;
            color: #603080;
        }
    </style>
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

                <?php if ($caminhoFoto !== ""): ?>

                    <img
                        src="<?= escapar($caminhoFotoSeguro) ?>"
                        alt="Foto de perfil de <?= escapar($nomeOriginal) ?>"
                        class="foto-perfil"
                        id="imagem-perfil"
                    >

                <?php else: ?>

                    <div
                        class="foto-perfil foto-sem-imagem"
                        id="imagem-perfil"
                        role="img"
                        aria-label="Perfil sem foto"
                    >
                        <span><?= $inicialSegura ?></span>
                    </div>

                <?php endif; ?>

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

        <!-- MEUS TRABALHOS -->
        <section class="trabalhos-perfil">

            <h2>Meus trabalhos</h2>

            <p>
                Compartilhe suas criações e mostre ao mundo
                a beleza da sua arte feita à mão!
            </p>

            <?php if (isset($_GET["trabalho"]) && $_GET["trabalho"] === "salvo"): ?>
                <p class="mensagem-status" role="status">
                    Seu trabalho foi publicado com sucesso! 💜
                </p>
            <?php endif; ?>

            <!-- CATEGORIAS E BOTÕES -->
            <div class="lista-categorias">

                <article class="categoria">
                    <span></span>
                    <h3>Crochê</h3>

                    <a
                        href="adicionar-trabalho.php?categoria=Croch%C3%A9"
                        class="botao-adicionar-trabalho"
                    >
                        + Adicionar trabalho
                    </a>
                </article>

                <article class="categoria">
                    <span></span>
                    <h3>Amigurumi</h3>

                    <a
                        href="adicionar-trabalho.php?categoria=Amigurumi"
                        class="botao-adicionar-trabalho"
                    >
                        + Adicionar trabalho
                    </a>
                </article>

                <article class="categoria">
                    <span></span>
                    <h3>Outros trabalhos</h3>

                    <a
                        href="adicionar-trabalho.php?categoria=Outros%20trabalhos"
                        class="botao-adicionar-trabalho"
                        data-categoria="Outros trabalhos"
                    >
                        + Adicionar trabalho
                    </a>
                </article>

            </div>

            <!-- TRABALHOS PUBLICADOS -->
            <div class="lista-trabalhos-publicados">

                <?php if (empty($trabalhos)): ?>

                    <div class="mensagem-sem-trabalhos">
                        <h3>Seus trabalhos aparecerão aqui! </h3>
                        <p>
                            Você ainda não publicou nenhum trabalho.
                            Clique em “Adicionar trabalho” para começar.
                        </p>
                    </div>

                <?php else: ?>

                    <?php foreach ($trabalhos as $trabalho): ?>

                        <article class="cartao-trabalho">

                            <?php
                            $fotoTrabalho = caminhoFotoTrabalho(
                                $trabalho["foto"] ?? ""
                            );
                            ?>

                            <?php if ($fotoTrabalho !== ""): ?>
                                <img
                                    class="foto-trabalho"
                                    src="<?= escapar($fotoTrabalho) ?>"
                                    alt="<?= escapar($trabalho["titulo"]) ?>"
                                    loading="lazy"
                                >
                            <?php endif; ?>

                            <div class="informacoes-trabalho">

                                <span class="categoria-trabalho">
                                    <?= escapar($trabalho["categoria"]) ?>
                                </span>

                                <h3>
                                    <?= escapar($trabalho["titulo"]) ?>
                                </h3>

                                <?php if (!empty($trabalho["descricao"])): ?>
                                    <p class="descricao-trabalho">
                                        <?= escapar($trabalho["descricao"]) ?>
                                    </p>
                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </section>

    </main>

    <script>
        // EDIÇÃO TEMPORÁRIA DA BIOGRAFIA
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

        // ESCOLHA E ENVIO DA FOTO DE PERFIL
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

            mensagemFoto.textContent = "Enviando sua foto...";
            formularioFoto.submit();
        });
    </script>

</body>
</html>