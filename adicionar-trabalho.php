<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.html");
    exit;
}

$categorias = [
    "Crochê",
    "Amigurumi",
    "Outros trabalhos"
];

$categoriaSelecionada = $_GET["categoria"] ?? "";

if (!in_array($categoriaSelecionada, $categorias, true)) {
    $categoriaSelecionada = "";
}

function escapar($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Adicionar trabalho | MOIRAS</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .formulario-trabalho {
            box-sizing: border-box;
            width: calc(100% - 30px);
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(70, 30, 100, .12);
        }

        .formulario-trabalho h2 {
            color: #603080;
            text-align: center;
        }

        .formulario-trabalho label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
            color: #49305d;
        }

        .formulario-trabalho input,
        .formulario-trabalho textarea,
        .formulario-trabalho select {
            box-sizing: border-box;
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #d8c8e8;
            border-radius: 10px;
            font: inherit;
            background: #fff;
        }

        .formulario-trabalho input:focus,
        .formulario-trabalho textarea:focus,
        .formulario-trabalho select:focus {
            outline: 2px solid #b99bd8;
            border-color: #603080;
        }

        .formulario-trabalho textarea {
            resize: vertical;
        }

        .formulario-trabalho button,
        .voltar-perfil {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 22px;
            border: none;
            border-radius: 25px;
            background: #603080;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .formulario-trabalho button:hover,
        .voltar-perfil:hover {
            background: #7945a0;
        }

        .observacao {
            color: #665b70;
            font-size: 14px;
        }

        .obrigatorio {
            color: #8b3478;
        }

        .mensagem-erro {
            color: #a12645;
            font-size: 14px;
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

    <main class="formulario-trabalho">

        <h2>Adicionar meu trabalho </h2>

        <p>
            Compartilhe sua criatividade com a comunidade MOIRAS!
        </p>

        <form
            action="salvar-trabalho.php"
            method="POST"
            enctype="multipart/form-data"
            id="formulario-trabalho"
        >

            <label for="titulo">
                Nome do trabalho: <span class="obrigatorio">*</span>
            </label>

            <input
                type="text"
                id="titulo"
                name="titulo"
                maxlength="150"
                required
                placeholder="Ex.: Axolote rosa de amigurumi"
            >

            <label for="categoria">
                Categoria: <span class="obrigatorio">*</span>
            </label>

            <select id="categoria" name="categoria" required>
                <option value="">Selecione uma categoria</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option
                        value="<?= escapar($categoria) ?>"
                        <?= $categoriaSelecionada === $categoria ? "selected" : "" ?>
                    >
                        <?= escapar($categoria) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <label for="descricao">Descrição do trabalho:</label>

            <textarea
                id="descricao"
                name="descricao"
                rows="5"
                maxlength="3000"
                placeholder="Conte como você criou essa peça e quais materiais utilizou..."
            ></textarea>

            <label for="foto">
                Foto do trabalho: <span class="obrigatorio">*</span>
            </label>

            <input
                type="file"
                id="foto"
                name="foto"
                accept=".jpg,.jpeg,.png,.webp"
                required
            >

            <p class="observacao">
                Formatos permitidos: JPG, PNG ou WEBP. Tamanho máximo: 5 MB.
            </p>

            <p
                id="mensagem-erro"
                class="mensagem-erro"
                role="alert"
            ></p>

            <button type="submit" id="botao-publicar">
                Publicar trabalho 
            </button>

        </form>

        <a href="perfil.php" class="voltar-perfil">
            Voltar ao perfil
        </a>

    </main>

    <script>
        const formulario = document.getElementById("formulario-trabalho");
        const campoFoto = document.getElementById("foto");
        const mensagemErro = document.getElementById("mensagem-erro");
        const botaoPublicar = document.getElementById("botao-publicar");

        formulario.addEventListener("submit", function (evento) {
            mensagemErro.textContent = "";

            const arquivo = campoFoto.files[0];

            if (!arquivo) {
                evento.preventDefault();
                mensagemErro.textContent = "Escolha uma foto para o seu trabalho.";
                return;
            }

            const tiposPermitidos = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (!tiposPermitidos.includes(arquivo.type)) {
                evento.preventDefault();
                mensagemErro.textContent = "Escolha uma imagem JPG, PNG ou WEBP.";
                return;
            }

            if (arquivo.size > 5 * 1024 * 1024) {
                evento.preventDefault();
                mensagemErro.textContent = "A imagem deve ter no máximo 5 MB.";
                return;
            }

            botaoPublicar.disabled = true;
            botaoPublicar.textContent = "Publicando...";
        });
    </script>

</body>
</html>