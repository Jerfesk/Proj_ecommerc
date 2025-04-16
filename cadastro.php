<?php
include 'conexao.php';    //cód. para estabelecer conexão com BD

if (isset($_POST["cadastrar"])) {    // condicional if, POST = array, cadastrar dados

    $nome = $_POST["nome"];          // se if for vdd, armazena os dados na variavél $nome
    $email = $_POST["email"];        //similar ao de cima
    $senha = $_POST["senha"];
    //$senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);  // Criptografa a senha(passoword_hash), o resultado hash é armazenado na $senha

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES ('$nome', '$email', '$senha')";  // cria-se aqui um comando SQL, atribui-se valores a variaveis

    if ($conn->query($sql) === TRUE) {  //  executa o SQL construído, $conn é variavel que consta em conexao.php
        echo "Cadastro realizado com sucesso!";  // se a execução for bem sucedida retorna esta mesg
    } else {                     // se não for bem sucedido segue o próximo comando 
        echo "Erro: " . $sql . "<br>" . $conn->error;  // mensagem de erro 
    }
}

$conn->close();               // fecha a conexão com o BD
?>

<form method="post" action="cadastro.php">                       <!-- abertura de formulário-->
    <input type="text" name="nome" placeholder="Nome">           <!-- campo de entrada de nome, definição do array-->
    <input type="email" name="email" placeholder="Email">        <!-- campo de entrada de email -->
    <input type="password" name="senha" placeholder="Senha">     <!-- campo de entrada  de senha -->
    <button type="submit" name="cadastrar">Cadastrar</button>    <!-- botão de envio do formulário -->
</form>