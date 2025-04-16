<?php
include 'conexao.php';

$sql = "SELECT * FROM produtos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<h2>" . $row["nome"] . "</h2>";
        echo "<p>" . $row["descricao"] . "</p>";
        echo "<p>Preço: R$" . $row["preco"] . "</p>";
        echo "<img src='" . $row["imagem"] . "' alt='" . $row["nome"] . "'>";
    }
} else {
    echo "Nenhum produto encontrado.";
}

$conn->close();
?>