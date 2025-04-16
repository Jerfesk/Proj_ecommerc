<?php
session_start();

if (isset($_POST["adicionar"])) {
    $id_produto = $_POST["id_produto"];
    $quantidade = 1;

    if (isset($_SESSION["carrinho"][$id_produto])) {
        $_SESSION["carrinho"][$id_produto] += $quantidade;
    } else {
        $_SESSION["carrinho"][$id_produto] = $quantidade;
    }
}

    // Exibe o carrinho
if (isset($_SESSION["carrinho"])) {
    foreach ($_SESSION["carrinho"] as $id_produto => $quantidade) {
        // Busca os dados do produto no banco de dados
        include 'conexao.php';
        $sql = "SELECT * FROM produtos WHERE id = $id_produto";
        $result = $conn->query($sql);
        $produto = $result->fetch_assoc();

        echo $produto["nome"] . " - Quantidade: " . $quantidade . "<br>";
    }
}
?>