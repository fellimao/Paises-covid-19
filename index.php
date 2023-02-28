<?php
# Adicionando script de consumer api e conexão do banco
include 'api/Consumer.php';
include 'database/conn.php';

date_default_timezone_set('America/Sao_Paulo');

# Preparando parametros para adquirir os paises
$data = array("listar_paises" => "1");
$paises = CallAPI("GET", 'https://dev.kidopilabs.com.br/exercicio/covid.php', $data);
$paises = json_decode($paises);

# Adquirindo dado do ultimo acesso
$conn = OpenCon();

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

if(isset($paises) && !empty($paises)){
?>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Folhas de estilo -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <link rel="stylesheet" href="css/mainCSS.css">
        <!-- Scripts  -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="js/mainScript.js"></script>
        <title>Aplicação</title>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <h1 class="display-3 text-justify" style="margin: 40px;">Casos e mortes por Covid-19</h1>
            </div>
            <div class="row">
            <?php foreach ($paises as $pais) { 
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
                        $nome = $pais;
                }
                if($pular == true)
                    continue;
                ?>
                <div class="card card_effect" style="width: 250px; margin: 10px;">
                <img class="card-img-top" src="img/<?=str_replace('*','',str_replace(' ', '_', $nome))?>.png" style="height: 150px;">
                <div class="card-body">
                    <h5 class="card-title text-center"><?=$nome?></h5>
                    <button onclick="redirecionar('pais.php?pais=<?php echo $pais; ?>')" class="btn btn-primary" style="width:100%">Visualizar dados</a>
                </div>
                </div>
            <?php } ?>
            </div>
        </div>

        <footer class="footer footer-custom">
            <div class="container">
                <h1><?=$resultado?></h1>
            </div>
        </footer>
    </body>
</html>

<?php
}else {
    echo "Não foi possível carregar os países.";
}
?>