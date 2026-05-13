<?php

$pergunta = "";
$respA = "";
$respB = "";
$respC = "";
$respD = "";

if (isset($_GET["edit"])) {
    $arq = fopen("questoes_multipla.txt", "r");

    // pula cabeçalho
    fgets($arq);

    $i = 0;
    while ($linha = fgets($arq)) {
            if ($i == $_GET["edit"]) {
                $dados = explode(";", $linha);

                $pergunta = $dados[0];
                $respA = $dados[1];
                $respB = $dados[2];
                $respC = $dados[3];
                $respD = $dados[4];
                $gabarito = $dados[4];
            }
            $i++;
    }
    fclose($arq);
}
?>

<form method="POST" action="update-multipla.php">

    <input type="text" name="pergunta" value="<?= $pergunta ?>" required>
    <input type="text" name="respA" value="<?= $respA ?>" required>
    <input type="text" name="respB" value="<?= $respB ?>" required>
    <input type="text" name="respC" value="<?= $respC ?>" required>
    <input type="text" name="respD" value="<?= $respD ?>" required>
    <input type="text" name="gabarito" value="<?= $gabarito ?>" required>

    <button type="submit">Atualizar</button>
</form>