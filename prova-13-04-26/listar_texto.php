<style>
    table, td {
        border: 1px solid #000;
    }

    td {
        padding: 6px;
    }
</style>
<a href="/" >Voltar</a>
<table>
<?php 
if(file_exists("questoes_texto.txt")) {   
    $arq = fopen("questoes_texto.txt", "r");
    fgets($arq); // tirar cabeçalho

    while($linha = fgets($arq)) {
        if(trim($linha) == "") continue;
        $dados = explode(";", $linha);

        echo "
        <tr>
            <td>$dados[0]</td>
            <td>$dados[1]</td>
        </tr>
        ";
    }
} 
?>
</table>