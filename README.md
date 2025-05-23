# PHP API Sicredi
Biblioteca para integração com a API Sicredi e geração de QR Code PIX.

## Instalação
```bash
composer require pstrennepohl/php-api-sicredi
```
## Exemplo de uso:

#### 1. Gerando um PIX para pagamento
```bash
  <?php
    require_once "./vendor/autoload.php";

    use PSTrennepohl\Sicredi\SicrediPIX;

    $initPix  = [
      "producao" => 0, // 0[Homologação], 1[Producao]
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
        "chave" => "ChaveDoCliente", 
        "solicitacaoPagador" => "Mensagem que aparece para quem vai pagar.",
        "infoAdicionais" => [
            [
                "nome" => "Conta/parcela",
                "valor" => "3/1"
            ]
        ]
    ];
    $ret = $pix->criarCobranca($cobranca);
    $qrcode = $pix->gerarQRCode($ret->pixCopiaECola);

    echo '<br><br><img width="300" height="300" src="'.$qrcode.'" alt="QR Code" />';
  ?>
```

#### 2. Verificando os dados do PIX gerado
```bash
  <?php
    $ret = $pix->dadosDeCobranca('txid');
    echo $ret->calendario->criacao; // data de criação
    echo $ret->calendario->expiracao; // tepo de validade
    echo $ret->status; // ATIVA (ainda é possivel pagar), CONCLUIDA (já foi paga)
    echo $ret->txid; // código ID da transação
    echo $ret->revisao; 
    echo $ret->location; 
    echo $ret->loc->id; 
    echo $ret->loc->location; 
    echo $ret->loc->tipoCob; 
    echo $ret->loc->criacao; 
    echo $ret->valor->original; // VALOR da cobrança
    echo $ret->valor->modalidadeAlteracao;
    echo $ret->valor->retirada;
    echo $ret->chave; // chave pix do recebedor
    echo $ret->solicitacaoPagador; // texto informativo para o pagador
    echo $ret->infoAdicionais[0]->nome; // dados para controle interno
    echo $ret->infoAdicionais[0]->valor; // valor do dado para controle interno
    echo $ret->pixCopiaECola; // código pix copia e cola
  ?>
```

#### 3. WEBHOOK Endereço utilizado para receber o status do PIX
  Obs.: O WebHook é setado apenas uma vez, não a cada transação, pois ele registra que TODOS os retornos dessa ChaveDoCliente devem ser para este endereço(URL).

#### 3.1 Verificando o endereço cadastrado
```bash
  <?php
    require_once "./vendor/autoload.php";
    use PSTrennepohl\Sicredi\SicrediPIX;

      $initPix  = [
      "producao" => 0, // 0[Homologação], 1[Producao]
      "client_id" => "IdDoClienteGeradoNaApiDoSicredi",
      "client_secret" => "SecretDoClienteGeradoNaApiDoSicredi",
      "crt_file" => "/Caminho/do/certificado.cer",
      "key_file" => "/caminho/do/chave.key",
      "pass" => "senha se houver, não é obrigatória"
    ];
    
    $pix = new SicrediPIX($initPix);
    $WebHook = $pix->getUrlWebhook("ChaveDoCliente"); // Retorna um json
    foreach ($WebHook as $chave => $valor) {
        echo "<b>[".$chave."]</b>: ".$valor."<br>";
    }
  ?>
```

#### 3.2 Cadastrando o endereço de retorno.
  Quando o pix é pago é acionado um evento que envia as informações para o endereço configurado, dessa forma consegue-se saber quando o PIX foi pago e dar baixa automatica internamente.
```bash  
  <?php 
    require_once "./vendor/autoload.php";
    use PSTrennepohl\Sicredi\SicrediPIX;

      $initPix  = [
      "producao" => 0, // 0[Homologação], 1[Producao]
      "client_id" => "IdDoClienteGeradoNaApiDoSicredi",
      "client_secret" => "SecretDoClienteGeradoNaApiDoSicredi",
      "crt_file" => "/Caminho/do/certificado.cer",
      "key_file" => "/caminho/do/chave.key",
      "pass" => "senha se houver, não é obrigatória"
    ];
    
    $pix = new SicrediPIX($initPix);
    
    $url = 'https://endereco.com.br/do/retornoDoPix.php';
    $chave = "ChaveDoCliente", 
    $pix->updateWebhook($url, $chave);
  ?>
```


#### 4. Para uma lista completa de opções para a geração de boleto execute:
```bash
  <?php
    require_once "./autoload.php";
    
    use PSTrennepohl\Sicredi\SicrediAPI;

    $sicredi = new SicrediAPI($agencia,$cedente,$posto,$token,$api_key);
    echo $sicredi->DadosBoleto->getVariaveis();
  ?>
```

## Qualquer dúvida consulte o manual do Sicredi!
