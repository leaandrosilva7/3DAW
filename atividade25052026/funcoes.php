<?php

function nomeArquivo($tipo){

    if($tipo == "texto"){
        return "questoes_texto.txt";
    }else{
        return "questoes_multipla.txt";
    }

}

function criarArquivo($tipo){

    $nome = nomeArquivo($tipo);

    if(!file_exists($nome)){

        $arq = fopen($nome,"w");

        if($tipo == "texto"){
            fwrite($arq,"pergunta;resposta\n");
        }else{
            fwrite($arq,"pergunta;A;B;C;D;gabarito\n");
        }

        fclose($arq);
    }
}

function salvarPergunta($tipo,$linha){

    criarArquivo($tipo);

    $arq = fopen(nomeArquivo($tipo),"a");

    fwrite($arq,$linha."\n");

    fclose($arq);
}

function listarPerguntas($tipo){

    criarArquivo($tipo);

    $arq = fopen(nomeArquivo($tipo),"r");

    fgets($arq);

    $dados = [];

    while($linha = fgets($arq)){

        if(trim($linha) != ""){
            $dados[] = explode(";",trim($linha));
        }

    }

    fclose($arq);

    return $dados;
}

function excluirPergunta($tipo,$id){

    $arq = fopen(nomeArquivo($tipo),"r");

    $novo = fgets($arq);

    $i = 0;

    while($linha = fgets($arq)){

        if($i != $id){
            $novo .= $linha;
        }

        $i++;
    }

    fclose($arq);

    $arq = fopen(nomeArquivo($tipo),"w");

    fwrite($arq,$novo);

    fclose($arq);
}

function editarPergunta($tipo,$id,$novaLinha){

    $arq = fopen(nomeArquivo($tipo),"r");

    $novo = fgets($arq);

    $i = 0;

    while($linha = fgets($arq)){

        if($i == $id){
            $novo .= $novaLinha."\n";
        }else{
            $novo .= $linha;
        }

        $i++;
    }

    fclose($arq);

    $arq = fopen(nomeArquivo($tipo),"w");

    fwrite($arq,$novo);

    fclose($arq);
}
?>
