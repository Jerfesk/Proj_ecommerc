<?php
session_start();  //inicia sessão

include 'conexao.php';  //conexão

$sql = "SELECT nome, email FROM usuario";  // Query SQL para selecionar todos os usuários
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Usuários</title>
</head>
<body>
    <h1>Lista de Usuários</h1>

    <?php
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<thead><tr><th>Nome</th><th>Email</th><th>Ações</th></tr></thead>";  //<th>ID</th> retirado p/ adequar a saida do ID
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            //echo "<td>" . $row["id_usuario"] . "</td>";  // tive que comentar, pois o ID está AUTO_INCREMENT
            echo "<td>" . $row["nome"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>";
            echo "<a href='alt_usuario.php?id=" . $row["email"] . "'>Alterar</a> | ";
            echo "<a href='del_usuario.php?id=" . $row["email"] . "'>Deletar</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>Nenhum usuário cadastrado.</p>";
    }

    $conn->close();  // Fechar a conexão BD
    ?>

    <br>
    <p><a href="adm.php">Voltar para Administração</a></p>
</body>
</html>