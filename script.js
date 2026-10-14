const botoesCurtir = document.querySelectorAll(".publicacao button");

botoesCurtir.forEach((botao) => {
    botao.addEventListener("click", () => {
        if (botao.textContent.includes("Curtir")) {
            botao.textContent = "Curtido";
            botao.style.backgroundColor = "#5b2f87";
        } else {
            botao.textContent = "Curtir";
            botao.style.backgroundColor = "#8b5cc0";
        }
    });
});

const campoPesquisa = document.getElementById("pesquisa");
const botaoPesquisa = document.getElementById("botao-pesquisa");

function normalizar(texto) {
    return texto
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim();
}

botaoPesquisa.addEventListener("click", () => {
    const termo = normalizar(campoPesquisa.value);
    const publicacoes = document.querySelectorAll(".publicacao");

    publicacoes.forEach((publicacao) => {
        const texto = normalizar(publicacao.textContent);
        publicacao.style.display =
            termo === "" || texto.includes(termo) ? "" : "none";
    });
});
console.log("MOIRAS funcionando!");

const categorias = document.querySelectorAll(".categoria");
const todasPublicacoes = document.querySelectorAll(".publicacao");

categorias.forEach((categoria) => {
    categoria.addEventListener("click", () => {
        const tipo = categoria.dataset.categoria;

        todasPublicacoes.forEach((publicacao) => {
            const texto = publicacao.textContent.toLowerCase();

            if (tipo === "croche" && texto.includes("crochê")) {
                publicacao.style.display = "";
            } else if (tipo === "amigurumi" && texto.includes("amigurumi")) {
                publicacao.style.display = "";
            } else if (tipo === "bordado" && texto.includes("bordado")) {
                publicacao.style.display = "";
            } else if (tipo === "costura" && texto.includes("costura")) {
                publicacao.style.display = "";
            } else {
                publicacao.style.display = "none";
            }
        });

        document.getElementById("explorar").scrollIntoView({
            behavior: "smooth"
        });
    });
});
