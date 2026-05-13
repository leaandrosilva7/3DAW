<?php
include("funcoes.php");

$acao = $_GET["acao"] ?? "";

echo "<a href='index.php'>Voltar</a><br><br>";

if($acao == "criar_texto"){
?>

<form method="POST">

<input name="pergunta" placeholder="Pergunta"><br><br>

<input name="resposta" placeholder="Resposta"><br><br>

<button name="salvar">Salvar</button>

</form>

<?php

if(isset($_POST["salvar"])){

$linha = $_POST["pergunta"].";".$_POST["resposta"];

salvarPergunta("texto",$linha);

header("Location: perguntas.php?acao=listar");
exit();

}

}

/* MULTIPLA */
elseif($acao == "criar_multipla"){
?>

<form method="POST">

<input name="pergunta" placeholder="Pergunta"><br><br>
<input name="a" placeholder="A"><br><br>
<input name="b" placeholder="B"><br><br>
<input name="c" placeholder="C"><br><br>
<input name="d" placeholder="D"><br><br>
<input name="gabarito" placeholder="Gabarito"><br><br>

<button name="salvar">Salvar</button>

</form>

<?php

if(isset($_POST["salvar"])){

$linha =
$_POST["pergunta"].";".
$_POST["a"].";".
$_POST["b"].";".
$_POST["c"].";".
$_POST["d"].";".
$_POST["gabarito"];

salvarPergunta("multipla",$linha);

header("Location: perguntas.php?acao=listar");
exit();

}

}

/* LISTAR */
elseif($acao == "listar"){

echo "<h2>TEXTO</h2>";

$dados = listarPerguntas("texto");

foreach($dados as $id=>$linha){

echo $linha[0]." - ".$linha[1];

echo " <a href='perguntas.php?acao=editar&tipo=texto&id=$id'>Editar</a>";

echo " <a href='perguntas.php?acao=excluir&tipo=texto&id=$id'>Excluir</a>";

echo "<br><br>";

}

echo "<hr>";

echo "<h2>MULTIPLA</h2>";

$dados = listarPerguntas("multipla");

foreach($dados as $id=>$linha){

echo $linha[0];

echo " <a href='perguntas.php?acao=editar&tipo=multipla&id=$id'>Editar</a>";

echo " <a href='perguntas.php?acao=excluir&tipo=multipla&id=$id'>Excluir</a>";

echo "<br><br>";

}

}

/* EXCLUIR */
elseif($acao == "excluir"){

excluirPergunta($_GET["tipo"],$_GET["id"]);

header("Location: perguntas.php?acao=listar");
exit();

}

/* EDITAR */
elseif($acao == "editar"){

$tipo = $_GET["tipo"];
$id = $_GET["id"];

$dados = listarPerguntas($tipo);

$linha = $dados[$id];

if($tipo == "texto"){
?>

<form method="POST">

<input name="pergunta" value="<?php echo $linha[0]; ?>"><br><br>

<input name="resposta" value="<?php echo $linha[1]; ?>"><br><br>

<button name="salvar">Salvar</button>

</form>

<?php

if(isset($_POST["salvar"])){

$nova = $_POST["pergunta"].";".$_POST["resposta"];

editarPergunta("texto",$id,$nova);

header("Location: perguntas.php?acao=listar");
exit();

}

}else{
?>

<form method="POST">

<input name="pergunta" value="<?php echo $linha[0]; ?>"><br><br>
<input name="a" value="<?php echo $linha[1]; ?>"><br><br>
<input name="b" value="<?php echo $linha[2]; ?>"><br><br>
<input name="c" value="<?php echo $linha[3]; ?>"><br><br>
<input name="d" value="<?php echo $linha[4]; ?>"><br><br>
<input name="gabarito" value="<?php echo $linha[5]; ?>"><br><br>

<button name="salvar">Salvar</button>

</form>

<?php

if(isset($_POST["salvar"])){

$nova =
$_POST["pergunta"].";".
$_POST["a"].";".
$_POST["b"].";".
$_POST["c"].";".
$_POST["d"].";".
$_POST["gabarito"];

editarPergunta("multipla",$id,$nova);

header("Location: perguntas.php?acao=listar");
exit();

}

}

}
?>