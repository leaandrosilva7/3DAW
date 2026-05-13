
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudos p/ prova</title>
</head>

<body>
    <form method="POST" action="create.php">
        <input name="nome" type="text" required>
        <input name="idade" type="text" required>
        <button name="salvar" type="submit">Salvar</button>
    </form>
    <table border="1">
        <tr>
            <td>Nome</td>
            <td>Idade</td>
            <td>Ações</td>
        </tr>

        <?php

        if (file_exists("dados.txt")) {
            $arq = fopen("dados.txt", "r");
            fgets($arq);

            $i = 0;
            while ($linha = fgets($arq)) {

                $dados = explode(";", $linha);

                echo "<tr>
                    <td>$dados[0]</td>
                    <td>$dados[1]</td>

                    <td><a href='delete.php?del=$i'>Deletar</a>
                    <a href='edit.php?edit=$i'>Editar</a></td>
                    </tr>
                    ";
                $i++;
            }
        }


        ?>



    </table>
</body>

</html>