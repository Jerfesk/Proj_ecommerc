<?php
// SEU CÓDIGO PHP ORIGINAL - INTACTO
session_start();  //inicia a sessão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    include 'conexao.php';       // conexão com BD

    $nome = $_POST["nome"];      // dados da tabela
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    
    $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);// Cripto a senha antes de salvar no banco de dados
    
    $sql = "INSERT INTO usuario (nome, email, senha,dt_inc) VALUES (?, ?, ?,now())";  //insere um novo usuário, obs:este now() é o comando agora, coloca data e hora.

    $stmt = $conn->prepare($sql);      // declaração SQL, query é a "consulta" ao BD, seu resultado é guardado em $stmt

    $stmt->bind_param("sss", $nome, $email, $senha_criptografada);  // Vincular os parâmetros, o sss imdica todos são strings

    if ($stmt->execute()) {                                     // Executar a query, verifica se cadastrado
        $mensagem = "Usuário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar usuário: " . $stmt->error;
    }

    $stmt->close();  // Fechar a declaração

    $conn->close();  // Fechar a conexão
}
// FIM DO SEU CÓDIGO PHP ORIGINAL
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário - Painel de Administração</title> <style>
        /* Estilos gerais (COPIADOS DA adm.php) */
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #212529;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* Container principal (COPIADO DA adm.php) */
        .container {
            width: 90%;
            max-width: 700px; /* Mesma largura máxima da adm.php */
            margin-top: 40px;
            margin-bottom: 40px;
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Título principal (COPIADO DA adm.php) */
        /* O h1 do seu HTML original será estilizado por esta regra */
        h1 {
            color: #343a40;
            text-align: center;
            margin-top: 0;
            margin-bottom: 35px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            font-size: 1.8em;
            font-weight: 600;
        }

        /* --- INÍCIO: NOVOS ESTILOS PARA O FORMULÁRIO (baseados na estética da adm.php) --- */
        .form-group {
            margin-bottom: 20px; /* Espaçamento entre os campos do formulário */
        }

        .form-group label {
            display: block; /* Faz o label ocupar a linha toda */
            margin-bottom: 8px; /* Espaço entre o label e o input */
            color: #495057; /* Cor similar aos subtítulos da adm.php */
            font-weight: 600;
            font-size: 0.95em;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%; /* Faz os inputs ocuparem toda a largura disponível */
            padding: 10px 12px;
            border: 1px solid #ced4da; /* Borda sutil */
            border-radius: 3px; /* Consistente com outros elementos */
            box-sizing: border-box; /* Evita que padding aumente a largura total */
            font-size: 0.95em;
            color: #495057;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #5a6268; /* Destaque no foco */
            outline: 0;
            box-shadow: 0 0 0 0.1rem rgba(108, 117, 125, 0.25); /* Sombra sutil no foco */
        }

        .btn-submit { /* Estilo para o botão de submissão do formulário */
            display: inline-block;
            background-color: #28a745; /* Verde para ação de "Cadastrar", diferenciando das ações da adm.php */
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid #28a745; /* Borda da mesma cor do fundo */
            cursor: pointer;
            font-size: 1em;
            margin-top: 10px; /* Espaço acima do botão */
        }

        .btn-submit:hover {
            background-color: #218838; /* Verde mais escuro no hover */
            border-color: #1e7e34;
        }

        /* Estilo para a mensagem de feedback (PHP) */
        .form-message {
            padding: 12px 18px;
            margin-bottom: 25px;
            border-radius: 3px;
            border: 1px solid transparent;
            text-align: center;
            font-size: 0.95em;
            background-color: #e9ecef; /* Fundo neutro e claro */
            color: #343a40; /* Texto escuro */
            border-color: #dee2e6; /* Borda sutil */
        }
        
        /* Seção e link de "Voltar" */
        .back-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6; /* Linha separadora como na adm.php */
        }

        .back-link {
            display: inline-block;
            background-color: #6c757d; /* Cinza médio, como os botões de ação da adm.php */
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid transparent;
        }

        .back-link:hover {
            background-color: #5a6268; /* Cinza um pouco mais escuro no hover */
            border-color: #545b62;
        }

        /* Ocultar <br> originais do formulário, pois .form-group já cuida do espaçamento */
        form > br { 
            display: none;
        }
        /* --- FIM: NOVOS ESTILOS PARA O FORMULÁRIO --- */

        /* Estilos da adm.php que podem não ser diretamente usados aqui, mas mantidos para caso de expansão */
        .management-section {
            margin-bottom: 30px;
            padding-top: 10px;
        }
        .management-section h2 {
            color: #495057; margin-top: 0; margin-bottom: 18px; font-size: 1.3em;
            border-bottom: 1px solid #e9ecef; padding-bottom: 8px; font-weight: 600;
        }
        .action-list {
            list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 10px;
        }
        .action-list li { margin-bottom: 0; }
        .action-list li a {
            display: inline-block; background-color: #6c757d; color: #ffffff; padding: 8px 15px;
            text-decoration: none; border-radius: 3px; font-size: 0.9em;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid transparent; text-align: center;
        }
        .action-list li a:hover { background-color: #5a6268; border-color: #545b62;}

    </style>
</head>
<body>
    <div class="container">
        <h1>Adicionar Novo Usuário</h1> <?php if (isset($mensagem)): ?>
            <p class="form-message"><?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn-submit">Cadastrar</button> </form> <div class="back-section">
            <a href="adm.php" class="back-link">Voltar para Administração</a>
        </div>
    </div> </body>
</html>

<!--
Inicia uma sessão.
Verifica se a página foi acessada através de um envio de formulário (método POST).
Se sim, inclui o arquivo de conexão com o banco de dados.
Recupera os dados do nome, email e senha enviados pelo formulário.
Criptografa a senha usando a função password_hash().
Prepara uma query SQL para inserir um novo usuário no banco de dados.
Vincula os valores do nome, email e senha criptografada à query preparada.
Executa a query e verifica se a inserção foi bem-sucedida.
Define uma mensagem de sucesso ou erro, dependendo do resultado da inserção.
Fecha a declaração SQL e a conexão com o banco de dados.
Exibe um formulário HTML para adicionar um novo usuário.
Se uma mensagem (de sucesso ou erro) estiver definida, ela é exibida acima do formulário.
  -->