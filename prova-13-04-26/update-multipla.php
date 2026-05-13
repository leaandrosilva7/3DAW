<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $arq = fopen("questoes_multipla.txt", "r");

    $novo = fgets($arq); // cabeçalho

    $i = 0;

    $pergunta = $_POST["pergunta"];
    $respA = $_POST["respA"];
    $respB = $_POST["respB"];
    $respC = $_POST["respC"];
    $respD = $_POST["respD"];
    $gabarito = $_POST["gabarito"];

    while ($linha = fgets($arq)) {

            if ($i == $_POST["id"]) {

                $novo .= $pergunta . ";" . $respA . ";" . $respB . ";" . $respC . ";" . $respD . ";". $gabarito . "\n";

            } else {

                $novo .= $linha;
            }

            $i++;
    }

    fclose($arq);

    $arq = fopen("questoes_multipla.txt", "w");
    fwrite($arq, $novo);
    fclose($arq);

    header("Location: alterar-deletar_multipla.php");
    exit();
}
?>