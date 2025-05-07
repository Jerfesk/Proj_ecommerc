<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <style>
        /* Estilos gerais */
        body {
            font-family: Arial, Helvetica, sans-serif; /* Fonte mais comum/formal */
            margin: 0;
            padding: 0;
            background-color: #f8f9fa; /* Cinza muito claro de fundo */
            color: #212529; /* Cor de texto principal (preto suave) */
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* Container principal */
        .container {
            width: 90%;
            max-width: 700px; /* Largura um pouco menor */
            margin-top: 40px; /* Mais espaço no topo */
            margin-bottom: 40px; /* Espaço embaixo */
            background-color: #ffffff; /* Fundo branco */
            padding: 30px 40px; /* Mais padding horizontal */
            border-radius: 4px; /* Cantos menos arredondados */
            border: 1px solid #dee2e6; /* Borda cinza clara */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); /* Sombra bem sutil */
        }

        /* Título principal */
        h1 {
            color: #343a40; /* Cinza bem escuro */
            text-align: center;
            margin-top: 0;
            margin-bottom: 35px;
            border-bottom: 1px solid #dee2e6; /* Linha divisória cinza */
            padding-bottom: 15px;
            font-size: 1.8em; /* Tamanho do título */
            font-weight: 600;
        }

        /* Seções de Gerenciamento */
        .management-section {
            margin-bottom: 30px;
            padding-top: 10px; /* Espaçamento interno superior */
            /* Removendo borda e fundo extra para um visual mais limpo */
        }

        .management-section h2 {
            color: #495057; /* Cinza escuro para subtítulos */
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 1.3em;
            border-bottom: 1px solid #e9ecef; /* Linha divisória mais sutil */
            padding-bottom: 8px;
            font-weight: 600; /* Títulos de seção mais destacados */
        }

        /* Estilo para a lista de ações */
        .action-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Espaçamento entre os botões */
        }

        .action-list li {
            margin-bottom: 0;
        }

        /* Estilo para os links (botões) */
        .action-list li a {
            display: inline-block;
            background-color: #6c757d; /* Cinza médio (cor base) */
            color: #ffffff;
            padding: 8px 15px; /* Botões um pouco menores */
            text-decoration: none;
            border-radius: 3px; /* Cantos mais retos */
            font-size: 0.9em;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid transparent; /* Borda inicial transparente */
            text-align: center;
        }

        /* Efeito Hover para os botões */
        .action-list li a:hover {
            background-color: #5a6268; /* Cinza um pouco mais escuro */
            border-color: #545b62; /* Mostra borda no hover */
        }

        /* Link/Botão de Sair */
        .logout-section {
            text-align: center;
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6; /* Linha separando o botão Sair */
        }

        .logout-link {
            display: inline-block;
            background-color: #495057; /* Cinza escuro */
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid transparent;
        }

        .logout-link:hover {
            background-color: #343a40; /* Cinza mais escuro (quase preto) */
            border-color: #343a40;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Painel de Administração</h1>

        <section class="management-section">
            <h2>Usuários</h2> <ul class="action-list">
                <li><a href="add_usuario.php">Adicionar</a></li>
                <li><a href="list_usuario.php">Listar</a></li>
            </ul>
        </section>

        <section class="management-section">
            <h2>Produtos</h2> <ul class="action-list">
                <li><a href="produto_cadastrar.php">Adicionar</a></li>
                <li><a href="produto_listar.php">Listar</a></li>
                
            </ul>
        </section>

        <div class="logout-section">
            <a href="index.php" class="logout-link">Sair</a>
        </div>
    </div>
</body>
</html>






















<!-- <?php
session_start();

// Verificar se o usuário está logado
// if (!isset($_SESSION["usuario_id"])) {
//     header("Location: login.php"); // Redirecionar para a página de login se não estiver logado
//     exit();
// }

?>

<!DOCTYPE html>
<html lang="pt-BR">
<body>
    
    <p><a href="index.php">Sair</a></p>

</body>
</html> -->