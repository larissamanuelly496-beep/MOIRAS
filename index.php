
<?php
require_once "conexao.php";

// Busca os trabalhos publicados e os nomes dos artistas
$sql = "SELECT
            trabalhos.id,
            trabalhos.titulo,
            trabalhos.descricao,
            trabalhos.categoria,
            trabalhos.foto,
            usuarios.nome AS artista
        FROM trabalhos
        INNER JOIN usuarios
            ON trabalhos.usuario_id = usuarios.id
        ORDER BY trabalhos.criado_em DESC";

$stmt = $conexao->prepare($sql);
$stmt->execute();

$trabalhos = $stmt->fetchAll();

// Função para proteger os textos exibidos na página
function limpar($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

// Prepara a categoria para os filtros
function categoriaFiltro($categoria) {
    $categoria = mb_strtolower(trim($categoria), 'UTF-8');

    return strtr($categoria, [
        'ê' => 'e',
        'é' => 'e',
        'á' => 'a',
        'ã' => 'a',
        'ç' => 'c',
        ' ' => '-'
    ]);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MOIRAS | Arte feita à mão</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <header id="topo">
        <h1>MOIRAS</h1>
        <p>Arte feita à mão, histórias compartilhadas.</p>
    </header>

    <nav class="menu">
        <a href="#inicio">Início</a>
        <a href="#explorar">Explorar</a>
        <a href="#artistas">Artistas</a>
        <a href="#categorias">Categorias</a>
        <a href="#sobre">Sobre</a>
    </nav>

    <section class="barra-pesquisa">
        <input
            type="text"
            id="pesquisa"
            placeholder="Procure por crochê, bordado, amigurumi..."
            aria-label="Pesquisar trabalhos artesanais"
        >

        <button type="button" id="botao-pesquisa">
            Pesquisar
        </button>
    </section>

    <main>

        <!-- INÍCIO -->
        <section id="inicio">

            <h2>Bem-vinda ao MOIRAS!</h2>

            <p>
                Cada peça tem uma história. Compartilhe a sua.
            </p>

            <p>
                Valorize o trabalho manual e conecte-se com
                quem ama artesanato.
            </p>

            <button
                type="button"
                onclick="window.location.href='cadastro.html'"
            >
                Criar minha conta
            </button>

            <p class="mensagem-entrar">
                Já conhece o MOIRAS?<br>
                Entre na sua conta e continue compartilhando
                sua criatividade!
            </p>

            <button
                type="button"
                onclick="window.location.href='login.html'"
            >
                Entrar
            </button>

        </section>


        <!-- CATEGORIAS -->
        <section id="categorias" class="categorias">

            <h2>Explore o mundo do artesanato</h2>

            <p>
                Encontre trabalhos feitos com carinho.
            </p>

            <div class="lista-categorias">

                <article class="categoria" data-categoria="croche">
                    <span></span>
                    <h3>Crochê</h3>
                </article>

                <article class="categoria" data-categoria="amigurumi">
                    <span></span>
                    <h3>Amigurumi</h3>
                </article>

                <article class="categoria" data-categoria="bordado">
                    <span></span>
                    <h3>Bordado</h3>
                </article>

                <article class="categoria" data-categoria="costura">
                    <span></span>
                    <h3>Costura</h3>
                </article>

            </div>

        </section>


        <!-- PUBLICAÇÕES -->
        <section id="explorar" class="publicacoes">

            <h2>Trabalhos que contam histórias</h2>

            <p>
                Conheça criações feitas com carinho por nossos artistas.
            </p>

            <div class="lista-publicacoes">

                <?php if (!empty($trabalhos)): ?>

                    <?php foreach ($trabalhos as $trabalho): ?>

                        <article
                            class="publicacao"
                            data-categoria="<?php echo limpar(categoriaFiltro($trabalho['categoria'])); ?>"
                        >

                            <div class="imagem-publicacao">

                                <img
                                    src="<?php echo limpar($trabalho['foto']); ?>"
                                    alt="<?php echo limpar($trabalho['titulo']); ?>"
                                    loading="lazy"
                                >

                            </div>

                            <div class="conteudo-publicacao">

                                <h3>
                                    <?php echo limpar($trabalho['titulo']); ?>
                                </h3>

                                <p>
                                    <?php echo limpar($trabalho['descricao']); ?>
                                </p>

                                <span>
                                    Por <?php echo limpar($trabalho['artista']); ?>
                                </span>

                                <button
                                    type="button"
                                    class="botao-curtir"
                                    aria-pressed="false"
                                >
                                    ♡ Curtir
                                </button>

                            </div>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="mensagem-vazia">
                        Ainda não temos trabalhos publicados.
                        Seja a primeira pessoa a compartilhar sua arte!
                    </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- ARTISTAS -->
        <section id="artistas" class="secao-artistas">

            <h2>Conheça nossos artistas</h2>

            <p>
                Cada artista tem uma história e um talento especial.
                No MOIRAS, valorizamos quem transforma criatividade
                em arte feita à mão.
            </p>

            <div class="lista-artistas">

                <article class="artista">
                    <h3>Lari</h3>
                    <p>Especialidade: Crochê</p>
                </article>

                <article class="artista">
                    <h3>Maria Criativa</h3>
                    <p>Especialidade: Amigurumi</p>
                </article>

                <article class="artista">
                    <h3>Clara Faz à Mão</h3>
                    <p>Especialidade: Bordado</p>
                </article>

            </div>

        </section>


        <!-- SOBRE -->
        <section id="sobre" class="secao-sobre">

            <h2>Sobre o MOIRAS</h2>

            <p>
                O MOIRAS é um espaço para compartilhar trabalhos
                artesanais, conhecer artistas e valorizar a beleza
                das criações feitas à mão.
            </p>

            <p>
                Nosso objetivo é conectar pessoas apaixonadas
                por artesanato e criatividade.
            </p>

        </section>

    </main>


    <!-- RODAPÉ -->
    <footer>

        <p>
            © 2026 MOIRAS — Arte feita à mão, histórias compartilhadas.
        </p>

        <a href="#topo">Voltar ao início ↑</a>

    </footer>

    <script src="script.js"></script>

</body>
</html>
