<?php
// Cópia de produto_listar.php,
// mas removendo funcionalidades (alterar/deletar).

// Se sua página de e-commerce já inicia a sessão, esta linha pode ser opcional.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'conexao.php'; // Sua conexão com o banco

// Buscar produtos do banco de dados
$sql = "SELECT id, nome, descricao, preco, imagem FROM produtos ORDER BY nome ASC";
$result = $conn->query($sql);
$produtos = [];
$mensagem_erro_busca = ""; // Para armazenar mensagens de erro da busca
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
} else if (!$result) {
    // Para depuração, se a consulta falhar.
    $mensagem_erro_busca = "Erro ao buscar produtos: " . $conn->error;
    // Em um ambiente de produção, você pode querer logar este erro em vez de exibi-lo.
    // error_log("Erro ao buscar produtos em pro_list.php: " . $conn->error);
}

// Define o caminho base para as imagens.
if (!defined('CAMINHO_BASE_IMAGENS')) {
    define('CAMINHO_BASE_IMAGENS', 'uploads/produtos/');
}


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Produtos</title> <?php // Título alterado para o contexto da loja ?>
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
        /* Removidos estilos de .btn-primary, .btn-edit, .btn-delete se não forem usados
           ou podem ser mantidos se outros elementos os utilizarem.
           Estilos de botão caso você queira adicionar
           um botão de "Ver detalhes" ou "Adicionar ao carrinho" futuramente com a mesma classe.
        */
        .btn, .action-link {
            display: inline-block; padding: 6px 12px; margin-bottom: 0;
            font-size: 0.9em; font-weight: 400; line-height: 1.42857143;
            text-align: center; white-space: nowrap; vertical-align: middle;
            cursor: pointer; user-select: none; background-image: none;
            border: 1px solid transparent; border-radius: 3px;
            text-decoration: none; margin-right: 5px;
        }
        .btn-primary { /* Estilo para um possível botão principal da loja */
            color: #fff; background-color: #007bff; border-color: #007bff;
            margin-bottom: 20px;
        }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        /* Mantendo .btn-edit e .btn-delete caso use para outros fins, mas não serão usados para Ações */
        .btn-edit { color: #fff; background-color: #ffc107; border-color: #ffc107; }
        .btn-edit:hover { background-color: #e0a800; border-color: #d39e00; }
        .btn-delete { color: #fff; background-color: #dc3545; border-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; border-color: #bd2130; }

        .product-table {
            width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;
        }
        .product-table th, .product-table td {
            border: 1px solid #dee2e6; padding: 10px; text-align: left; vertical-align: middle;
        }
        .product-table th {
            background-color: #e9ecef; font-weight: 600; color: #495057;
        }
        .product-table td img.product-thumbnail {
            max-width: 80px; max-height: 80px; height: auto;
            border-radius: 3px; border: 1px solid #ddd;
        }
        .product-table td.actions-cell { /* Este estilo não será mais usado para os botões de ação */
            min-width: 130px; text-align: center;
        }
        .description-cell {
            max-width: 300px; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; /* Mantém em uma linha, use 'normal' para quebrar linha */
        }
        .form-message { /* Estilo para mensagens, pode ser usado para $mensagem_erro_busca */
            padding: 12px 18px; margin-bottom: 25px; border-radius: 3px;
            border: 1px solid transparent; text-align: center; font-size: 0.95em;
        }
        .form-message.success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-message.error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .nav-links { /* Estilo para navegação, removido o conteúdo mas o estilo pode ser útil */
            text-align: center; margin-top: 30px; padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .nav-links a { color: #007bff; text-decoration: none; margin: 0 10px; }
        .nav-links a:hover { text-decoration: underline; }

        .no-products {
            text-align: center; padding: 20px; color: #6c757d; font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nossos Produtos</h1> <?php // Título ajustado ?>

        <?php if (!empty($mensagem_erro_busca)): ?>
            <p class="form-message error">
                <?php echo htmlspecialchars($mensagem_erro_busca); ?>
            </p>
        <?php endif; ?>

        <?php // Botão "Cadastrar Novo Produto" REMOVIDO ?>
        <?php /* <a href="produto_cadastrar.php" class="btn btn-primary">Cadastrar Novo Produto</a> */ ?>

        <?php if (count($produtos) > 0): ?>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Descrição (trecho)</th>
                        <th>Preço</th>
                        <?php // Coluna "Ações" REMOVIDA ?>
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
                                $descricao_completa = strip_tags($produto['descricao']);
                                $descricao_curta = mb_substr($descricao_completa, 0, 70);
                                echo htmlspecialchars($descricao_curta);
                                if (mb_strlen($descricao_completa) > 70) {
                                    echo "...";
                                }
                                ?>
                            </td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <?php // Célula de ações com botões Alterar/Deletar REMOVIDA ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php if (empty($mensagem_erro_busca)): // Só mostra "nenhum produto" se não houve erro na busca ?>
            <p class="no-products">Nenhum produto disponível no momento.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php //  "Voltar à página inicial" ?>
        <div class="nav-links">
            <a href="index.php">Voltar à Página Inicial</a> <?php ?>
        </div>

    </div>
</body>
</html>
<?php
// Fechar a conexão se ela foi aberta por este script e não será mais usada.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>