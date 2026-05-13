<?php
$arquivo = "dados.txt";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["salvar"])) {

    if (!file_exists($arquivo)) {
        $arq = fopen($arquivo, "w");
        fwrite($arq, "nome;idade");
        fclose($arq);
    }

    $nome = $_POST["nome"];
    $idade = $_POST["idade"];
    $linha = $nome . ";" . $idade . "\n";

    $arq = fopen($arquivo, "a");
    fwrite($arq, $linha);
    fclose($arq);

    header("Location: index.php");
    exit();
}
