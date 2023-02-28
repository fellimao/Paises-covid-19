<?php
# Adicionando script de consumer api e conexão do banco
include 'api/Consumer.php';
include 'database/conn.php';

# Preparando parametros para adquirir os paises
$data = array("listar_paises" => "1");
$paises = CallAPI("GET", 'https://dev.kidopilabs.com.br/exercicio/covid.php', $data);
$paises = json_decode($paises);

$conn = OpenCon();

// Pegando horario atual como timestamp
$horario = time();

// Pegando pais para ser salvo
$pais = $_GET['pais'];

// Preparando insert
$sql = "INSERT INTO acessos (data_hora, Pais) VALUES (?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("is", $horario, $pais);

if (!$stmt->execute()) {
    echo "Erro na execução da consulta: " . $stmt->error;
    exit();
}

# Adquirindo dado do ultimo acesso

$sql = "SELECT data_hora, pais FROM acessos ORDER BY data_hora DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ultimo_horario = $row["data_hora"];
    $ultimo_pais = $row["pais"];
    $data_formatada = date('d/m/Y H:i:s', $ultimo_horario);
    $resultado = "Última requisição: " . strval($data_formatada) . " - " . $ultimo_pais;
} else {
    $resultado = "Não foi feito nenhuma requisição ainda";
}

CloseCon($conn);


# Preparando parametros para adquirir os paises
$data = array("pais" => $pais);
$info = CallAPI("GET", 'https://dev.kidopilabs.com.br/exercicio/covid.php', $data);
$info = json_decode($info, true);

$total_confirmados = 0;
$total_Mortos = 0;
# Se tiver mais que 1 index, quer dizer que tem vários estados
if(array_key_exists(1, $info)){
    foreach ($info as $key => $estados) {
        $array[] = $estados;
        $total_confirmados += $estados['Confirmados'];
        $total_Mortos += $estados['Mortos'];
    }
}
else{
    $info = $info[0];
    $pais_selecionado = [];
    foreach ($info as $key => $value) {
        $pais_selecionado[$key] = $value;
    }

    $total_confirmados = $pais_selecionado['Confirmados'];
    $total_Mortos = $pais_selecionado['Mortos'];
}

$taxa_mortes =  number_format(($total_Mortos / $total_confirmados) * 100, 2, ',', '');

?>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Folhas de estilo -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="css/mainCSS.css">

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
        <script src="js/mainScript.js"></script>
        <title>Aplicação</title>
    </head>
    <body>
        <?php if(array_key_exists(1, $info)){
            ?>
            <div class="container">
                <div class="row justify-content-center">
                    <h1 class="display-3 text-justify" style="margin: 40px;"><?=$pais?></h1>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="button" onclick="redirecionar('index.php')" class="btn btn-secondary btn-lg" style="width: 200px;">Voltar</button>
                        <button type="button" data-toggle="modal" data-target="#comparacao" class="btn btn-primary btn-lg" style="width: 200px;">Comparar</button>
                    </div>
                </div>
                <div class="row">
        <?php
                foreach ($array as $estado){
                    if($estado['ProvinciaEstado'] == "" || $estado['ProvinciaEstado'] == NULL)
                        $estado['ProvinciaEstado'] = $estado['Pais']
        ?>
                <div class="card card_effect" style="width: 30%;margin: 10px;">
                <iframe src="https://maps.google.com/maps?q=Estado <?=$estado['ProvinciaEstado']?>-<?=$estado['Pais']?> &t=&z=13&ie=UTF8&iwloc=&output=embed&z=6" frameborder="0"style="border:0;height:300px;"></iframe>
                    <div class="card-body">
                        <h5 class="card-title"><?=$estado['ProvinciaEstado'] ?></h5>
                    </div>
                    <ul class="list-group list-group-flush cardlist">
                        <li class="list-group-item">Confirmados: <?=$estado['Confirmados'] ?> </li>
                        <li class="list-group-item">Mortos: <?=$estado['Mortos'] ?></li>
                    </ul>
                </div>
        <?php 
                }
        ?>
                </div>
            </div>
        <?php
            }
        else{
            ?>
            <div class="container">
                <div class="row justify-content-center">
                    <h1 class="display-3 text-justify" style="margin: 40px;"><?=$pais_selecionado['Pais']?></h1>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="button" onclick="redirecionar('index.php')" class="btn btn-secondary btn-lg" style="width: 200px;">Voltar</button>
                        <button type="button" data-toggle="modal" data-target="#comparacao" class="btn btn-primary btn-lg" style="width: 200px;">Comparar</button>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="card card_effect" style="width:80%;margin: 10px;">
                        <iframe src="https://maps.google.com/maps?q=Pais <?=$pais_selecionado['Pais']?>&t=&z=13&ie=UTF8&iwloc=&output=embed&z=6" frameborder="0"style="border:0;height:400px;"></iframe>
                        <div class="card-body">
                            <h5 class="card-title"><?=$pais_selecionado['Pais'] ?></h5>
                        </div>
                        <ul class="list-group list-group-flush cardlist">
                            <li class="list-group-item">Confirmados: <?=$pais_selecionado['Confirmados'] ?> </li>
                            <li class="list-group-item">Mortos: <?=$pais_selecionado['Mortos'] ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php
            }
        ?>

        <footer class="footer footer-custom">
            <div class="container">
                <h1><?=$resultado?></h1>
            </div>
        </footer>
    </body>
</html>

<!-- Modal -->
<div class="modal fade" id="comparacao" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalComparacao">Comparação de países</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12 justify-content-center text-center">
                <h5>Selecione um país:</h5>
                <div class="mx-auto" style="max-width: 300px;">
                    <select class="form-control" id="pais-select">
                        <option value="">Selecione um país</option>
                    <?php
                        foreach ($paises as $pais_select) { 
                            $pular = false;
                            switch($pais){
                                case "Korea, North":
                                    $nome = "North Korea";
                                    break;
                                case "Korea, South":
                                    $nome = "South Korea";
                                    break;
                                case "Holy See":
                                    $nome = "Vatican city";
                                    break;
                                case "North Macedonia":
                                    $nome = "Macedonia";
                                    break;
                                case "Diamond Princess":
                                case "MS Zaandam":
                                case "Winter Olympics 2022":
                                case "Summer Olympics 2020":
                                    $pular = true;
                                    break;
                                default:
                                    $nome = $pais_select;
                            }
                            if($pular == true)
                                continue;
                    ?>
                        <option value="<?=$pais_select?>"><?=$pais_select?></option>
                    <?php
                        }
                    ?>
                    </select>
                </div>
            </div>
          <div class="col-md-6 justify-content-center text-center">
                <h1 id="titulo_pais_pagina"><?=$pais?></h1>
                <div id="card_pais_pagina" class="card mx-auto text-center" style="width: 250px; margin: 10px;">
                    <img class="card-img-top" src="img/<?=str_replace('*','',str_replace(' ', '_', $pais))?>.png" style="height: 150px;">
                    <ul class="list-group list-group-flush cardlist">
                        <li class="list-group-item">Confirmados: <?=$total_confirmados?> </li>
                        <li class="list-group-item">Mortos: <?=$total_Mortos ?></li>
                        <li class="list-group-item">Taxa de morte: <?=$taxa_mortes ?> %</li>
                    </ul>
                </div>
          </div>
          <div class="col-md-6 justify-content-center text-center">
                <h1 id="titulo_pais_selecionado"> País </h1>
                <div id="card_pais_selecionado" class="card mx-auto text-center" style="width: 250px; margin: 10px;">
                    <img class="card-img-top" style="height: 150px;">
                    <ul class="list-group list-group-flush cardlist">
                        <li class="list-group-item">Confirmados: </li>
                        <li class="list-group-item">Mortos: </li>
                        <li class="list-group-item">Taxa de morte: </li>
                    </ul>
                </div>
          </div>
          <div class="col-md-12 justify-content-center text-center">
                <h5 id="diferenca_taxa"></h5>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {
    $('#pais-select').change(function() {
        var pais = $(this).val();
        adquireDadoPais(pais).then(function(response) {

            let total_confirmados = 0;
            let total_mortos = 0;
            let taxaMorte2 = 0

            const card = document.querySelector('#card_pais_selecionado');
            const cardImg = card.querySelector('.card-img-top');
            const cardList = card.querySelectorAll('.list-group-item');
            
            const values = Object.values(response);
            for (let i = 0; i < values.length; i++) {
                const val = values[i];
                total_confirmados += val.Confirmados;
                total_mortos += val.Mortos;
            }

            taxaMorte2 = (total_mortos/total_confirmados)*100;

            document.querySelector('#titulo_pais_selecionado').innerText = pais;
            pais = pais.replace(/\s+/g, '_');
            pais = pais.replace(/\*/g, ''); 
            cardImg.src = "img/" + pais + ".png";
            cardList[0].innerText = 'Confirmados: ' + total_confirmados;
            cardList[1].innerText = 'Mortos: ' + total_mortos;
            cardList[2].innerText = 'Taxa de morte: ' + (taxaMorte2).toFixed(2) + ' %';

            const taxaMortesElem = document.querySelector('#card_pais_pagina li:nth-child(3)');
            const taxaMorte1 = taxaMortesElem.textContent.trim().split(' ')[3].replace(',', '.');

            taxa_diferenca = parseFloat(taxaMorte1) - parseFloat(taxaMorte2);

            document.querySelector('#diferenca_taxa').innerText = 'A diferença da taxa de mortes entre os dois paises é: ' + taxa_diferenca.toFixed(2) + ' %';
        })
        .catch(function(error) {
            console.log(error);
        });
    });
});
</script>