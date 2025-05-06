<?php
session_start(); // Inicia a sessão
include 'conexao.php'; // Conexão com o BD

$mensagem = "";       // Variável para exibir mensagens
$mensagem_tipo = "error"; // Tipo da mensagem ('success' ou 'error')
$usuario = null;      // Array para guardar dados do usuário a ser editado
$nome = '';           // Variável para preencher o nome no form
$email = '';          // Variável para preencher o email no form
$id_original_get = null; // Guarda o ID original do GET

// --- Parte 1: Buscar dados do usuário para exibir no formulário (GET Request) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id_original_get = $_GET['id']; // Email original vindo da URL

    $sql_select = "SELECT nome, email FROM usuario WHERE email = ?";
    $stmt_select = $conn->prepare($sql_select);

    if ($stmt_select) {
        $stmt_select->bind_param("s", $id_original_get);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();

        if ($result_select->num_rows == 1) {
            $usuario = $result_select->fetch_assoc();
            $nome = $usuario['nome'];
            $email = $usuario['email']; // Email atual (pode ser alterado no form)
        } else {
            $mensagem = "Usuário não encontrado com o e-mail fornecido.";
            $mensagem_tipo = "error";
        }
        $stmt_select->close();
    } else {
        $mensagem = "Erro ao preparar a busca pelo usuário: " . $conn->error;
        $mensagem_tipo = "error";
    }
}

// --- Parte 2: Processar a atualização (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar se os dados necessários foram enviados
    if (isset($_POST['id_original'], $_POST['nome'], $_POST['email'])) {
        $id_original = $_POST['id_original']; // Email original (do campo hidden)
        $novo_nome = trim($_POST['nome']);
        $novo_email = trim($_POST['email']);
        $nova_senha = $_POST['nova_senha']; // Não usar trim em senhas
        $confirmar_senha = $_POST['confirmar_senha'];

        // Preencher variáveis para re-exibir o formulário em caso de erro
        $nome = $novo_nome;
        $email = $novo_email; // Usa o novo email para preencher o form se der erro

        // Inicia a transação
        $conn->begin_transaction();
        $atualizacao_bem_sucedida = true;
        $mensagem_erro_transacao = ''; // Guarda mensagens de erro específicas

        // 1. Atualizar nome e email
        // Verificar se o novo email já existe (se for diferente do original)
        $email_existe = false;
        if (strtolower($novo_email) !== strtolower($id_original)) {
            $sql_check_email = "SELECT email FROM usuario WHERE email = ? AND email != ?";
            $stmt_check = $conn->prepare($sql_check_email);
            if ($stmt_check) {
                $stmt_check->bind_param("ss", $novo_email, $id_original);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $email_existe = true;
                }
                $stmt_check->close();
            } else {
                 $mensagem_erro_transacao .= "Erro ao verificar e-mail existente. ";
                 $atualizacao_bem_sucedida = false;
            }
        }

        if ($email_existe) {
            $mensagem_erro_transacao .= "O novo e-mail informado ('" . htmlspecialchars($novo_email) . "') já está em uso por outro usuário. ";
            $atualizacao_bem_sucedida = false;
        } elseif ($atualizacao_bem_sucedida) { // Só continua se não houve erro na verificação
            $sql_atualizar_dados = "UPDATE usuario SET nome = ?, email = ?, dt_alt = now() WHERE email = ?";
            $stmt_dados = $conn->prepare($sql_atualizar_dados);
            if ($stmt_dados) {
                $stmt_dados->bind_param("sss", $novo_nome, $novo_email, $id_original);
                if (!$stmt_dados->execute()) {
                    $mensagem_erro_transacao .= "Erro ao atualizar dados: " . htmlspecialchars($stmt_dados->error) . ". ";
                    $atualizacao_bem_sucedida = false;
                }
                $stmt_dados->close();
            } else {
                $mensagem_erro_transacao .= "Erro ao preparar atualização de dados. ";
                $atualizacao_bem_sucedida = false;
            }
        }

        // 2. Atualizar senha (se fornecida e válida)
        if ($atualizacao_bem_sucedida && !empty($nova_senha)) {
            if ($nova_senha === $confirmar_senha) {
                $senha_criptografada = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql_atualizar_senha = "UPDATE usuario SET senha = ? WHERE email = ?"; // Usar novo email ou id_original? Usar o NOVO email se ele foi atualizado com sucesso.
                $email_referencia_senha = $novo_email; // Email a ser usado na cláusula WHERE da senha

                $stmt_senha = $conn->prepare($sql_atualizar_senha);
                if ($stmt_senha) {
                    $stmt_senha->bind_param("ss", $senha_criptografada, $email_referencia_senha);
                    if (!$stmt_senha->execute()) {
                        $mensagem_erro_transacao .= "Erro ao atualizar senha: " . htmlspecialchars($stmt_senha->error) . ". ";
                        $atualizacao_bem_sucedida = false;
                    }
                    $stmt_senha->close();
                } else {
                    $mensagem_erro_transacao .= "Erro ao preparar atualização de senha. ";
                    $atualizacao_bem_sucedida = false;
                }
            } else {
                $mensagem_erro_transacao .= "A nova senha e a confirmação não coincidem. ";
                $atualizacao_bem_sucedida = false;
            }
        }

        // 3. Finalizar transação
        if ($atualizacao_bem_sucedida) {
            $conn->commit();
            $mensagem = "Usuário atualizado com sucesso!";
            $mensagem_tipo = "success";
            // Guarda mensagem na sessão e redireciona para a lista
            $_SESSION['mensagem_lista'] = $mensagem; // Usar o mesmo nome da list_usuario.php
            header('Location: list_usuario.php');
            exit();
        } else {
            $conn->rollback();
            $mensagem = $mensagem_erro_transacao; // Define a mensagem de erro acumulada
            $mensagem_tipo = "error";
            // Mantém $usuario preenchido para reexibir o formulário
            $usuario = ['nome' => $nome, 'email' => $email]; // Recria $usuario com os dados submetidos
        }
    } else {
         $mensagem = "Erro: Dados do formulário incompletos.";
         $mensagem_tipo = "error";
         // Tenta recarregar dados originais se possível (se GET id ainda estiver disponível)
         if ($id_original_get) {
             // (O código para buscar o usuário novamente poderia ser repetido aqui,
             // mas por simplicidade vamos apenas mostrar o erro e o link de voltar)
             $usuario = null; // Indica que não podemos mostrar o form preenchido
         }
    }
}

// Fechar conexão apenas se não foi fechada antes (em caso de erro GET inicial)
if ($conn && $conn->ping()) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Usuário - Administração</title>
    <style>
        /* === Estilos Base (Iguais ao adm.php/list_usuario.php formal) === */
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
        .container {
            width: 90%;
            max-width: 600px; /* Largura adequada para formulário */
            margin: 40px auto;
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
            margin-bottom: 35px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            font-size: 1.8em;
            font-weight: 600;
        }
        .message { margin: 15px 0; padding: 15px; border: 1px solid transparent; border-radius: 4px; font-size: 1em; text-align: center;}
        .message.success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .message.error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }

        /* === Estilos Específicos para Formulários === */
        .form-group {
            margin-bottom: 20px; /* Espaço entre grupos de label/input */
        }
        .form-label {
            display: block; /* Label acima do input */
            margin-bottom: 6px;
            font-weight: 500; /* Peso da label */
            color: #495057; /* Cor da label */
        }
        .form-control {
            width: 100%; /* Ocupa toda a largura */
            padding: 10px 12px; /* Padding interno */
            font-size: 1em;
            border: 1px solid #ced4da; /* Borda cinza padrão */
            border-radius: 3px;
            box-sizing: border-box; /* Garante que padding não aumente a largura */
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control:focus {
            border-color: #80bdff; /* Borda azul no foco (padrão Bootstrap) */
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Sombra azul no foco */
        }
        .form-text {
            display: block;
            margin-top: 6px;
            font-size: 0.85em;
            color: #6c757d; /* Texto de ajuda cinza */
        }

        /* Ações do Formulário (Botões) */
        .form-actions {
            text-align: center; /* Centraliza botões */
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6; /* Linha antes dos botões */
        }
        .button { /* Estilo base para botões e links tipo botão */
            display: inline-block;
            padding: 10px 25px; /* Tamanho do botão */
            text-decoration: none;
            border-radius: 3px;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            cursor: pointer;
            border: 1px solid transparent;
            font-size: 1em;
            margin: 0 5px; /* Espaço entre botões */
        }
        .button-submit {
            background-color: #007bff; /* Azul para ação principal (Salvar) */
            color: #ffffff;
            border-color: #007bff;
        }
        .button-submit:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .button-cancel {
            background-color: #6c757d; /* Cinza para cancelar/voltar */
            color: #ffffff;
            border-color: #6c757d;
        }
        .button-cancel:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Alterar Usuário</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="message <?php echo $mensagem_tipo; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if ($usuario): // Exibe o formulário apenas se o usuário foi encontrado ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="id_original" value="<?php echo htmlspecialchars($id_original_get ?? $id_original ?? ''); ?>">

                <div class="form-group">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($nome); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="form-group">
                    <label for="nova_senha" class="form-label">Nova Senha:</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control" aria-describedby="senhaHelp">
                    <small id="senhaHelp" class="form-text">Deixe em branco para não alterar a senha atual.</small>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha" class="form-label">Confirmar Nova Senha:</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control">
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-submit">Salvar Alterações</button>
                    <a href="list_usuario.php" class="button button-cancel">Cancelar / Voltar</a>
                </div>
            </form>
        <?php elseif (empty($mensagem)): // Caso não encontrou usuário e nenhuma msg foi setada antes ?>
            <div class="message error">Usuário não especificado ou não encontrado.</div>
            <div class="form-actions">
                 <a href="list_usuario.php" class="button button-cancel">Voltar para Lista</a>
            </div>
        <?php else: // Caso tenha mensagem de erro (ex: usuário não encontrado no GET) e não deva mostrar form ?>
             <div class="form-actions">
                 <a href="list_usuario.php" class="button button-cancel">Voltar para Lista</a>
             </div>
        <?php endif; ?>
    </div></body>
</html>