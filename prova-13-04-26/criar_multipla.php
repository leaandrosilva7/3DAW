<?php 
    if($_SERVER["REQUEST_METHOD"] && isset($_POST["salvar"])){

    if(!file_exists("questoes_multipla.txt")) {
        $arq = fopen("questoes_multipla.txt", "w");
        fwrite($arq, "pergunta;resposta\n");
        fclose($arq);
    }

    $pergunta = $_POST["pergunta"];
    $respA = $_POST["respA"];
    $respB = $_POST["respB"];
    $respC = $_POST["respC"];
    $respD = $_POST["respD"];
    $gabarito = $_POST["gabarito"];
    $linha = $pergunta . ";" . $respA . ";" . $respB . ";" . $respC . ";" . $respD . ";". $gabarito . "\n";

    $arq = fopen("questoes_multipla.txt", "a");
    fwrite($arq, $linha);
    fclose($arq);
    }

    header("Location: index.html");
    exit();
?>