<?php
session_start();          //inicia sessão

include 'conexao.php';   // conecta BD

$mensagem = "";  // Inicializar variáveis para mensagens

if (isset($_GET['id']) && is_numeric($_GET['id'])) {   // Verificar se o ID do usuário foi passado pela URL
    $id_usuario = $_GET['id'];

    $sql = "SELECT id_usuario, nome, email FROM usuario WHERE id_usuario = ?";  // Busca usuário no BD
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if (!$usuario) {     // Se o usuário não for encontrado, redirecionar
        header("Location: listar_usuarios.php");
        exit();
    }
} else {
   
    header("Location: listar_usuarios.php");    // Se o ID não foi passado ou não é válido, redirecionar
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {     // Processar o formulário de alteração
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];          // A senha pode ser opcional para alterar

    $sql = "UPDATE usuario SET nome = ?, email = ?";   // Preparar a query SQL para atualizar os dados
    $tipos = "ss";
    $params = [$nome, $email];

    if (!empty($senha)) {        // Se a senha foi informada, atualizar também
        $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);
        $sql .= ", senha = ?";
        $tipos .= "s";
        $params[] = $senha_criptografada;
    }

    $sql .= " WHERE id_usuario = ?";
    $tipos .= "i";
    $params[] = $id_usuario;

    $stmt = $conn->prepare($sql);
    // Usar array_merge para combinar os tipos e parâmetros
    $stmt->bind_param($tipos, ...$params);

    if ($stmt->execute()) {
        $mensagem = "Dados do usuário atualizados com sucesso!";
        // Buscar novamente os dados atualizados para exibir no formulário
        $stmt_refresh = $conn->prepare("SELECT id_usuario, nome, email FROM usuario WHERE id_usuario = ?");
        $stmt_refresh->bind_param("i", $id_usuario);
        $stmt_refresh->execute();
        $result_refresh = $stmt_refresh->get_result();
        $usuario = $result_refresh->fetch_assoc();
        $stmt_refresh->close();
    } else {
        $mensagem = "Erro ao atualizar os dados do usuário: " . $stmt->error;
    }
    $stmt->close();
}

// Fechar a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Usuário</title>
</head>
<body>
    <h1>Alterar Usuário</h1>

    <?php if ($mensagem): ?>
        <p><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $usuario['id_usuario']); ?>">
        <div>
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
        </div>
        <br>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
        <br>
        <div>
            <label for="senha">Nova Senha (opcional):</label>
            <input type="password" id="senha" name="senha">
            <small>Deixe em branco para manter a senha atual.</small>
        </div>
        <br>
        <button type="submit">Salvar Alterações</button>
        <p><a href="listar_usuarios.php">Voltar para a Lista de Usuários</a></p>
    </form>
</body>
</html>