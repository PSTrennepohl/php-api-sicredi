# PHP API Sicredi
Biblioteca para integração com a API Sicredi e geração de QR Code PIX.

## Instalação
```bash
composer require pstrennepohl/php-api-sicredi

Exemplo de uso:

Gerando um PIX para pagamento

<?php
  require_once "./vendor/autoload.php";

  use PSTrennepohl\Sicredi\SicrediAPI;
  use PSTrennepohl\Sicredi\SicrediPIX;

  $initPix  = [
    "producao" => 1, // 0 | 1 
    "client_id" => "IdDoClienteGeradoNaApiDoSicredi",
    "client_secret" => "SecretDoClienteGeradoNaApiDoSicredi",
    "crt_file" => "/Caminho/do/certificado.cer",
    "key_file" => "/caminho/do/chave.key",
    "pass" => "senha se houver, não é obrigatória"
  ];

$pix = new SicrediPIX($initPix);

$cobranca  = [
    "calendario" => [
        "expiracao"=> 60 //nesse caso expira em 60 segundos
    ],
   
    "valor" => [
        "original" => 3.50 // valor a ser cobrado
    ],
    "chave" => "ChaveDoCliente", // geralmente o cnpj da conta
    "solicitacaoPagador" => "Mensagem que aparece para quem vai pagar.",
    "infoAdicionais" => [
        [
            "nome" => "Conta/parcela",
            "valor" => "3/1"
        ]
    ]
];
$cob = json_decode($pix->criarCobranca($cobranca));
$qrcode = $pix->gerarQRCode($cob->pixCopiaECola);

echo '<br><br><img width="300" height="300" src="'.$qrcode.'" alt="QR Code" />';

?>

Informado o endereço de retorno.
  Quando o pix é pago é acionado um evento que envia as informações para o endereço configurado, dessa forma consegue-se saber quando o PIX foi pago e dar baixa automatica internamente.
  Obs.: esse codigo pode ser executado somente uma vez, pois ele registra que TODOS os retornos dessa chave devem ser para este endereço(URL).

<?php 
  // WEBHOOK PARA RECEBIMENTO DE NOTIFICAÇÕES DO PIX
  require_once "./vendor/autoload.php";
  use PSTrennepohl\Sicredi\SicrediAPI;
  use PSTrennepohl\Sicredi\SicrediPIX;

    $initPix  = [
    "producao" => 1, // 0 | 1 
    "client_id" => "IdDoClienteGeradoNaApiDoSicredi",
    "client_secret" => "SecretDoClienteGeradoNaApiDoSicredi",
    "crt_file" => "/Caminho/do/certificado.cer",
    "key_file" => "/caminho/do/chave.key",
    "pass" => "senha se houver, não é obrigatória"
  ];
  
  $pix = new SicrediPIX($initPix);
  
  $url = 'https://endereco.com.br/do/retornoDoPix.php';
  $chave = "ChaveDoCliente", // geralmente o cnpj da conta
  $pix->updateWebhook($url, $chave);
?>



Para uma lista completa de opções na geração de boleto execute:
<?php
  require_once "./autoload.php";
  use PSTrennepohl\Sicredi\SicrediAPI;
  $sicredi = new SicrediAPI($agencia,$cedente,$posto,$token,$api_key);
  echo $sicredi->DadosBoleto->getVariaveis();
?>

Qualquer dúvida consulte o manual do Sicredi!
