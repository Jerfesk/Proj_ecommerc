<?php
session_start();   // Iniciar a sessão

include 'conexao.php';    //conecta com BD

$mensagem = "";

// Verificar se o ID do usuário foi passado pela URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Processar a exclusão APÓS confirmação
    if (isset($_POST['confirmar_exclusao'])) {
        $sql = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);

        if ($stmt->execute()) {
            $mensagem = "Usuário deletado com sucesso!";
        } else {
            $mensagem = "Erro ao deletar usuário: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Buscar o nome do usuário para exibir na confirmação
        $sql_select = "SELECT nome FROM usuario WHERE id_usuario = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $id_usuario);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $usuario = $result_select->fetch_assoc();
        $stmt_select->close();

        // Se o usuário não for encontrado, redirecionar
        if (!$usuario) {
            header("Location: listar_usuarios.php");
            exit();
        }
    }
} else {
    // Se o ID não foi passado ou não é válido, redirecionar
    header("Location: listar_usuarios.php");
    exit();
}

// Fechar a conexão com o banco de dados (será fechada após o processamento ou no final da página)
if (isset($conn)) {
    $conn->close();
}
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

    <?php if ($mensagem): ?>
        <p><?php echo $mensagem; ?></p>
        <p><a href="listar_usuarios.php">Voltar para a Lista de Usuários</a></p>
    <?php elseif (isset($usuario)): ?>
        <p>Você tem certeza que deseja deletar o usuário <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong>?</p>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id_usuario); ?>">
            <button type="submit" name="confirmar_exclusao">Confirmar Exclusão</button>
            <button type="button" onclick="window.location.href='listar_usuarios.php'">Cancelar</button>
        </form>
    <?php else: ?>
        <p>Nenhum usuário selecionado para deletar.</p>
        <p><a href="listar_usuarios.php">Voltar para a Lista de Usuários</a></p>
    <?php endif; ?>
</body>
</html>