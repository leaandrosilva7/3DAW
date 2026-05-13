<?php 
    if($_SERVER["REQUEST_METHOD"] && isset($_POST["salvar"])){
    if(!file_exists("questoes_texto.txt")) {
        $arq = fopen("questoes_texto.txt", "w");
        fwrite($arq, "pergunta;resposta\n");
        fclose($arq);
    }
    
    $pergunta = $_POST["pergunta"];
    $resposta = $_POST["resposta"];
    $linha = $pergunta . ";" . $resposta . "\n";

    $arq = fopen("questoes_texto.txt", "a");
    fwrite($arq, $linha);
    fclose($arq);
    }

    header("Location: index.html");
    exit();
?>