<?php

if (isset($_GET["del"])) {
    $arq = fopen("questoes_texto.txt", "r");

    // botando cabeçalho
    $novo_conteudo = fgets($arq);

    $i = 0;
    while ($linha = fgets($arq)) {

        if ($_GET["del"] != $i) {
            $novo_conteudo .= $linha;
        }

        $i++;
    }

    fclose($arq);

    $arq = fopen("questoes_texto.txt", "w");
    fwrite($arq, $novo_conteudo);
    fclose($arq);

    header("Location: alterar-deletar_texto.php");
    exit();
}