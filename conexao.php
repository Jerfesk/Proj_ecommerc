<?php
$servername = "localhost";  // no local host
$username = "root";         // sempre esse user name e no mysqli...
$password = "";             // esta é a senha "vazio"
$dbname = "test";           // este nome
$port = 3307;               // criada essa porta, pois a 3306 teve problema.

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname, port: $port);

// Verifica a conexão
if ($conn->connect_error)
{
    die("Falha na conexão: " . $conn->connect_error);   // se der erro
}
else{
    //echo "conectado";                                   // se conseguir conectar
}
?>