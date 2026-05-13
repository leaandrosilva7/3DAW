// Incluir
<?php 
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if(file_exists("dados.txt")) {
        if(isset($_POST["sigla"]) && isset($_POST["nome"]) && isset($_POST["carga"])) {
            $sigla = $_POST["sigla"];
            $nome = $_POST["nome"];
            $carga = $_POST["carga"];
            $linha = "\n" . $sigla . ";" . $nome . ";" . $carga;
            $arq = fopen("dados.txt", "a");
            fwrite($arq, $linha);
            fclose($arq);
            }
        }
    }
    header("Location: index.html");
    exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividade Incluir</title>
</head>
<body>
    

    <h2>Incluir Aluno</h2>

    <form method="POST" action="incluir.php">
        <input placeholder="Sigla" name="sigla" type="text">
        <input placeholder="Nome" name="nome" type="text">
        <input placeholder="Carga" name="carga" type="number">
        <button type="submit">Cadastrar</button>
    </form>


</body>
</html>