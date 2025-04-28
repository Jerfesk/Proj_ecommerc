<?php

session_start();  //inicia a sessão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    include 'conexao.php';       // conexão com BD

    $nome = $_POST["nome"];      // dados da tabela
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    
    $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);// Cripto a senha antes de salvar no banco de dados
    
    $sql = "INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)";  //insere um novo usuário

    $stmt = $conn->prepare($sql);      // declaração SQL, query é a "consulta" ao BD, seu resultado é guardado em $stmt

    $stmt->bind_param("sss", $nome, $email, $senha_criptografada);  // Vincular os parâmetros, o sss imdica todos são strings

    if ($stmt->execute()) {                           // Executar a query, verifica se cadastrado
        $mensagem = "Usuário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar usuário: " . $stmt->error;
    }

    $stmt->close();  // Fechar a declaração

    $conn->close();  // Fechar a conexão
}
?>

<!DOCTYPE html>        <!-- tipo HTML5  -->
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário</title>
</head>
<body>
    <h1>Adicionar Novo Usuário</h1>

    <?php if (isset($mensagem)): ?>
        <p><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div>
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <br>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <br>
        <div>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <br>
        <button type="submit">Cadastrar</button>
        <p><a href="adm.php">Voltar para Administração</a></p>
    </form>
</body>
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