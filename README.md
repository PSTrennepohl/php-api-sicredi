# PHP API Sicredi
Biblioteca para integração com a API Sicredi e geração de QR Code PIX.

## Instalação
```bash
composer require pstrennepohl/php-api-sicredi


Para uma lista completa de opções execute:
<?php
  require_once "./autoload.php";
  use PSTrennepohl\Sicredi\SicrediAPI;
  $sicredi = new SicrediAPI($agencia,$cedente,$posto,$token,$api_key);
  echo $sicredi->DadosBoleto->getVariaveis();
?>

Qualquer dúvida consulte o manual do Sicredi!
