<?php

$nome = "";
$idade = "";
$id = "";

if (isset($_GET["edit"])) {

    $arq = fopen("dados.txt", "r");

    // pula cabeçalho
    fgets($arq);

    $i = 0;

    while ($linha = fgets($arq)) {

            if ($i == $_GET["edit"]) {

                $dados = explode(";", $linha);

                $nome = $dados[0];
                $idade = $dados[1];
                $id = $i;
            }

            $i++;

    }

    fclose($arq);
}
?>

<form method="POST" action="update.php">

    <input type="text" name="nome" value="<?= $nome ?>" required>
    <input type="text" name="idade" value="<?= $idade ?>" required>

    <input type="hidden" name="id" value="<?= $id ?>">

    <button type="submit">Atualizar</button>

</form>
