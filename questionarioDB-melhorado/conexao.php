<?php

$host    = "localhost";
$usuario = "root";
$senha   = "";
$banco   = "questionario";

$conn = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conn) {
    die(json_encode(["erro" => "Erro ao conectar: " . mysqli_connect_error()]));
}

?>
