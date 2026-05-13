<?php
$resultado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $a = $_POST["a"];
    $b = $_POST["b"];
    $operacao = $_POST["operacao"];

    switch ($operacao) {
        case "soma":
            $resultado = $a + $b;
            break;

        case "sub":
            $resultado = $a - $b;
            break;

        case "multi":
            $resultado = $a * $b;
            break;

        case "div":
            if ($b != 0) {
                $resultado = $a / $b;
            } else {
                $resultado = "Erro: divisão por zero!";
            }
            break;

        case "pot":
            $resultado = pow($a, $b);
            break;

        case "raiz":
            if ($a >= 0) {
                $resultado = sqrt($a);
            } else {
                $resultado = "Erro: raiz de número negativo!";
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html>
<body>

<h1><?php echo 'Minha Calculadora!'; ?></h1>

<form method="POST" action="calculadora.php">
    a: <input type="text" name="a"><br>
    b: <input type="text" name="b"><br><br>

    <button type="submit" name="operacao" value="soma">Somar</button>
    <button type="submit" name="operacao" value="sub">Subtrair</button>
    <button type="submit" name="operacao" value="multi">Multiplicar</button>
    <button type="submit" name="operacao" value="div">Dividir</button>
    <button type="submit" name="operacao" value="pot">Potência</button>
    <button type="submit" name="operacao" value="raiz">Raiz</button>
</form>

<br>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Resultado: " . $resultado;
}
?>

</body>
</html>