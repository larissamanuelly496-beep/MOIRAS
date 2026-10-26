
document.addEventListener("DOMContentLoaded", () => {

    // ELEMENTOS DA PÁGINA
    const campoPesquisa = document.getElementById("pesquisa");
    const botaoPesquisa = document.getElementById("botao-pesquisa");

    const categorias = document.querySelectorAll(
        "#categorias .categoria"
    );

    const publicacoes = document.querySelectorAll(
        ".publicacao"
    );

    const botoesCurtir = document.querySelectorAll(
        ".botao-curtir"
    );

    let categoriaSelecionada = "";
    let termoPesquisa = "";

    // NORMALIZAR TEXTO: IGNORAR DIFERENÇAS DE ACENTUAÇÃO
    function normalizar(texto) {
        return texto
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim();
    }

    // PREPARAR AS INFORMAÇÕES DE CADA PUBLICAÇÃO
    const dadosPublicacoes = Array.from(publicacoes).map(
        (publicacao) => {
            const titulo =
                publicacao.querySelector("h3")?.textContent || "";

            const descricao =
                publicacao.querySelector("p")?.textContent || "";

            const autor =
                publicacao.querySelector(".conteudo-publicacao span")
                    ?.textContent || "";

            return {
                elemento: publicacao,
                categoria: normalizar(
                    publicacao.dataset.categoria || ""
                ),
                texto: normalizar(
                    `${titulo} ${descricao} ${autor} ${publicacao.dataset.categoria || ""}`
                )
            };
        }
    );

    // CRIAR MENSAGEM DE RESULTADO
    let mensagemResultado = document.getElementById(
        "mensagem-resultado"
    );

    if (!mensagemResultado) {
        mensagemResultado = document.createElement("p");
        mensagemResultado.id = "mensagem-resultado";
        mensagemResultado.setAttribute("aria-live", "polite");

        const lista = document.querySelector(".lista-publicacoes");

        if (lista) {
            lista.insertAdjacentElement(
                "afterend",
                mensagemResultado
            );
        }
    }

    // PESQUISAR E FILTRAR PUBLICAÇÕES
    function filtrarPublicacoes() {
        let quantidadeVisivel = 0;

        dadosPublicacoes.forEach((dados) => {
            const correspondeCategoria =
                categoriaSelecionada === "" ||
                dados.categoria === categoriaSelecionada;

            const correspondePesquisa =
                termoPesquisa === "" ||
                dados.texto.includes(termoPesquisa);

            const mostrar =
                correspondeCategoria && correspondePesquisa;

            dados.elemento.hidden = !mostrar;

            if (mostrar) {
                quantidadeVisivel++;
            }
        });

        if (mensagemResultado) {
            if (quantidadeVisivel === 0) {
                mensagemResultado.textContent =
                    "Nenhum trabalho encontrado. Tente outra pesquisa ou categoria.";
            } else {
                mensagemResultado.textContent =
                    `${quantidadeVisivel} ${
                        quantidadeVisivel === 1
                            ? "trabalho encontrado"
                            : "trabalhos encontrados"
                    }.`;
            }
        }
    }

    // ATUALIZAR O ESTADO VISUAL DAS CATEGORIAS
    function atualizarCategorias() {
        categorias.forEach((categoria) => {
            const selecionada =
                normalizar(categoria.dataset.categoria || "") ===
                categoriaSelecionada &&
                categoriaSelecionada !== "";

            categoria.classList.toggle(
                "categoria-selecionada",
                selecionada
            );

            categoria.setAttribute(
                "aria-pressed",
                String(selecionada)
            );
        });
    }

    // EXECUTAR A PESQUISA
    function pesquisar() {
        termoPesquisa = normalizar(campoPesquisa?.value || "");

        filtrarPublicacoes();

        document.getElementById("explorar")?.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });
    }

    if (botaoPesquisa && campoPesquisa) {
        botaoPesquisa.addEventListener("click", pesquisar);

        campoPesquisa.addEventListener("keydown", (evento) => {
            if (evento.key === "Enter") {
                evento.preventDefault();
                pesquisar();
            }
        });

        // ATUALIZAR OS RESULTADOS ENQUANTO A PESSOA DIGITA
        campoPesquisa.addEventListener("input", () => {
            termoPesquisa = normalizar(campoPesquisa.value);
            filtrarPublicacoes();
        });
    }

    // FILTRAR POR CATEGORIA
    categorias.forEach((categoria) => {

        categoria.setAttribute("role", "button");
        categoria.setAttribute("tabindex", "0");

        function selecionarCategoria() {
            const categoriaClicada = normalizar(
                categoria.dataset.categoria || ""
            );

            // CLICAR NOVAMENTE REMOVE O FILTRO
            categoriaSelecionada =
                categoriaSelecionada === categoriaClicada
                    ? ""
                    : categoriaClicada;

            atualizarCategorias();
            filtrarPublicacoes();

            document.getElementById("explorar")?.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }

        categoria.addEventListener(
            "click",
            selecionarCategoria
        );

        // PERMITIR USAR A CATEGORIA PELO TECLADO
        categoria.addEventListener("keydown", (evento) => {
            if (
                evento.key === "Enter" ||
                evento.key === " "
            ) {
                evento.preventDefault();
                selecionarCategoria();
            }
        });
    });

    // CARREGAR CURTIDAS SALVAS NO NAVEGADOR
    botoesCurtir.forEach((botao, indice) => {
        const chaveCurtida = `moiras-curtida-${indice}`;

        function atualizarBotao(curtido) {
            botao.dataset.curtido = String(curtido);
            botao.setAttribute(
                "aria-pressed",
                String(curtido)
            );

            botao.textContent = curtido
                ? "♥ Curtido"
                : "♡ Curtir";

            botao.classList.toggle(
                "curtido",
                curtido
            );
        }

        let curtido = false;

        try {
            curtido =
                localStorage.getItem(chaveCurtida) === "true";
        } catch (erro) {
            console.warn(
                "Não foi possível carregar a curtida salva.",
                erro
            );
        }

        atualizarBotao(curtido);

        botao.addEventListener("click", () => {
            curtido = botao.dataset.curtido !== "true";

            atualizarBotao(curtido);

            try {
                localStorage.setItem(
                    chaveCurtida,
                    String(curtido)
                );
            } catch (erro) {
                console.warn(
                    "Não foi possível salvar a curtida.",
                    erro
                );
            }
        });
    });

    // INFORMAR QUE O JAVASCRIPT FOI CARREGADO
    filtrarPublicacoes();

    console.log(
        "MOIRAS: pesquisa, filtros e curtidas inicializados!"
    );
});
