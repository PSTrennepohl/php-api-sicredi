<?php
namespace PSTrennepohl\Sicredi;

use chillerlan\QRCode\QRCode;

class SicrediPIX{
    const urlH = 'https://api-pix-h.sicredi.com.br';
    const urlP = 'https://api-pix.sicredi.com.br';

    public  $url;
    public  $client_id;
    public  $client_secret;
    public  $authorization;
    public  $token;
    public  $crt_file;
    public  $key_file;
    public  $pass;
    public  $header;
    public  $parth;
    public  $fields;

    public function __construct($dados){

        $resultadoCertificado = $this->VerificarCertificado($dados["crt_file"]);

        if ($resultadoCertificado["valido"] === false) {
            throw new \Exception($resultadoCertificado['mensagem']);
        }
        
        if ((int) $dados["producao"] == 1) {
            $this->url = self::urlP;
        } else {
            $this->url = self::urlH;
        }

        $this->client_id        = $dados["client_id"];
        $this->client_secret    = $dados["client_secret"];

        $this->crt_file = $dados["crt_file"];
        $this->key_file = $dados["key_file"];
        $this->pass     = $dados["pass"];

        $this->authorization = base64_encode($this->client_id . ":" . $this->client_secret);

        $this->token = $this->accessToken();
    }

    public function Request($method){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url .  $this->parth);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->fields);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->crt_file);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->key_file);
        curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->pass);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function accessToken(){

        $this->parth  = '/oauth/token?grant_type=client_credentials&scope=cob.write+cob.read+webhook.read+webhook.write';
        $this->header = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . $this->authorization . ' '
        ];
        $response     = $this->Request("POST");
        return json_decode($response)->access_token;
    }

    public function updateWebhook($url, $chave){
        $this->parth  =  '/api/v2/webhook/' . $chave;
        $this->header =  ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token . ''];
        $this->fields =  json_encode(["webhookUrl" => $url]);
        $response     =  $this->Request("PUT");
        return $response;
    }

    public function getUrlWebhook($chave){
        $this->parth  =  '/api/v2/webhook/' . $chave;
        $this->header =  ['Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("GET");
        return json_decode($response);
    }

    public function deleteUrlWebhook($chave){
        $this->parth  =  '/api/v2/webhook/' . $chave;
        $this->header =  ['Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("DELETE");
        return $response;
    }

    public function criarCobranca($data){
        $this->fields = json_encode($data);
        $this->parth  =  '/api/v2/cob';
        $this->header =  ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("POST");
        return json_decode($response);
    }

    /*
    	RETORNO
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
    */
    public function dadosDeCobranca($id){
        $this->parth  =  '/api/v2/cob/' . $id;
        $this->header =  ['Authorization: Bearer ' . $this->token . ''];
        $response     =  $this->Request("GET");
        return json_decode($response);
    }

    public function gerarQRCode(string $data): string {
        return (new QRCode)->render($data);
    }

    public function VerificarCertificado($cer){
        $resposta = [
            "valido" => false,
            "mensagem" => "",
            "expiracao" => null,
        ];

        // Tenta carregar o conteúdo do certificado
        $conteudo = @file_get_contents($cer);
        if ($conteudo === false || trim($conteudo) === "") {
            $resposta["mensagem"] = "❌ Não foi possível ler o arquivo do certificado ou está vazio.";
            return $resposta;
        }

        // Detectar se PEM ou DER (simplificado)
        $isPEM = strpos($conteudo, '-----BEGIN CERTIFICATE-----') !== false;

        if ($isPEM) {
            // Tenta ler PEM
            $cert = @openssl_x509_read($conteudo);
            if (!$cert) {
                $resposta["mensagem"] = "❌ Erro ao interpretar o certificado PEM (formato inválido).";
                return $resposta;
            }
        } else {
            // Tenta ler DER - abrir o conteúdo como DER
            // PHP só suporta DER com parâmetro a partir do PHP 8:
            if (defined('OPENSSL_FORMAT_DER')) {
                $cert = @openssl_x509_read($conteudo, OPENSSL_FORMAT_DER);
                if (!$cert) {
                    $resposta["mensagem"] = "❌ Erro ao interpretar o certificado DER (formato inválido).";
                    return $resposta;
                }
            } else {
                // PHP versão < 8.0 não suporta ler DER direto, então converta fora do PHP
                $resposta["mensagem"] = "❌ Certificado DER não suportado nesta versão do PHP. Converta para PEM.";
                return $resposta;
            }
        }

        // Parsea os dados do certificado
        $cert_info = @openssl_x509_parse($cert);
        if (!$cert_info || !isset($cert_info["validTo_time_t"])) {
            $resposta["mensagem"] = "❌ Certificado lido, mas a data de expiração não foi encontrada.";
            return $resposta;
        }

        // Extrai a data de expiração
        $expiry_timestamp = $cert_info["validTo_time_t"];
        $resposta["expiracao"] = date('Y-m-d', $expiry_timestamp);

        // Verifica validade
        if ($expiry_timestamp > time()) {
            $resposta["valido"] = true;
            $resposta["mensagem"] = "✅ Certificado válido até {$resposta['expiracao']}.";
        } else {
            $resposta["mensagem"] = "❌ Certificado expirado em {$resposta['expiracao']}.";
        }

        return $resposta;
    }
}



?>