<?php

if (isset($_GET["del"])) {
    $arq = fopen("dados.txt", "r");

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

    $arq = fopen("dados.txt", "w");
    fwrite($arq, $novo_conteudo);
    fclose($arq);

    header("Location: index.php");
    exit();
}
