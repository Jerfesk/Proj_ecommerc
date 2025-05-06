<?php
session_start();
include 'conexao.php'; // Conexão com o BD

$mensagem = ''; // Variável para guardar a mensagem para a sessão
$acao = ''; // Ação a ser tomada: 'confirmar', 'deletar', 'erro'
$id_usuario = null; // Email/ID do usuário recebido

// --- 1. Determinar a Ação e o ID ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_delete']) && isset($_POST['id_usuario'])) {
    // Usuário confirmou a deleção via POST
    $acao = 'deletar';
    $id_usuario = $_POST['id_usuario'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Requisição inicial para confirmar a deleção via GET
    $acao = 'confirmar';
    $id_usuario = $_GET['id'];
} else {
    // Nenhuma ação válida ou ID faltando
    $acao = 'erro';
    $mensagem = "Requisição inválida ou ID do usuário não fornecido.";
}

// --- 2. Processar a Ação ---

// ## Ação de Deletar ##
if ($acao === 'deletar') {
    $sql = "DELETE FROM usuario WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $mensagem = "Erro CRÍTICO ao preparar query: " . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("s", $id_usuario);
        if ($stmt->execute()) {
            // Usar htmlspecialchars para segurança caso exiba o email na mensagem
            $mensagem = "Usuário '" . htmlspecialchars($id_usuario) . "' deletado com sucesso!";
        } else {
            $mensagem = "Erro ao deletar usuário: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
    $conn->close();

    // Guarda a mensagem na sessão para exibir em adm.php
    $_SESSION['mensagem_adm'] = $mensagem;
    // Redireciona para adm.php
    header('Location: adm.php');
    // Garante que o script pare após o redirecionamento
    exit();

}
// ## Ação de Erro (se quisermos redirecionar também) ##
elseif ($acao === 'erro') {
     $conn->close(); // Fecha conexão se aberta
     // Guarda a mensagem de erro na sessão
     $_SESSION['mensagem_adm'] = $mensagem;
     // Redireciona para adm.php
     header('Location: adm.php');
     // Garante que o script pare
     exit();
}

// ## Ação de Confirmar ##
// Se a ação for 'confirmar', o script continua e exibe o HTML abaixo.
// A conexão será fechada após o HTML ou antes, se não for mais necessária.
// Se $acao não for 'confirmar', algo deu errado antes (e já deveria ter redirecionado).
if ($acao !== 'confirmar') {
     // Segurança extra: se não for confirmar e não redirecionou, redireciona agora.
     $conn->close();
     $_SESSION['mensagem_adm'] = 'Ocorreu um erro inesperado no processo de deleção.';
     header('Location: adm.php');
     exit();
}

// Fecha a conexão aqui se ainda estiver aberta (apenas no caso de confirmação)
$conn->close();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Deleção de Usuário</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        h1 { text-align: center; color: #333; }
        .confirmation-box {
            border: 1px solid #ddd;
            padding: 30px;
            margin: 30px auto;
            display: block;
            width: fit-content; /* Ajusta a largura ao conteúdo */
            max-width: 500px; /* Define uma largura máxima */
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center; /* Centraliza texto e botões */
        }
        .confirmation-box p { font-size: 1.1em; margin-bottom: 25px; color: #555; }
        .confirmation-box strong { color: #000; }
        .confirmation-box button, .confirmation-box a {
            text-decoration: none;
            padding: 12px 25px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1em;
            margin: 0 10px; /* Espaçamento entre botões */
            display: inline-block; /* Permite margem e padding */
            border: none; /* Remove borda padrão */
        }
        .button-delete { background-color: #dc3545; color: white; }
        .button-delete:hover { background-color: #c82333; }
        .button-cancel { background-color: #6c757d; color: white; }
        .button-cancel:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <h1>Confirmar Deleção</h1>

    <div class="confirmation-box">
        <p>Deseja mesmo excluir o usuário com e-mail: <br><strong><?php echo htmlspecialchars($id_usuario); ?></strong>?</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
             <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
             <button type="submit" name="confirmar_delete" class="button-delete">Sim, excluir</button>
        </form>
        <a href="adm.php" class="button-cancel">Não, cancelar</a>
    </div>

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