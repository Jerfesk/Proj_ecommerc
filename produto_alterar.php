<?php
session_start();
include 'conexao.php'; // Sua conexão com o banco

// VERIFICA SE O USUÁRIO ESTÁ LOGADO (exemplo básico)
// if (!isset($_SESSION["usuario_id"])) {
//     header("Location: login.php");
//     exit();
// }

define('DIRETORIO_UPLOAD', 'uploads/produtos/');
$mensagem = "";
$produto = null;
$produto_id = null;

// 1. VERIFICAR SE O ID DO PRODUTO FOI PASSADO E BUSCAR DADOS (MÉTODO GET)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $produto_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($produto_id) {
        $sql = "SELECT id, nome, descricao, preco, imagem FROM produtos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $produto = $result->fetch_assoc();
        } else {
            $_SESSION['mensagem_feedback'] = "Produto não encontrado.";
            header("Location: produto_listar.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['mensagem_feedback'] = "ID de produto inválido.";
        header("Location: produto_listar.php");
        exit();
    }
}

// 2. PROCESSAR O FORMULÁRIO DE ALTERAÇÃO (MÉTODO POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto_id_post = filter_var($_POST['produto_id'], FILTER_VALIDATE_INT);
    $nome = $_POST["nome"];
    $descricao = $_POST["descricao"];
    $preco = $_POST["preco"];
    $imagem_atual_hidden = $_POST["imagem_atual_hidden"]; // Nome da imagem que estava no banco
    $nome_imagem_para_db = $imagem_atual_hidden; // Por padrão, mantém a imagem atual

    // Validação básica
    if (empty($nome) || empty($preco) || !$produto_id_post) {
        $mensagem = "Nome, Preço e ID do Produto são obrigatórios.";
        // Recarregar dados do produto para exibir no formulário novamente em caso de erro POST
        // (Isso é importante se a página for recarregada com erro sem redirecionar)
        if ($produto_id_post) {
            $sql_temp = "SELECT id, nome, descricao, preco, imagem FROM produtos WHERE id = ?";
            $stmt_temp = $conn->prepare($sql_temp);
            $stmt_temp->bind_param("i", $produto_id_post);
            $stmt_temp->execute();
            $result_temp = $stmt_temp->get_result();
            $produto = $result_temp->fetch_assoc(); // Atualiza $produto para re-renderizar o form
            $stmt_temp->close();
             // Mantém os valores POSTados para repreencher, exceto se $produto foi recarregado
            $produto['nome'] = $nome;
            $produto['descricao'] = $descricao;
            $produto['preco'] = $preco;
            // $produto['imagem'] já é $imagem_atual_hidden
        }

    } else {
        // Processamento da NOVA imagem, se enviada
        if (isset($_FILES["nova_imagem"]) && $_FILES["nova_imagem"]["error"] == UPLOAD_ERR_OK) {
            $arquivo_temporario = $_FILES["nova_imagem"]["tmp_name"];
            $nome_original_nova = $_FILES["nova_imagem"]["name"];
            $extensao_nova = strtolower(pathinfo($nome_original_nova, PATHINFO_EXTENSION));
            $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($extensao_nova, $tipos_permitidos)) {
                $nome_nova_imagem = uniqid('produto_', true) . '.' . $extensao_nova;
                $caminho_destino_nova = DIRETORIO_UPLOAD . $nome_nova_imagem;

                if (move_uploaded_file($arquivo_temporario, $caminho_destino_nova)) {
                    // Nova imagem carregada com sucesso, preparar para deletar a antiga
                    if (!empty($imagem_atual_hidden) && file_exists(DIRETORIO_UPLOAD . $imagem_atual_hidden)) {
                        unlink(DIRETORIO_UPLOAD . $imagem_atual_hidden); // Deleta a imagem antiga do servidor
                    }
                    $nome_imagem_para_db = $nome_nova_imagem; // Define a nova imagem para ir ao DB
                } else {
                    $mensagem = "Erro ao mover a nova imagem para o diretório de uploads.";
                    // Se erro no upload da nova, mantém a antiga no DB, $nome_imagem_para_db não muda.
                }
            } else {
                $mensagem = "Tipo de arquivo da nova imagem não permitido.";
            }
        } elseif (isset($_FILES["nova_imagem"]) && $_FILES["nova_imagem"]["error"] != UPLOAD_ERR_NO_FILE) {
             $mensagem = "Erro no upload da nova imagem: código " . $_FILES["nova_imagem"]["error"];
        }


        // Se não houve mensagem de erro até agora, prossegue para atualizar o banco
        if (empty($mensagem)) {
            $sql_update = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, imagem = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("ssdsi", $nome, $descricao, $preco, $nome_imagem_para_db, $produto_id_post);
                if ($stmt_update->execute()) {
                    $_SESSION['mensagem_feedback'] = "Produto alterado com sucesso!";
                    header("Location: produto_listar.php");
                    exit();
                } else {
                    $mensagem = "Erro ao alterar produto: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                 $mensagem = "Erro ao preparar a consulta de atualização: " . $conn->error;
            }
        }
        // Se houve erro, a página será recarregada e $mensagem exibida.
        // É preciso garantir que $produto ainda tenha os dados para preencher o formulário.
        // Se a mensagem foi setada, e $produto_id_post existe, recarregamos $produto:
        if (!empty($mensagem) && $produto_id_post && !$produto) { // Se $produto não foi carregado antes (ex: erro de ID no GET e agora erro no POST)
            $sql_temp = "SELECT id, nome, descricao, preco, imagem FROM produtos WHERE id = ?";
            $stmt_temp = $conn->prepare($sql_temp);
            $stmt_temp->bind_param("i", $produto_id_post);
            $stmt_temp->execute();
            $result_temp = $stmt_temp->get_result();
            $produto = $result_temp->fetch_assoc();
            $stmt_temp->close();
            // E se os dados do POST são mais recentes que os do banco (porque o usuário já tentou editar)
            if ($produto) {
                $produto['nome'] = $nome; // Usa o que o usuário digitou no POST
                $produto['descricao'] = $descricao;
                $produto['preco'] = $preco;
                $produto['imagem'] = $imagem_atual_hidden; // A imagem exibida será a que estava lá
            }
        }

    }
}

// Se $produto ainda é null (ex: acesso direto a produto_alterar.php sem ID, ou ID inválido no POST e falha ao recarregar)
if (!$produto && $_SERVER["REQUEST_METHOD"] !== "GET") { // Se não for GET e $produto não foi carregado
    // Isso pode acontecer se o usuário submeter um formulário com ID inválido (manipulado)
    // e não houver dados prévios de GET.
    // A verificação no início do POST já tenta recarregar $produto,
    // mas como uma salvaguarda final:
    $_SESSION['mensagem_feedback'] = "Erro ao carregar dados do produto para alteração.";
    // header("Location: produto_listar.php"); // Pode ser muito agressivo, depende da UX
    // exit();
    // Alternativamente, exibir uma mensagem na própria página:
    if(empty($mensagem)) $mensagem = "Não foi possível carregar os dados do produto. Verifique o ID ou tente novamente.";
    // E garantir que o formulário não tente acessar $produto['campo'] se $produto for null
}


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Produto - Painel de Administração</title>
    <style>
        /* COPIE AQUI O MESMO CSS USADO NAS OUTRAS PÁGINAS (adm.php, produto_cadastrar.php, etc.) */
        /* ... (body, .container, h1, .form-group, input, textarea, .btn-submit, .form-message, .nav-links) ... */
        body {
            font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0;
            background-color: #f8f9fa; color: #212529; display: flex;
            flex-direction: column; align-items: center; min-height: 100vh;
            padding-top: 20px; padding-bottom: 20px;
        }
        .container {
            width: 90%; max-width: 700px; margin-top: 20px; margin-bottom: 20px;
            background-color: #ffffff; padding: 30px 40px; border-radius: 4px;
            border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        h1 {
            color: #343a40; text-align: center; margin-top: 0; margin-bottom: 35px;
            border-bottom: 1px solid #dee2e6; padding-bottom: 15px;
            font-size: 1.8em; font-weight: 600;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px; color: #495057;
            font-weight: 600; font-size: 0.95em;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da;
            border-radius: 3px; box-sizing: border-box; font-size: 0.95em; color: #495057;
        }
        .form-group input[type="file"] { padding: 7px 12px; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #5a6268; outline: 0;
            box-shadow: 0 0 0 0.1rem rgba(108, 117, 125, 0.25);
        }
        .btn-submit { /* Pode usar um nome mais genérico como .btn-save ou .btn-primary */
            display: inline-block; background-color: #007bff; /* Azul para salvar/confirmar */
            color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 3px;
            font-weight: 500; transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid #007bff; cursor: pointer; font-size: 1em; margin-top: 10px;
        }
        .btn-submit:hover { background-color: #0056b3; border-color: #0056b3; }
        .form-message {
            padding: 12px 18px; margin-bottom: 25px; border-radius: 3px;
            border: 1px solid transparent; text-align: center; font-size: 0.95em;
        }
        .form-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        
        .current-image-container { margin-bottom: 15px; }
        .current-image-container img {
            max-width: 150px; max-height: 150px; border: 1px solid #ddd;
            border-radius: 3px; display: block;
        }
        .current-image-container p { font-size: 0.9em; color: #6c757d; margin-top: 5px;}

        .nav-links {
            text-align: center; margin-top: 30px; padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .nav-links a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .nav-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Alterar Produto</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="form-message <?php echo strpos(strtolower($mensagem), 'sucesso') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <?php if ($produto): // Só exibe o formulário se os dados do produto foram carregados ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <input type="hidden" name="produto_id" value="<?php echo htmlspecialchars($produto['id']); ?>">
            <input type="hidden" name="imagem_atual_hidden" value="<?php echo htmlspecialchars($produto['imagem']); ?>">

            <div class="form-group">
                <label for="nome">Nome do Produto:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="preco">Preço (R$):</label>
                <input type="number" id="preco" name="preco" step="0.01" min="0" value="<?php echo htmlspecialchars($produto['preco']); ?>" required>
            </div>

            <?php if (!empty($produto['imagem']) && file_exists(DIRETORIO_UPLOAD . $produto['imagem'])): ?>
            <div class="form-group current-image-container">
                <label>Imagem Atual:</label>
                <img src="<?php echo DIRETORIO_UPLOAD . htmlspecialchars($produto['imagem']); ?>" alt="Imagem Atual">
            </div>
            <?php elseif (!empty($produto['imagem'])): ?>
            <div class="form-group current-image-container">
                <label>Imagem Atual:</label>
                <p>Arquivo de imagem não encontrado no servidor (<?php echo htmlspecialchars($produto['imagem']); ?>).</p>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="nova_imagem">Alterar Imagem (opcional):</label>
                <input type="file" id="nova_imagem" name="nova_imagem" accept="image/png, image/jpeg, image/gif">
            </div>
            
            <button type="submit" class="btn-submit">Salvar Alterações</button>
        </form>
        <?php else: ?>
            <?php if(empty($mensagem)) { // Se não há produto E não há mensagem de erro principal, exibe uma padrão ?>
                 <p class="form-message error">Não foi possível carregar o produto para edição. Verifique o ID ou <a href="produto_listar.php">volte para a lista</a>.</p>
            <?php } ?>
        <?php endif; ?>

        <div class="nav-links">
            <a href="produto_listar.php">Cancelar e Voltar para Lista</a> | 
            <a href="adm.php">Painel de Administração</a>
        </div>
    </div>
</body>
</html>
<?php if (isset($conn)) { $conn->close(); } ?>


<!-- ###############  EXPLICAÇÃO   ################ 
  Explicações Chave:

CSS: Lembre-se de copiar todo o CSS das outras páginas para o bloco <style> e adicionar/ajustar os estilos que forneci (como .current-image-container).

Carregamento de Dados (GET):

O script primeiro verifica se um id foi passado via GET.
Busca os dados do produto correspondente no banco.
Se o produto não for encontrado ou o ID for inválido, redireciona para a lista de produtos com uma mensagem de erro.
Processamento do Formulário (POST):

Valida os dados recebidos.
Tratamento da Imagem:
$imagem_atual_hidden: Guarda o nome da imagem que estava no banco quando o formulário foi carregado.
$nome_imagem_para_db: Inicialmente, assume que a imagem não será alterada.
Se uma nova imagem ($_FILES["nova_imagem"]) for enviada e válida:
Ela é salva no diretório de uploads.
A imagem antiga (referenciada por $imagem_atual_hidden) é deletada do servidor usando unlink().
$nome_imagem_para_db é atualizado com o nome da nova imagem.
Atualização no Banco:
A query UPDATE atualiza nome, descrição, preço e o campo imagem (que conterá o nome da nova imagem ou o nome da imagem antiga se nenhuma nova foi enviada).
Após a atualização bem-sucedida, o usuário é redirecionado para produto_listar.php com uma mensagem de sucesso na sessão.
Tratamento de Erros no POST: Se ocorrer um erro durante o POST (validação, upload, ou DB), a variável $mensagem é preenchida. O script tenta recarregar os dados do produto ($produto) para que o formulário possa ser exibido novamente com os valores que o usuário tentou submeter ou os valores originais, e a mensagem de erro é mostrada.
Exibição da Imagem Atual: O formulário mostra a imagem atual do produto, se existir.

Campo Hidden imagem_atual_hidden: Este campo é crucial. Ele envia de volta para o script PHP o nome do arquivo da imagem que estava originalmente associada ao produto quando a página de alteração foi carregada. Isso permite que o script PHP saiba qual arquivo de imagem antigo deletar do servidor caso uma nova imagem seja carregada com sucesso.

Repreenchimento do Formulário:

Se o método for GET, os valores do banco preenchem o formulário.
Se o método for POST e houver um erro de validação (sem redirecionamento), a lógica tenta manter os valores que o usuário digitou no formulário para que ele não precise redigitar tudo. Isso é feito atualizando o array $produto com os valores de $_POST antes de renderizar o formulário novamente.
Importante:

Certifique-se de que o diretório uploads/produtos/ tenha permissão de escrita pelo servidor web.
Teste exaustivamente, incluindo casos como:
Alterar dados sem alterar a imagem.
Alterar dados e a imagem.
Tentar alterar um produto com ID inválido.
Enviar um tipo de arquivo de imagem inválido.
A lógica para lidar com o estado do formulário quando ocorre um erro no POST e a página é recarregada (sem redirecionar imediatamente) pode ser um pouco complexa para garantir que os dados corretos sejam exibidos. A abordagem de redirecionar sempre para a lista com uma mensagem na sessão simplifica isso, mas pode ser menos amigável se o erro for algo que o usuário pode corrigir no formulário imediatamente. O código acima tenta um equilíbrio. 


-->