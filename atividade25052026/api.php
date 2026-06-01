<?php
include("funcoes.php");

header("Content-Type: application/json");

$acao = $_GET["acao"] ?? $_POST["acao"] ?? "";

if($acao == "listar"){

    $texto   = listarPerguntas("texto");
    $multipla = listarPerguntas("multipla");

    echo json_encode(["texto" => $texto, "multipla" => $multipla]);

}elseif($acao == "salvar_texto"){

    $linha = $_POST["pergunta"].";".$_POST["resposta"];
    salvarPergunta("texto", $linha);
    echo json_encode(["ok" => true]);

}elseif($acao == "salvar_multipla"){

    $linha =
        $_POST["pergunta"].";".
        $_POST["a"].";".
        $_POST["b"].";".
        $_POST["c"].";".
        $_POST["d"].";".
        $_POST["gabarito"];

    salvarPergunta("multipla", $linha);
    echo json_encode(["ok" => true]);

}elseif($acao == "excluir"){

    excluirPergunta($_POST["tipo"], $_POST["id"]);
    echo json_encode(["ok" => true]);

}elseif($acao == "editar_texto"){

    $nova = $_POST["pergunta"].";".$_POST["resposta"];
    editarPergunta("texto", $_POST["id"], $nova);
    echo json_encode(["ok" => true]);

}elseif($acao == "editar_multipla"){

    $nova =
        $_POST["pergunta"].";".
        $_POST["a"].";".
        $_POST["b"].";".
        $_POST["c"].";".
        $_POST["d"].";".
        $_POST["gabarito"];

    editarPergunta("multipla", $_POST["id"], $nova);
    echo json_encode(["ok" => true]);

}else{
    echo json_encode(["erro" => "acao invalida"]);
}
?>
