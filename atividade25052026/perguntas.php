<!DOCTYPE html>
<html>
<body>

<a href="index.php">Voltar</a><br><br>

<div id="conteudo"></div>

<script>

var acao = new URLSearchParams(window.location.search).get("acao") || "";

function xhr(method, url, dados, callback){
    var req = new XMLHttpRequest();
    req.open(method, url, true);
    req.onreadystatechange = function(){
        if(req.readyState == 4 && req.status == 200){
            callback(JSON.parse(req.responseText));
        }
    };
    if(method == "POST"){
        req.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    }
    req.send(dados);
}

function serializeForm(form){
    var params = [];
    var elements = form.elements;
    for(var i = 0; i < elements.length; i++){
        var el = elements[i];
        if(el.name && el.name != "salvar"){
            params.push(encodeURIComponent(el.name)+"="+encodeURIComponent(el.value));
        }
    }
    return params.join("&");
}

if(acao == "criar_texto"){

    document.getElementById("conteudo").innerHTML =
        '<form id="form">' +
        '<input name="pergunta" placeholder="Pergunta"><br><br>' +
        '<input name="resposta" placeholder="Resposta"><br><br>' +
        '<button type="button" onclick="salvarTexto()">Salvar</button>' +
        '</form>';

    window.salvarTexto = function(){
        var form = document.getElementById("form");
        var dados = "acao=salvar_texto&" + serializeForm(form);
        xhr("POST","api.php",dados,function(resp){
            if(resp.ok) window.location.href = "perguntas.php?acao=listar";
        });
    };

}else if(acao == "criar_multipla"){

    document.getElementById("conteudo").innerHTML =
        '<form id="form">' +
        '<input name="pergunta" placeholder="Pergunta"><br><br>' +
        '<input name="a" placeholder="A"><br><br>' +
        '<input name="b" placeholder="B"><br><br>' +
        '<input name="c" placeholder="C"><br><br>' +
        '<input name="d" placeholder="D"><br><br>' +
        '<input name="gabarito" placeholder="Gabarito"><br><br>' +
        '<button type="button" onclick="salvarMultipla()">Salvar</button>' +
        '</form>';

    window.salvarMultipla = function(){
        var form = document.getElementById("form");
        var dados = "acao=salvar_multipla&" + serializeForm(form);
        xhr("POST","api.php",dados,function(resp){
            if(resp.ok) window.location.href = "perguntas.php?acao=listar";
        });
    };

}else if(acao == "listar"){

    xhr("GET","api.php?acao=listar",null,function(resp){

        var html = "<h2>TEXTO</h2>";

        for(var id = 0; id < resp.texto.length; id++){
            var linha = resp.texto[id];
            html += linha[0]+" - "+linha[1];
            html += " <a href='perguntas.php?acao=editar&tipo=texto&id="+id+"'>Editar</a>";
            html += " <a href='#' onclick='excluir(\"texto\","+id+");return false;'>Excluir</a>";
            html += "<br><br>";
        }

        html += "<hr><h2>MULTIPLA</h2>";

        for(var id = 0; id < resp.multipla.length; id++){
            var linha = resp.multipla[id];
            html += linha[0];
            html += " <a href='perguntas.php?acao=editar&tipo=multipla&id="+id+"'>Editar</a>";
            html += " <a href='#' onclick='excluir(\"multipla\","+id+");return false;'>Excluir</a>";
            html += "<br><br>";
        }

        document.getElementById("conteudo").innerHTML = html;
    });

    window.excluir = function(tipo, id){
        var dados = "acao=excluir&tipo="+tipo+"&id="+id;
        xhr("POST","api.php",dados,function(resp){
            if(resp.ok) window.location.href = "perguntas.php?acao=listar";
        });
    };


}else if(acao == "editar"){

    var tipo = new URLSearchParams(window.location.search).get("tipo");
    var id   = new URLSearchParams(window.location.search).get("id");

    xhr("GET","api.php?acao=listar",null,function(resp){

        var dados = tipo == "texto" ? resp.texto : resp.multipla;
        var linha = dados[id];

        if(tipo == "texto"){

            document.getElementById("conteudo").innerHTML =
                '<form id="form">' +
                '<input name="pergunta" value="'+linha[0]+'"><br><br>' +
                '<input name="resposta" value="'+linha[1]+'"><br><br>' +
                '<button type="button" onclick="salvarEdicao()">Salvar</button>' +
                '</form>';

            window.salvarEdicao = function(){
                var form = document.getElementById("form");
                var postDados = "acao=editar_texto&id="+id+"&" + serializeForm(form);
                xhr("POST","api.php",postDados,function(resp){
                    if(resp.ok) window.location.href = "perguntas.php?acao=listar";
                });
            };

        }else{

            document.getElementById("conteudo").innerHTML =
                '<form id="form">' +
                '<input name="pergunta" value="'+linha[0]+'"><br><br>' +
                '<input name="a" value="'+linha[1]+'"><br><br>' +
                '<input name="b" value="'+linha[2]+'"><br><br>' +
                '<input name="c" value="'+linha[3]+'"><br><br>' +
                '<input name="d" value="'+linha[4]+'"><br><br>' +
                '<input name="gabarito" value="'+linha[5]+'"><br><br>' +
                '<button type="button" onclick="salvarEdicao()">Salvar</button>' +
                '</form>';

            window.salvarEdicao = function(){
                var form = document.getElementById("form");
                var postDados = "acao=editar_multipla&id="+id+"&" + serializeForm(form);
                xhr("POST","api.php",postDados,function(resp){
                    if(resp.ok) window.location.href = "perguntas.php?acao=listar";
                });
            };
        }
    });
}

</script>

</body>
</html>
