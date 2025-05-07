<?php
session_start();
include 'conexao.php'; // Sua conexão com o banco

// VERIFICA SE O USUÁRIO ESTÁ LOGADO (exemplo básico, adapte conforme sua lógica de sessão)
// if (!isset($_SESSION["usuario_id"])) {
//     header("Location: login.php");
//     exit();
// }

$mensagem = ""; // Para feedback ao usuário

// Define o diretório de upload
define('DIRETORIO_UPLOAD', 'uploads/produtos/');

// Cria o diretório se não existir
if (!is_dir(DIRETORIO_UPLOAD)) {
    mkdir(DIRETORIO_UPLOAD, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $descricao = $_POST["descricao"];
    $preco = $_POST["preco"];
    $nome_imagem = null;

    // Validação básica (adicione mais conforme necessário)
    if (empty($nome) || empty($preco)) {
        $mensagem = "Nome e Preço são campos obrigatórios.";
    } else {
        // Processamento do upload da imagem
        if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == 0) {
            $arquivo_temporario = $_FILES["imagem"]["tmp_name"];
            $nome_original = $_FILES["imagem"]["name"];
            $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
            
            // Tipos de imagem permitidos
            $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($extensao, $tipos_permitidos)) {
                // Gera um nome único para a imagem
                $nome_imagem = uniqid('produto_', true) . '.' . $extensao;
                $caminho_destino = DIRETORIO_UPLOAD . $nome_imagem;

                if (move_uploaded_file($arquivo_temporario, $caminho_destino)) {
                    // Imagem carregada com sucesso
                } else {
                    $mensagem = "Erro ao mover a imagem para o diretório de uploads.";
                    $nome_imagem = null; // Garante que não salve nome de imagem se o upload falhou
                }
            } else {
                $mensagem = "Tipo de arquivo de imagem não permitido. Use JPG, JPEG, PNG ou GIF.";
                $nome_imagem = null;
            }
        } elseif (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] != UPLOAD_ERR_NO_FILE) {
            // Se houve um erro no upload que não seja "nenhum arquivo enviado"
            $mensagem = "Erro no upload da imagem: código " . $_FILES["imagem"]["error"];
        }

        // Só prossegue para inserir no banco se não houver mensagem de erro de imagem (ou se imagem for opcional)
        if (empty($mensagem) || $nome_imagem !== null || (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] == UPLOAD_ERR_NO_FILE)) {
             // Se $mensagem não está vazia por erro de nome/preço, mas o upload foi ok ou não houve tentativa.
            if (empty($mensagem) && (empty($_POST["nome"]) || empty($_POST["preco"]))) {
                 $mensagem = "Nome e Preço são campos obrigatórios."; // Redefine se o erro era outro
            } else if (empty($mensagem)) {

                // Inserir no banco de dados
                $sql = "INSERT INTO produtos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssds", $nome, $descricao, $preco, $nome_imagem);
                    if ($stmt->execute()) {
                        $mensagem = "Produto cadastrado com sucesso!";
                        // Limpar os campos ou redirecionar
                        $_POST = array(); // Limpa o POST para o formulário aparecer vazio
                    } else {
                        $mensagem = "Erro ao cadastrar produto: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $mensagem = "Erro ao preparar a consulta: " . $conn->error;
                }
            }
        }
    }
    // $conn->close(); // Feche a conexão no final do script ou onde for apropriado
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto - Painel de Administração</title>
    <style>
        /* COPIE AQUI O MESMO CSS USADO NA adm.php, login.php, etc. */
        /* ... (incluindo body, .container, h1, .form-group, input, .btn-submit, .form-message, .back-section, .back-link) ... */
        body {
            font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0;
            background-color: #f8f9fa; color: #212529; display: flex;
            flex-direction: column; align-items: center; min-height: 100vh;
            padding-top: 20px; padding-bottom: 20px; /* Para evitar que o container cole nas bordas */
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
        .btn-submit {
            display: inline-block; background-color: #28a745; color: #ffffff;
            padding: 10px 20px; text-decoration: none; border-radius: 3px;
            font-weight: 500; transition: background-color 0.2s ease, border-color 0.2s ease;
            border: 1px solid #28a745; cursor: pointer; font-size: 1em; margin-top: 10px;
        }
        .btn-submit:hover { background-color: #218838; border-color: #1e7e34; }
        .form-message {
            padding: 12px 18px; margin-bottom: 25px; border-radius: 3px;
            border: 1px solid transparent; text-align: center; font-size: 0.95em;
        }
        .form-message.success { /* Para mensagens de sucesso */
            background-color: #d4edda; color: #155724; border-color: #c3e6cb;
        }
        .form-message.error { /* Para mensagens de erro */
            background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;
        }
        .nav-links { /* Estilo para os links de navegação no final */
            text-align: center; margin-top: 30px; padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .nav-links a {
            color: #007bff; text-decoration: none; margin: 0 10px;
        }
        .nav-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastrar Novo Produto</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="form-message <?php echo strpos(strtolower($mensagem), 'sucesso') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome do Produto:</label>
                <input type="text" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao"><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="preco">Preço (R$):</label>
                <input type="number" id="preco" name="preco" step="0.01" min="0" value="<?php echo isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="imagem">Imagem do Produto:</label>
                <input type="file" id="imagem" name="imagem" accept="image/png, image/jpeg, image/gif">
            </div>
            
            <button type="submit" class="btn-submit">Cadastrar Produto</button>
        </form>

        <div class="nav-links">
            <a href="produto_listar.php">Ver Lista de Produtos</a> | 
            <a href="adm.php">Painel de Administração</a>
        </div>
    </div>
</body>
</html>
<?php if (isset($conn)) { $conn->close(); } ?>

<!-- 

       EXPLICAÇÃO 
CSS: No bloco <style>, você deve copiar e colar todo o CSS que usamos para a adm.php e outras páginas. Eu coloquei um esqueleto dos estilos mais importantes que adaptamos para os formulários. Certifique-se de que ele esteja completo. Adicionei classes .success e .error para .form-message.
Upload de Imagem:
O formulário tem enctype="multipart/form-data", que é essencial para uploads.
O PHP verifica se um arquivo foi enviado, valida o tipo e tamanho (você pode adicionar validação de tamanho se desejar), gera um nome único e o move para o diretório uploads/produtos/.
O nome da imagem (ou null se não houver imagem ou erro) é salvo no banco.
Validação: A validação no exemplo é básica. Você deve expandi-la conforme necessário (ex: tamanho máximo para descrição, formato do preço, etc.).
Feedback: A variável $mensagem é usada para mostrar feedback ao usuário.
Segurança: htmlspecialchars() é usado para exibir dados no formulário (evitar XSS ao recarregar após erro) e na mensagem. Prepared statements são usados para a inserção no banco.
Manter Dados no Formulário: Se houver um erro (ex: de validação, mas não de cadastro em si), os valores que o usuário digitou são mantidos nos campos do formulário. Se o cadastro for bem-sucedido, $_POST é limpo para que o formulário apareça vazio para um novo cadastro.
Navegação: Links para "Ver Lista de Produtos" (que criaremos a seguir) e "Painel de Administração" foram adicionados.
Conexão: O fechamento da conexão $conn->close(); foi adicionado no final com uma verificação.
A seguir, criaremos produto_listar.php. Me diga se você quer prosseguir com ele ou se tem alguma dúvida ou ajuste para o produto_cadastrar.php!

############ SOBBRE AS IMAGENS ##############

Resumo Prático para Você (XAMPP + VSCode):

No seu VSCode, abra a pasta do seu projeto (ex: C:\xampp\htdocs\minha_loja_php\).
Dentro dessa pasta, crie uma nova pasta chamada uploads. (Clique com o botão direito no explorador de arquivos do VSCode, "New Folder").
Dentro da pasta uploads, crie outra pasta chamada produtos.
Agora você tem: minha_loja_php/uploads/produtos/.
No seu arquivo produto_cadastrar.php (e depois em produto_alterar.php), a linha:
PHP

define('DIRETORIO_UPLOAD', 'uploads/produtos/');
Está correta, pois ela assume que o script PHP está rodando na pasta raiz do seu projeto (minha_loja_php), e o diretório de uploads é relativo a ele.
Quando um usuário enviar uma imagem através do formulário em produto_cadastrar.php:

O PHP receberá o arquivo temporariamente.
Gerará um nome único para ele (ex: produto_abc123.png).
Usará move_uploaded_file() para mover o arquivo da localização temporária para minha_loja_php/uploads/produtos/produto_abc123.png.
O nome produto_abc123.png (ou uploads/produtos/produto_abc123.png) será salvo na coluna imagem da sua tabela produtos no banco de dados.
Espero que isso deixe mais claro como o diretório de uploads funciona no seu ambiente de desenvolvimento! Se ainda tiver dúvidas, pode perguntar.



-->