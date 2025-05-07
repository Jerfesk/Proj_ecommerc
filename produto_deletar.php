<?php
session_start();
include 'conexao.php'; // Sua conexão com o banco

// VERIFICA SE O USUÁRIO ESTÁ LOGADO (exemplo básico, adapte conforme sua lógica de sessão)
// if (!isset($_SESSION["usuario_id"])) {
//     // Armazena uma mensagem de erro na sessão para ser exibida na página de login
//     $_SESSION['mensagem_feedback'] = "Você precisa estar logado para realizar esta ação.";
//     header("Location: login.php");
//     exit();
// }

define('DIRETORIO_UPLOAD', 'uploads/produtos/');
$_SESSION['mensagem_feedback'] = ""; // Inicializa a mensagem de feedback

if (isset($_GET['id'])) {
    $produto_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($produto_id) {
        // 1. Buscar o nome da imagem do produto antes de deletar do banco
        $sql_select_imagem = "SELECT imagem FROM produtos WHERE id = ?";
        $stmt_select = $conn->prepare($sql_select_imagem);
        
        if ($stmt_select) {
            $stmt_select->bind_param("i", $produto_id);
            $stmt_select->execute();
            $result_select = $stmt_select->get_result();
            
            if ($result_select->num_rows === 1) {
                $produto = $result_select->fetch_assoc();
                $nome_imagem_para_deletar = $produto['imagem'];

                // 2. Deletar o produto do banco de dados
                $sql_delete = "DELETE FROM produtos WHERE id = ?";
                $stmt_delete = $conn->prepare($sql_delete);

                if ($stmt_delete) {
                    $stmt_delete->bind_param("i", $produto_id);

                    if ($stmt_delete->execute()) {
                        // 3. Se a exclusão do banco foi bem-sucedida, deletar a imagem do servidor
                        if (!empty($nome_imagem_para_deletar)) {
                            $caminho_imagem_completo = DIRETORIO_UPLOAD . $nome_imagem_para_deletar;
                            if (file_exists($caminho_imagem_completo)) {
                                if (unlink($caminho_imagem_completo)) {
                                    $_SESSION['mensagem_feedback'] = "Produto e imagem associada deletados com sucesso!";
                                } else {
                                    $_SESSION['mensagem_feedback'] = "Produto deletado do banco, mas houve um erro ao deletar o arquivo da imagem do servidor.";
                                }
                            } else {
                                // Se o arquivo da imagem não existe, mas o registro sim (e foi deletado)
                                $_SESSION['mensagem_feedback'] = "Produto deletado com sucesso! (Arquivo de imagem não encontrado no servidor).";
                            }
                        } else {
                            // Se não havia imagem associada
                            $_SESSION['mensagem_feedback'] = "Produto deletado com sucesso! (Não havia imagem associada).";
                        }
                    } else {
                        $_SESSION['mensagem_feedback'] = "Erro ao deletar o produto do banco de dados: " . $stmt_delete->error;
                    }
                    $stmt_delete->close();
                } else {
                     $_SESSION['mensagem_feedback'] = "Erro ao preparar a consulta de exclusão: " . $conn->error;
                }
            } else {
                $_SESSION['mensagem_feedback'] = "Produto não encontrado para exclusão.";
            }
            $stmt_select->close();
        } else {
            $_SESSION['mensagem_feedback'] = "Erro ao preparar a consulta para buscar a imagem do produto: " . $conn->error;
        }
    } else {
        $_SESSION['mensagem_feedback'] = "ID de produto inválido para exclusão.";
    }
} else {
    $_SESSION['mensagem_feedback'] = "Nenhum ID de produto fornecido para exclusão.";
}

if (isset($conn)) {
    $conn->close();
}

// Redirecionar de volta para a lista de produtos
header("Location: produto_listar.php");
exit();

/*   ############ explicação ##########

Como Funciona:

Verificação de ID: O script primeiro verifica se um id foi passado pela URL ($_GET['id']) e se é um número inteiro válido.
Buscar Imagem: Antes de deletar o registro do produto no banco, ele faz uma consulta para obter o nome do arquivo da imagem (imagem) associada a esse produto. Isso é importante porque, uma vez que o registro é deletado do banco, não teríamos mais essa informação para encontrar o arquivo no servidor.
Deletar do Banco: Se o produto é encontrado, o script tenta deletar o registro da tabela produtos usando o ID.
Deletar Arquivo da Imagem:
Se a exclusão do banco for bem-sucedida e se existia um nome de imagem (!empty($nome_imagem_para_deletar)):
O script constrói o caminho completo para o arquivo da imagem (DIRETORIO_UPLOAD . $nome_imagem_para_deletar).
Verifica se o arquivo realmente existe (file_exists()).
Tenta deletar o arquivo do servidor usando unlink().
Mensagens de Feedback: Mensagens apropriadas de sucesso ou erro são armazenadas na variável de sessão $_SESSION['mensagem_feedback']. Estas mensagens serão exibidas na página produto_listar.php após o redirecionamento.
Redirecionamento: Independentemente do resultado (sucesso ou erro na operação), o usuário é redirecionado de volta para produto_listar.php.
Fechamento da Conexão: A conexão com o banco é fechada.
Para usar este script:

Certifique-se de que o link "Deletar" na sua página produto_listar.php esteja apontando corretamente para produto_deletar.php?id=ID_DO_PRODUTO.
O diretório uploads/produtos/ (definido por DIRETORIO_UPLOAD) deve ter permissão de escrita para o servidor PHP, para que o unlink() funcione.
Este script não produz nenhuma saída HTML diretamente; sua única função é processar a exclusão e redirecionar

*/
?>