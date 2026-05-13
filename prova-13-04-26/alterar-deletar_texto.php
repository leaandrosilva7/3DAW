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
    <h1>Alterar/Deletar QUESTÕES DE TEXTO</h1>
<?php 
if(file_exists("questoes_texto.txt")) {   
    $arq = fopen("questoes_texto.txt", "r");
    fgets($arq); // tirar cabeçalho

    $i = 0;
    while($linha = fgets($arq)) {
        if(trim($linha) == "") continue;
        $dados = explode(";", $linha);

        echo "
        <tr>
            <td>$dados[0]</td>
            <td><a href='deletar_texto.php?del=$i'>Deletar</a></td>
        </tr>
        ";
    }
} 
?>
</table>