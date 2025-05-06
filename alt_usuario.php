<?php
session_start(); // Inicia a sessão
include 'conexao.php'; // Conexão com o BD

$mensagem = ""; // Variável para exibir mensagens

// Verifica se um ID de usuário foi passado pela URL para edição
if (isset($_GET['id'])) {       //cria parâmetro de id na URL
    $id_usuario = $_GET['id'];  //o parâmetro se existir na URL, então fica orientado para edição

    // Query SQL para selecionar os dados do usuário com o ID fornecido
    $sql = "SELECT nome, email FROM usuario WHERE email = ?";  //define a string SQL, para receber os dados
    $stmt = $conn->prepare($sql);      //o resultado é armazenado em $stms em estrutura da query
    $stmt->bind_param("s", $id_usuario);  // vincula $id_usuario na query preparada
    $stmt->execute();               //executa a query SQL, com o valor $id_usuario
    $result = $stmt->get_result();  //a variável $result armazena o resultado da query executada

    if ($result->num_rows == 1) {           // verifica se o ID do usuario foi encontrado no BD
        $usuario = $result->fetch_assoc();  // Se usuario encontrado, associa um array $usuario os nomes das colunas nome e email
        $nome = $usuario['nome'];
        $email = $usuario['email'];
    } else {
        $mensagem = "Usuário não encontrado.";  // se não encontrar o usuário.
    }
    $stmt->close();   // Fecha a declaração preparada.
}

// Processa a alteração quando o formulário é submetido, indica que será efetuado a alteração.
if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    $id_original = $_POST['id_original'];
    $novo_nome = $_POST['nome'];
    $novo_email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Inicia a transação para garantir que nome/email e senha sejam atualizados juntos (opcional, mas recomendado)
    $conn->begin_transaction();  // inicia a alteração no BD, agrupa todas as informações sejam elas alteradas ou não, e se alguma falhar as alterações são desfeitas(roolback)
    $atualizacao_bem_sucedida = true;  // verificação booleana para certificar que as atualizações foram bem sucedidas.

    // Query SQL para atualizar nome e email
    $sql_atualizar_dados = "UPDATE usuario SET nome = ?, email = ?, dt_alt = now() WHERE email = ?";
    $stmt_dados = $conn->prepare($sql_atualizar_dados);
    $stmt_dados->bind_param("sss", $novo_nome, $novo_email, $id_original);

    if (!$stmt_dados->execute()) {
        $mensagem = "Erro ao atualizar dados do usuário: " . $stmt_dados->error;
        $atualizacao_bem_sucedida = false;
    }
    $stmt_dados->close();

    // Verifica se o campo de nova senha foi preenchido
    if (!empty($nova_senha)) {
        // Verifica se a nova senha e a confirmação coincidem
        if ($nova_senha === $confirmar_senha) {
            $senha_criptografada = password_hash($nova_senha, PASSWORD_DEFAULT);

            // Query SQL para atualizar a senha
            $sql_atualizar_senha = "UPDATE usuario SET senha = ? WHERE email = ?";
            $stmt_senha = $conn->prepare($sql_atualizar_senha);
            $stmt_senha->bind_param("ss", $senha_criptografada, $id_original);

            if (!$stmt_senha->execute()) {
                $mensagem = "Erro ao atualizar a senha: " . $stmt_senha->error;
                $atualizacao_bem_sucedida = false;
            }
            $stmt_senha->close();
        } else {
            $mensagem = "A nova senha e a confirmação não coincidem.";
            $atualizacao_bem_sucedida = false;
        }
    }

    if ($atualizacao_bem_sucedida) {
        $conn->commit();
        $mensagem = "Usuário atualizado com sucesso!";
    } else {
        $conn->rollback();
    }
}

$conn->close(); // Fecha a conexão
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

    <?php if (!empty($mensagem)): ?>
        <p><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <?php if (isset($usuario)): ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="id_original" value="<?php echo $email; ?>">
            <div>
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" required>
            </div>
            <br>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <br>
            <div>
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha">
                <small>Deixe em branco para não alterar a senha.</small>
            </div>
            <br>
            <div>
                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha">
            </div>
            <br>
            <button type="submit">Salvar Alterações</button>
            <p><a href="list_usuario.php">Voltar para Lista de Usuários</a></p>
        </form>
    <?php else: ?>
        <p><a href="list_usuario.php">Voltar para Lista de Usuários</a></p>
    <?php endif; ?>
</body>
</html>