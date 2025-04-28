<?php
session_start(); // Inicia a sessão
include 'conexao.php'; // Conexão com o BD

$mensagem = ""; // Variável para exibir mensagens

// Verifica se um ID de usuário foi passado pela URL para deleção
if (isset($_GET['id'])) {       //quando click em deletas na lista, o email é dado parâmetro de URL 
    $id_usuario = $_GET['id'];  // $_GET é um array associativo que tem variáveis passadas para script da URL

    // Query SQL para deletar o usuário com o ID fornecido
    $sql = "DELETE FROM usuario WHERE email = ?";  // identifica o email na tabela usuario que deve ser deletado
    $stmt = $conn->prepare($sql);           // verifica a conexao com BD e cria uma variável $stmt
    $stmt->bind_param("s", $id_usuario);  //vincula a variável id_usuario ao ? especifica tipo s "string"

    if ($stmt->execute()) {     // se for deletado retorna true e mensagem de sucesso ou false msg de erro.
        $mensagem = "Usuário deletado com sucesso!";  
    } else {
        $mensagem = "Erro ao deletar usuário: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close(); // Fecha a conexão
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deletar Usuário</title>
</head>
<body>
    <h1>Deletar Usuário</h1>

    <?php if (!empty($mensagem)): ?>
        <p><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <p><a href="list_usuario.php">Voltar para Lista de Usuários</a></p>
</body>
</html>

<!--
Inicia a sessão.
Inclui o arquivo de conexão com o banco de dados.
Verifica se um ID de usuário (id) foi passado pela URL.
Se um ID foi passado, prepara e executa uma query SQL para deletar o usuário com esse ID (email).
Define uma mensagem de sucesso ou erro com base no resultado da deleção.
Fecha a declaração SQL e a conexão com o banco de dados.
Exibe um título "Deletar Usuário".
Exibe a mensagem de sucesso ou erro, se houver.
Fornece um link para voltar à página de listagem de usuários.
-->