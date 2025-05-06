<?php
session_start(); // inicia sessão (necessário para mensagens flash, se houver)
include 'conexao.php'; // conexão

// Query SQL para selecionar todos os usuários, ordenados por nome
$sql = "SELECT nome, email FROM usuario ORDER BY nome ASC";
$result = $conn->query($sql);

// Verifica se houve erro na query
$error_message = null; // Variável para guardar mensagem de erro da query
if ($result === false) {
    // Em um app real, seria ideal logar o erro $conn->error
    $error_message = "Erro ao buscar a lista de usuários.";
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Usuários - Administração</title>
    <style>
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa; /* Cinza muito claro de fundo */
            color: #212529; /* Cor de texto principal (preto suave) */
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 800px; /* Aumentado para acomodar a tabela confortavelmente */
            margin: 40px auto;
            background-color: #ffffff; /* Fundo branco */
            padding: 30px 40px;
            border-radius: 4px;
            border: 1px solid #dee2e6; /* Borda cinza clara */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        h1 {
            color: #343a40; /* Cinza bem escuro */
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

        /* === Estilos Específicos para a Tabela === */
        .data-table {
            width: 100%;
            border-collapse: collapse; /* Remove espaços entre células */
            margin-bottom: 25px;
        }
        .data-table th,
        .data-table td {
            padding: 12px 10px; /* Padding vertical e horizontal */
            text-align: left;
            border-bottom: 1px solid #dee2e6; /* Linha divisória entre linhas */
            vertical-align: middle; /* Alinha conteúdo verticalmente */
        }
        .data-table thead th { /* Cabeçalho da tabela */
            background-color: #e9ecef; /* Fundo cinza claro */
            color: #495057; /* Texto cinza escuro */
            font-weight: 600;
            font-size: 0.9em; /* Fonte um pouco menor */
            text-transform: uppercase; /* Opcional */
            border-top: 1px solid #dee2e6; /* Linha no topo */
        }
        .data-table tbody tr:hover {
            background-color: #f1f3f5; /* Fundo cinza muito claro no hover */
        }
        .data-table td:last-child { /* Coluna de ações */
             width: 1%; /* Força a coluna a ser o menor possível */
             white-space: nowrap; /* Impede que os botões quebrem linha */
        }

        /* Estilos para links de Ação dentro da tabela */
        .action-link {
            display: inline-block;
            padding: 6px 12px; /* Padding menor para botões na tabela */
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.85em;
            margin-right: 6px; /* Espaço entre botões */
            color: #ffffff;
            border: 1px solid transparent;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            cursor: pointer; /* Indica clicabilidade */
        }
        .action-link:last-child {
             margin-right: 0; /* Remove margem do último botão */
        }
        .action-link.edit {
            background-color: #6c757d; /* Cinza */
            border-color: #6c757d;
        }
        .action-link.edit:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .action-link.delete {
            background-color: #495057; /* Cinza escuro (alternativa formal ao vermelho) */
            border-color: #495057;
        }
        .action-link.delete:hover {
            background-color: #343a40;
            border-color: #343a40;
        }
        /* Estilo para mensagem 'Nenhum usuário' */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #6c757d; /* Cinza */
            font-style: italic;
        }

        /* Estilo para o link 'Voltar' (Importado do adm.php) */
        .back-link-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .back-link {
            display: inline-block;
            background-color: #6c757d; /* Mesmo cinza dos botões de editar */
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            font-weight: 500;
            transition: background-color 0.2s ease;
            border: 1px solid transparent;
        }
        .back-link:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lista de Usuários</h1>

        <?php
        // Exibe mensagem flash da sessão, se existir
        if (isset($_SESSION['mensagem_lista'])) { // Usar um nome específico se esta página puder receber msgs
            $mensagem_tipo = (strpos(strtolower($_SESSION['mensagem_lista']), 'sucesso') !== false) ? 'success' : 'error';
            echo '<div class="message ' . $mensagem_tipo . '">' . htmlspecialchars($_SESSION['mensagem_lista']) . '</div>';
            unset($_SESSION['mensagem_lista']); // Limpa a mensagem
        }

        // Exibe erro de query, se houver
        if ($error_message !== null) {
             echo '<div class="message error">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["nome"]); ?></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td>
                                <a href='alt_usuario.php?id=<?php echo urlencode($row["email"]); ?>' class="action-link edit">Alterar</a>
                                <a href='del_usuario.php?id=<?php echo urlencode($row["email"]); ?>' class="action-link delete"
                                   onclick="return confirm('Deseja mesmo excluir o usuário com e-mail: <?php echo htmlspecialchars(addslashes($row["email"])); ?>?');">Deletar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php elseif ($error_message === null): // Só exibe 'nenhum usuário' se não houve erro na query ?>
            <p class="no-data">Nenhum usuário cadastrado.</p>
        <?php endif; ?>

        <div class="back-link-section">
            <a href="adm.php" class="back-link">Voltar para Administração</a>
        </div>

    </div> </body>
</html>
<?php
// Libera o resultado e fecha a conexão apenas se a query foi bem-sucedida
if ($result) {
    $result->free();
}
$conn->close();
?>