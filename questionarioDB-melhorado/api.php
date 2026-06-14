<?php

// Diz ao navegador que a resposta virá em formato JSON
header("Content-Type: application/json");

// Inclui o arquivo de conexão com o banco
include("conexao.php");

// Lê qual ação o front-end quer executar
// $_POST lê dados enviados no corpo da requisição (payload)
$acao = $_POST["acao"];

// LISTAR - Retorna todas as perguntas do banco em JSON
if ($acao == "listar") {

    $resultado = mysqli_query($conn, "SELECT * FROM perguntas");

    $perguntas = [];

    // Percorre cada linha retornada e adiciona ao array
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $perguntas[] = $linha;
    }

    // Converte o array PHP para texto JSON e envia ao front-end
    echo json_encode($perguntas);


// CRIAR - Insere uma nova pergunta no banco
} elseif ($acao == "criar") {

    $tipo     = $_POST["tipo"];
    $pergunta = $_POST["pergunta"];
    $resposta = $_POST["resposta"];

    mysqli_query($conn, "INSERT INTO perguntas (tipo, pergunta, resposta)
                         VALUES ('$tipo', '$pergunta', '$resposta')");


// EDITAR - Atualiza uma pergunta existente pelo id
} elseif ($acao == "editar") {

    $id       = $_POST["id"];
    $tipo     = $_POST["tipo"];
    $pergunta = $_POST["pergunta"];
    $resposta = $_POST["resposta"];

    mysqli_query($conn, "UPDATE perguntas
                         SET tipo = '$tipo', pergunta = '$pergunta', resposta = '$resposta'
                         WHERE id = $id");


// EXCLUIR - Remove uma pergunta pelo id
} elseif ($acao == "excluir") {

    $id = $_POST["id"];

    mysqli_query($conn, "DELETE FROM perguntas WHERE id = $id");

}

?>
