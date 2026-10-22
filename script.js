
document.addEventListener("DOMContentLoaded", () => {

    // ELEMENTOS DA PÁGINA
    const campoPesquisa = document.getElementById("pesquisa");
    const botaoPesquisa = document.getElementById("botao-pesquisa");

    const categorias = document.querySelectorAll(".categoria");
    const publicacoes = document.querySelectorAll(".publicacao");
    const botoesCurtir = document.querySelectorAll(".botao-curtir");

    let categoriaSelecionada = "";
    let termoPesquisa = "";

    // NORMALIZAR TEXTO PARA FACILITAR A PESQUISA
    function normalizar(texto) {
        return texto
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim();
    }

    // PESQUISA E FILTRO DE CATEGORIAS
    function filtrarPublicacoes() {
        publicacoes.forEach((publicacao) => {

            const categoria = publicacao.dataset.categoria || "";
            const texto = normalizar(publicacao.textContent);

            const correspondeCategoria =
                categoriaSelecionada === "" ||
                categoria === categoriaSelecionada;

            const correspondePesquisa =
                termoPesquisa === "" ||
                texto.includes(termoPesquisa);

            publicacao.style.display =
                correspondeCategoria && correspondePesquisa
                    ? ""
                    : "none";
        });
    }

    // PESQUISAR AO CLICAR NO BOTÃO
    if (botaoPesquisa && campoPesquisa) {
        botaoPesquisa.addEventListener("click", () => {

            termoPesquisa = normalizar(campoPesquisa.value);

            filtrarPublicacoes();

            document.getElementById("explorar").scrollIntoView({
                behavior: "smooth"
            });
        });

        // PESQUISAR TAMBÉM AO APERTAR ENTER
        campoPesquisa.addEventListener("keydown", (evento) => {
            if (evento.key === "Enter") {
                botaoPesquisa.click();
            }
        });
    }

    // FILTRAR POR CATEGORIA
    categorias.forEach((categoria) => {

        categoria.setAttribute("role", "button");
        categoria.setAttribute("tabindex", "0");

        function selecionarCategoria() {

            categoriaSelecionada = categoria.dataset.categoria || "";

            filtrarPublicacoes();

            document.getElementById("explorar").scrollIntoView({
                behavior: "smooth"
            });
        }

        categoria.addEventListener("click", selecionarCategoria);

        // PERMITIR USAR A CATEGORIA PELO TECLADO
        categoria.addEventListener("keydown", (evento) => {
            if (evento.key === "Enter" || evento.key === " ") {
                evento.preventDefault();
                selecionarCategoria();
            }
        });
    });

    // BOTÕES DE CURTIR
    botoesCurtir.forEach((botao) => {

        botao.dataset.curtido = "false";
        botao.setAttribute("aria-pressed", "false");

        botao.addEventListener("click", () => {

            const jaCurtiu = botao.dataset.curtido === "true";

            if (jaCurtiu) {

                botao.dataset.curtido = "false";
                botao.setAttribute("aria-pressed", "false");
                botao.textContent = "♡ Curtir";

            } else {

                botao.dataset.curtido = "true";
                botao.setAttribute("aria-pressed", "true");
                botao.textContent = "♥ Curtido";

            }
        });
    });

    console.log("MOIRAS: JavaScript carregado com sucesso! ");

});
