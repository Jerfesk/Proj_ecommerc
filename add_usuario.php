<?php

session_start();  //inicia a sessão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    include 'conexao.php';       // conexão com BD

    $nome = $_POST["nome"];      // dados da tabela
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    
    $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);// Cripto a senha antes de salvar no banco de dados
    
    $sql = "INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)";  //insere um novo usuário

    $stmt = $conn->prepare($sql);      // declaração SQL

    $stmt->bind_param("sss", $nome, $email, $senha_criptografada);  // Vincular os parâmetros

    if ($stmt->execute()) {                           // Executar a query
        $mensagem = "Usuário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar usuário: " . $stmt->error;
    }

    $stmt->close();  // Fechar a declaração

    $conn->close();  // Fechar a conexão
}
?>

<!DOCTYPE html>
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