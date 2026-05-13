<?php
    // $msg = "";

if(isset($_POST["nome"]) && isset($_POST["idade"])) {
    // isset para especificar quais dados devem estar sendo passados para cadastro
    $nome = $_POST["nome"];
    $idade = $_POST["idade"];
    
    $linha = "\n$nome;$idade;";


    if(!file_exists("alunos.txt")) {
        $arq = fopen("alunos.txt", "w") or die("deu ruim");
        fwrite($arq, $linha);
        fclose($arq);
    }

    $arq = fopen("alunos.txt", "a");
    fwrite($arq, $linha);
    fclose($arq);   
}


    if(isset($_POST['mostrar'])){
    $arqR = fopen("alunos.txt", 'r');
    // Cria tabela
    echo "<table >";
    echo "<tr><th>Nome</th><th>Idade</th></tr>";

    while(!feof($arqR)) {
        $linha = fgets($arqR);
        if($linha != ""){
            $colunaDados = explode(";", $linha);
            echo "<tr>";
            echo "<td>".$colunaDados[0]."</td>";
            echo "<td>".$colunaDados[1]."</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    fclose($arqR);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alunos</title>
</head>
<body>

    <form action="" method="POST">
        <label for="">Nome: </label>
        <input type="text" name="nome">
        <label for="">Idade: </label>
        <input type="number" name="idade">
        <button type="submit">Cadastrar aluno</button>
    </form>
    
    <form action="" method="POST">
    <button type="submit" name="mostrar">Mostrar Alunos</button>
    </form>
    
</body>
</html>