<?php
session_start();
include 'conexao.php'; // Sua conexão com o banco

// VERIFICA SE O USUÁRIO ESTÁ LOGADO (exemplo básico, adapte conforme sua lógica de sessão)
// if (!isset($_SESSION["usuario_id"])) {
//     header("Location: login.php");
//     exit();
// }

$mensagem_feedback = "";
if (isset($_SESSION['mensagem_feedback'])) {
    $mensagem_feedback = $_SESSION['mensagem_feedback'];
    unset($_SESSION['mensagem_feedback']); // Limpa a mensagem após exibir
}


// Buscar produtos do banco de dados
$sql = "SELECT id, nome, descricao, preco, imagem FROM produtos ORDER BY nome ASC";
$result = $conn->query($sql);
$produtos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
} else if (!$result) {
    // Para depuração, se a consulta falhar
    $mensagem_feedback = "Erro ao buscar produtos: " . $conn->error;
}

define('CAMINHO_BASE_IMAGENS', 'uploads/produtos/');

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Produtos - Painel de Administração</title>
    <style>
        
        body {
            font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0;
            background-color: #f8f9fa; color: #212529; display: flex;
            flex-direction: column; align-items: center; min-height: 100vh;
            padding-top: 20px; padding-bottom: 20px;
        }
        .container {
            width: 95%; /* Um pouco mais largo para a tabela */
            max-width: 1100px; /* Máximo maior para a tabela */
            margin-top: 20px; margin-bottom: 20px;
            background-color: #ffffff; padding: 25px 35px; border-radius: 4px;
            border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        h1 {
            color: #343a40; text-align: center; margin-top: 0; margin-bottom: 30px;
            border-bottom: 1px solid #dee2e6; padding-bottom: 15px;
            font-size: 1.8em; font-weight: 600;
        }
        .btn, .action-link { /* Estilo base para botões e links de ação */
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 0.9em;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            background-image: none;
            border: 1px solid transparent;
            border-radius: 3px;
            text-decoration: none;
            margin-right: 5px; /* Espaço entre botões de ação */
        }
        .btn-primary { /* Botão para "Cadastrar Novo Produto" */
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            margin-bottom: 20px; /* Espaço abaixo do botão */
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-edit {
            color: #fff;
            background-color: #ffc107; /* Amarelo para editar */
            border-color: #ffc107;
        }
        .btn-edit:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        .btn-delete {
            color: #fff;
            background-color: #dc3545; /* Vermelho para deletar */
            border-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }
        .product-table th, .product-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }
        .product-table th {
            background-color: #e9ecef;
            font-weight: 600;
            color: #495057;
        }
        .product-table td img.product-thumbnail {
            max-width: 80px;
            max-height: 80px;
            height: auto;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        .product-table td.actions-cell {
            min-width: 130px; /* Para caber os dois botões */
            text-align: center;
        }
        .description-cell {
            max-width: 300px; /* Limita a largura da descrição */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap; /* Mantém em uma linha, use 'normal' para quebrar linha */
        }
        .form-message {
            padding: 12px 18px; margin-bottom: 25px; border-radius: 3px;
            border: 1px solid transparent; text-align: center; font-size: 0.95em;
        }
        .form-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .nav-links {
            text-align: center; margin-top: 30px; padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .nav-links a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .nav-links a:hover { text-decoration: underline; }

        .no-products { /* Mensagem se não houver produtos */
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lista de Produtos</h1>

        <?php if (!empty($mensagem_feedback)): ?>
            <p class="form-message <?php echo (strpos(strtolower($mensagem_feedback), 'sucesso') !== false || strpos(strtolower($mensagem_feedback), 'deletado') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($mensagem_feedback); ?>
            </p>
        <?php endif; ?>

        <a href="produto_cadastrar.php" class="btn btn-primary">Cadastrar Novo Produto</a>

        <?php if (count($produtos) > 0): ?>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Descrição (trecho)</th>
                        <th>Preço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td>
                                <?php if (!empty($produto['imagem']) && file_exists(CAMINHO_BASE_IMAGENS . $produto['imagem'])): ?>
                                    <img src="<?php echo CAMINHO_BASE_IMAGENS . htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="product-thumbnail">
                                <?php else: ?>
                                    Sem Imagem
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td class="description-cell" title="<?php echo htmlspecialchars($produto['descricao']); ?>">
                                <?php 
                                    // Exibe um trecho da descrição
                                    $descricao_curta = mb_substr(strip_tags($produto['descricao']), 0, 70); // Pega os primeiros 70 caracteres
                                    echo htmlspecialchars($descricao_curta);
                                    if (mb_strlen(strip_tags($produto['descricao'])) > 70) {
                                        echo "...";
                                    }
                                ?>
                            </td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td class="actions-cell">
                                <a href="produto_alterar.php?id=<?php echo $produto['id']; ?>" class="btn btn-edit">Alterar</a>
                                <a href="produto_deletar.php?id=<?php echo $produto['id']; ?>" class="btn btn-delete" onclick="return confirm('Tem certeza que deseja deletar este produto: <?php echo htmlspecialchars(addslashes($produto['nome'])); ?>? Esta ação não pode ser desfeita.');">Deletar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-products">Nenhum produto cadastrado ainda.</p>
        <?php endif; ?>

        <div class="nav-links">
            <a href="adm.php">Painel de Administração</a>
        </div>
    </div>
</body>
</html>
<?php if (isset($conn)) { $conn->close(); } ?>


<!-- ############## EXPLICAÇÃO ################ 
CSS:

Copie todo o CSS da adm.php e das outras páginas para o bloco <style>. Eu adicionei novos estilos específicos para a tabela (.product-table, th, td, .product-thumbnail, .actions-cell, .description-cell) e para os botões de ação na tabela (.btn-edit, .btn-delete).
Adaptei as classes .form-message.success e .form-message.error para feedback.
O .container tem uma largura máxima um pouco maior para acomodar bem a tabela.
PHP:

Busca todos os produtos da tabela produtos.
$mensagem_feedback: É usado para exibir mensagens de sucesso ou erro (por exemplo, após deletar um produto e ser redirecionado de volta para esta página). A mensagem é armazenada na sessão para persistir através do redirecionamento.
CAMINHO_BASE_IMAGENS: Constante definida para facilitar a referência ao diretório das imagens.
Tabela de Produtos:

Imagem: Verifica se o arquivo da imagem existe no diretório uploads/produtos/ antes de tentar exibi-lo. Se não houver imagem ou o arquivo não for encontrado, exibe "Sem Imagem".
Descrição: Exibe apenas um trecho da descrição (os primeiros 70 caracteres) para que a tabela não fique muito extensa. O atributo title na célula da descrição mostra a descrição completa ao passar o mouse sobre ela. Usei mb_substr para lidar corretamente com caracteres multibyte (como acentos) e strip_tags para remover HTML da descrição antes de cortar.
Preço: Formatado como moeda brasileira usando number_format().
Ações:
"Alterar": Leva para produto_alterar.php?id=ID_DO_PRODUTO.
"Deletar": Leva para produto_deletar.php?id=ID_DO_PRODUTO. Inclui um onclick com confirm() para pedir confirmação ao usuário antes de deletar. addslashes() foi usado no nome do produto dentro do confirm para evitar problemas com aspas no nome.
Navegação:

Um botão "Cadastrar Novo Produto" no topo da tabela.
Link para o "Painel de Administração" no final.
Sem Produtos: Se não houver produtos cadastrados, uma mensagem "Nenhum produto cadastrado ainda." é exibida.

Próximos Passos:

Certifique-se de que o CSS no bloco <style> esteja completo, copiando-o das outras páginas e adicionando os novos estilos que forneci.
Teste esta página. Você deverá ver a lista de produtos que cadastrou (se houver algum). 


-->