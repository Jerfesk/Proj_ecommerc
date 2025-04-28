<?php
session_start();        // sessão do servidor
include 'conexao.php';  // cód. p conexão com banco

if (isset($_POST["login"])) {  // condiç de verificaç de login
    $email = $_POST["email"];  // se vdd, armaz o email digitado 
    $senha = $_POST["senha"];  // igual a de cima, mas armaz a senha

    $sql = "SELECT * FROM usuario WHERE email = '$email'"; //verif se o email armaz consta no banco
    $result = $conn->query($sql);                   // executa o sql definido na variavel $sql, c/ objeto $conn def. na conexão o result é armaz em $result

    if ($result->num_rows == 1) {                  // aqui a verificaç do email, se encontrado $result = 1(vdd) foi encontrado no banco
        $usuario = $result->fetch_assoc();         // se o usuário é encontrado, são carregadas sua chaves, nomes das colunas
        
        if (password_verify($senha, $usuario["SENHA"])) {

         //($senha == $usuario["SENHA"])                // verifia usuaria sem cripto
         //(password_verify($senha, $nome["SENHA"]))   // para verificar a senha, esta é a função se a senha corresponde a hash senha armaz no banco


            $_SESSION["usuario_id"] = $usuario["id"];   // se a senha estiver correta, esta linha armaz o valor da coluna id do usuário, o ID dele está presentes em todas as pg do site
            header("Location: adm.php");        // redireciona o usuario para pg adm
            exit();                                     // exit() após o header para garantir que o script pare de ser executado
            
            //echo "Login realizado com sucesso!";  //se o logim for bem sucedido está mensagem aparece

        } else {                                  // aqui começa o else/caso contrário, a senha fornecida não contar no hash armaz
            echo "Senha incorreta.";             // aparece está mensagem.. . caso senha incorreta
        }

    } else {                                    // este é o caso se já no email não for encontrado
        echo "Usuário não encontrado.";         // mensagem de erro.
    }
}

$conn->close();    // fecha a conexão com o banco
?>        <!-- termino do php -->              

<form method="post" action="login.php">                       <!-- define um formulário, method = serao enviados para o servidor atrave HTTP POST, action = para onde serão levado os dados para analise no caso login.php --> 
    <input type="email" name="email" placeholder="Email">     <!-- cria o campo de texto para usuário, onde o formato esta em email e será usado no $_POST para validação -->
    <input type="password" name="senha" placeholder="Senha">  <!-- idéi similar a de cima, mas para senha, tambem usado $_POST que sera usado no php -->
    <button type="submit" name="login">Login</button>         <!-- cria o botão, p/ enviar o formulário, como especificado irá para a arquivo action -->
    <br>
    <p><a href="index.php">Voltar</a></p>
</form>