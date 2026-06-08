<?php

header("Content-Type: application/json");

include("conexao.php");

$acao = $_GET["acao"];


// LISTAR
if ($acao == "listar") {

    $resultado = mysqli_query($conn, "SELECT * FROM perguntas");

    $perguntas = [];

    while ($linha = mysqli_fetch_assoc($resultado)) {
        $perguntas[] = $linha;
    }

    echo json_encode($perguntas);


// CRIAR
} elseif ($acao == "criar") {

    $tipo     = $_GET["tipo"];
    $pergunta = $_GET["pergunta"];
    $resposta = $_GET["resposta"];

    mysqli_query($conn, "INSERT INTO perguntas (tipo, pergunta, resposta)
                         VALUES ('$tipo', '$pergunta', '$resposta')");


// EDITAR
} elseif ($acao == "editar") {

    $id       = $_GET["id"];
    $tipo     = $_GET["tipo"];
    $pergunta = $_GET["pergunta"];
    $resposta = $_GET["resposta"];

    mysqli_query($conn, "UPDATE perguntas
                         SET tipo = '$tipo', pergunta = '$pergunta', resposta = '$resposta'
                         WHERE id = $id");


// EXCLUIR
} elseif ($acao == "excluir") {

    $id = $_GET["id"];

    mysqli_query($conn, "DELETE FROM perguntas WHERE id = $id");

}

?>
