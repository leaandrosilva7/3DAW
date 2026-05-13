<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $arquivo = "dados.txt";

    $arq = fopen($arquivo, "r");

    $novo = fgets($arq); // cabeçalho

    $i = 0;

    while ($linha = fgets($arq)) {

        if (trim($linha) != "") {

            if ($i == $_POST["id"]) {

                $novo .= $_POST["nome"] . ";" . $_POST["idade"] . "\n";

            } else {

                $novo .= $linha;
            }

            $i++;
        }
    }

    fclose($arq);

    $arq = fopen($arquivo, "w");
    fwrite($arq, $novo);
    fclose($arq);

    header("Location: index.php");
    exit();
}
?>