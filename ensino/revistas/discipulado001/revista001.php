<style>

</style>

<body class="corpo">
    <nav class="conteudototal">
        <div class="container">
            <h1 class="titulorevista">Disciplina: Como a Igreja protege o Nome de Jesus?</h1>
            <div class="texto_sumario" id="left-div">
                <ul>
                    <p id="titulosumario"><strong><u>ÍNDICE</u></strong></p>

                    <li class="sumario"><a class="linksumario" href="ensino/revistas/discipulado001/licao01.html">Lição
                            1 - Fundamentos da
                            Disciplina na Igreja</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 2 - A Disciplina Bíblica no Antigo
                            Testamento</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 3 - A Disciplina Bíblica no Novo
                            Testamento</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 4 - A Disciplina no contexto atual – I
                            Parte</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 5 - A Disciplina no contexto atual –
                            II Parte</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 6 - A Disciplina no contexto atual –
                            III Parte</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 7 - Firmando um Compromisso</a>
                    </li>
                    <li class="sumario"><a class="linksumario" href="#">Lição 8 - Considerações Pastorais</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="conteudorevista">
            <iframe id="iframeConteudo" src="" frameborder="0"></iframe>

        </div>
    </nav>

    <script>
        // Adicione um evento de clique para cada link do sumário
        const linksSumario = document.querySelectorAll('.linksumario');
        const iframeConteudo = document.getElementById('iframeConteudo');

        // Adicione um evento de clique para cada link do sumário
        linksSumario.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Evita que o link seja seguido normalmente

                // Remove a classe 'selected' de todos os links antes de adicionar ao link clicado
                linksSumario.forEach(link => {
                    link.classList.remove('selected');
                });

                // Adiciona a classe 'selected' ao link clicado
                this.classList.add('selected');

                const url = this.href; // Obtém o URL do arquivo HTML do link clicado
                iframeConteudo.src = url; // Define o atributo "src" do iframe com o URL do arquivo HTML
            });
        });
    </script>

</body>