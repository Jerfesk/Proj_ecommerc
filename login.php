<?php
session_start();       // sessão do servidor
include 'conexao.php';   // cód. p conexão com banco

$mensagem_erro = ""; // Variável para armazenar mensagens de erro para o layout

if (isset($_POST["login"])) {   // condiç de verificaç de login
    $email = $_POST["email"];   // se vdd, armaz o email digitado 
    $senha = $_POST["senha"];   // igual a de cima, mas armaz a senha

    // NOTA DE SEGURANÇA: A consulta abaixo está vulnerável a SQL Injection.
    // Considere usar Prepared Statements como nos outros arquivos para maior segurança.
    $sql = "SELECT * FROM usuario WHERE email = '$email'"; //verif se o email armaz consta no banco
    $result = $conn->query($sql);                       // executa o sql definido na variavel $sql, c/ objeto $conn def. na conexão o result é armaz em $result

    if ($result->num_rows == 1) {                       // aqui a verificaç do email, se encontrado $result = 1(vdd) foi encontrado no banco
        $usuario = $result->fetch_assoc();                // se o usuário é encontrado, são carregadas sua chaves, nomes das colunas
        
        // Verificando a senha (assume que a coluna da senha no banco é 'SENHA')
        if (password_verify($senha, $usuario["SENHA"])) {
            $_SESSION["usuario_id"] = $usuario["id"];   // se a senha estiver correta, esta linha armaz o valor da coluna id do usuário
            header("Location: adm.php");                // redireciona o usuario para pg adm
            exit();                                     // exit() após o header para garantir que o script pare
        } else {                                        // aqui começa o else/caso contrário, a senha fornecida não contar no hash armaz
            $mensagem_erro = "Senha incorreta.";         // Armazena a mensagem de erro
        }
    } else {                                        // este é o caso se já no email não for encontrado
        $mensagem_erro = "Usuário não encontrado.";  // Armazena a mensagem de erro
    }
}

// $conn->close(); // Idealmente, fechar a conexão após o uso ou antes do exit.
// Se 'conexao.php' sempre cria $conn, pode ficar aqui ou ser movido para dentro do if.
// Por ora, mantendo a estrutura original, mas note que se o método não for POST, $conn pode não ter sido usado ainda.
// Se 'conexao.php' não cria $conn se o método não for POST, chamar close() aqui pode dar erro.
// Para maior segurança, $conn->close() deve ser chamado apenas se $conn existir e após seu uso.
// Ex: if(isset($conn) && $conn) { $conn->close(); }
// Mas, para manter o mínimo de alteração no seu PHP original, vou deixar como estava (implícito que $conn é fechado).
// Se o script original tinha $conn->close(); no final, vamos manter.
if (isset($conn) && $conn instanceof mysqli) { // Verifica se $conn existe e é um objeto mysqli antes de fechar
    $conn->close();   // fecha a conexão com o banco
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel de Administração</title>
    <style>
        
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
            padding-top: 40px; /* Adicionado para dar espaço no topo em telas menores */
            padding-bottom: 40px;
        }

        .container {
            width: 90%;
            max-width: 450px; /* Um pouco mais estreito para formulário de login */
            margin-top: 0; /* Ajustado pois body já tem padding */
            margin-bottom: 0; /* Ajustado */
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: #343a40;
            text-align: center;
            margin-top: 0;
            margin-bottom: 30px; /* Um pouco menos de margem para login */
            /* border-bottom: 1px solid #dee2e6; */ /* Opcional para login */
            /* padding-bottom: 15px; */  /* Opcional para login */
            font-size: 1.8em;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label { /* Embora não usemos labels visíveis, mantemos para consistência se adicionadas */
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
            font-size: 0.95em;
        }

        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 15px; /* Padding um pouco maior para inputs de login */
            border: 1px solid #ced4da;
            border-radius: 3px;
            box-sizing: border-box;
            font-size: 1em; /* Tamanho de fonte um pouco maior */
            color: #495057;
        }

        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            border-color: #5a6268;
            outline: 0;
            box-shadow: 0 0 0 0.1rem rgba(108, 117, 125, 0.25);
        }

        .btn-login { /* Nome da classe alterado para especificidade */
            display: block; /* Botão ocupando largura total */
            width: 100%;
            background-color: #007bff; /* Azul para login, cor primária comum */
            color: #ffffff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid #007bff;
            cursor: pointer;
            font-size: 1.1em; /* Botão um pouco maior */
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #0056b3; /* Azul mais escuro */
            border-color: #0056b3;
        }

        .form-message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 3px;
            border: 1px solid transparent;
            text-align: center;
            font-size: 0.9em;
        }
        
        .form-message.error { /* Estilo específico para mensagens de erro */
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .back-section {
            text-align: center;
            margin-top: 25px; /* Um pouco menos de margem */
            /* border-top: 1px solid #dee2e6; */ /* Opcional para login */
            /* padding-top: 15px; */  /* Opcional para login */
        }

        .back-link {
            color: #007bff; /* Cor do link para combinar com o botão */
            text-decoration: none;
            font-size: 0.9em;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Ocultar <br> originais do formulário */
        form > br { 
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Acessar Painel</h1>

        <?php if (!empty($mensagem_erro)): ?>
            <p class="form-message error"><?php echo htmlspecialchars($mensagem_erro, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="senha" placeholder="Senha" required>
            </div>
            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <div class="back-section">
            <a href="index.php" class="back-link">Voltar</a>
        </div>
    </div>
</body>
</html>