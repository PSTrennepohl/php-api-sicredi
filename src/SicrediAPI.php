<?php
namespace PSTrennepohl\Sicredi;

class SicrediAPI{

    protected $token = null;//'249151B0AA3345345654235445265345497093407BDD92B88362AE5F486F16CD';
    protected $cedente = null;
    protected $agencia = null;
    protected $posto = null;
    protected $access_token = null;
    protected $api_key = null;
    protected $operacao = 'HOMOLOGACAO'; // HOMOLOGACAO OU PRODUCAO
    protected $link_auth = null;// 'https://api-parceiro.sicredi.com.br/sb/auth/openapi/token'; // padrão homologacao;
    protected $link_cobranca_boletoV1 = null; //'https://api-parceiro.sicredi.com.br/sb/cobranca/boleto/v1/boletos'; // padrão homologacao;
    protected $debug = false;
    public    $DadosBoleto = null;
    
    // Construtor (AGENCIA, CEDENTE, POSTO, TOKEN, API_KEY, OPERAÇÃO)
    function __construct ($ag, $ce, $po, $tok, $apk, $op='HOMOLOGACAO') {
        if($op == 'PRODUCAO'){
            $this->link_auth = 'https://api-parceiro.sicredi.com.br/auth/openapi/token';
            $this->link_cobranca_boletoV1 = 'https://api-parceiro.sicredi.com.br/cobranca/boleto/v1/boletos';
        }else{
            $this->link_auth = 'https://api-parceiro.sicredi.com.br/sb/auth/openapi/token';
            $this->link_cobranca_boletoV1 = 'https://api-parceiro.sicredi.com.br/sb/cobranca/boleto/v1/boletos';
        }

        $this->agencia = $ag;
        $this->cedente = $ce;
        $this->posto = $po;
        $this->token = $tok;
        $this->api_key = $apk;
        $this->access_token = $this->GetAccessToken();
        $this->DadosBoleto = new DadosBoleto();
    }

    private function GetAccessToken() {
        $curl_token = curl_init();

        curl_setopt_array($curl_token, array(
            CURLOPT_URL => $this->link_auth, //"https://api-parceiro.sicredi.com.br/sb/auth/openapi/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'username='.$this->cedente.$this->agencia.'&password='.$this->token.'&scope=cobranca&grant_type=password',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'x-api-key: '.$this->api_key,
                'context: COBRANCA',
                'Accept: application/json'
            ),
        ));



        $response = curl_exec($curl_token);
        $info = curl_getinfo($curl_token);
        $err = curl_error( $curl_token) ;
        curl_close( $curl_token) ;
        //sleep(5);
        if ($err) {
            if($this->debug){ $this->logMe("cURL Error: ".$err.'<br>'); }
            return "cURL Error: ".$err.'<br>';
        } else {
            if($this->debug){ $this->logMe(($response)); }
            $j = json_decode($response,true);
            return $j['access_token'];
        }
        if($this->debug){ $this->logMe("Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"]); }
        return "Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"];
    }

    public function EmitirBoleto(){

        if(count($this->DadosBoleto->getDadosBoleto('CLASSE')) < 7){
            return 'Preencha os dados do boleto ex.: <br>
                    $var->DadosBoleto->setTipoCobranca();<br> 
                    $var->DadosBoleto->setcodigoBeneficiario();<br> 
                    $var->DadosBoleto->setespecieDocumento();<br> 
                    $var->DadosBoleto->setvalor();<br> 
                    ...
                    <br>
                    Para uma lista completa de opções execute:<br><br>

                    require_once "./autoload.php";<br>
                    use PSTrennepohl\Sicredi\SicrediAPI;<br>
                    $sicredi = new SicrediAPI($agencia,$cedente,$posto,$token,$api_key);<br>
                    echo $sicredi->DadosBoleto->getVariaveis();<br>

                    <br>Na dúvida consulte o manual do Sicredi.';
        }
        if(array_key_exists('ERRO', $this->DadosBoleto->getDadosBoleto('CLASSE'))){
            return $this->DadosBoleto->getDadosBoleto('JSON');
        }

        $curl_Emite = curl_init();

        curl_setopt_array($curl_Emite, array(
            CURLOPT_URL => $this->link_cobranca_boletoV1, //'https://api-parceiro.sicredi.com.br/sb/cobranca/boleto/v1/boletos',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $this->DadosBoleto->getDadosBoleto('JSON'),
            CURLOPT_HTTPHEADER => array(
                'x-api-key: '.$this->api_key, //135345308-345kj-345ki-78fd-548fdaajfhd6'
                'Authorization: Bearer '.$this->access_token,
                'Content-Type: application/json',
                'cooperativa: '.$this->agencia, ////////////////////////////////////////////////////////////////////
                'posto: '.$this->posto ////////////////////////////////////////////////////////////////////
            ),
        ));

        $response = curl_exec($curl_Emite);
        $info = curl_getinfo($curl_Emite);
        $err = curl_error( $curl_Emite) ;
        curl_close( $curl_Emite) ;
        if ($err) {
            if($this->debug){ $this->logMe("cURL Error: ".$err.'<br>'); }
            return "cURL Error: ".$err.'<br>';
        } else {
            if($this->debug){ $this->logMe(($response)); }
            return json_decode($response);
        }
        if($this->debug){ $this->logMe("Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"]); }
        return "Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"];
    }

    public function imprimirBoleto($linhaDigitavel){
        $curl_imp = curl_init();
        curl_setopt_array($curl_imp, array(
            CURLOPT_URL => $this->link_cobranca_boletoV1.'/pdf?linhaDigitavel='.$linhaDigitavel,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$this->access_token,
                'x-api-key: '.$this->api_key //135345308-345kj-345ki-78fd-548fdaajfhd6'
            ),
        ));

        $response = curl_exec($curl_imp);
        $info = curl_getinfo($curl_imp);
        $err = curl_error( $curl_imp) ;
        curl_close( $curl_imp) ;
        if ($err) {
            if($this->debug){ $this->logMe("cURL Error: ".$err.'<br>'); }
            return "cURL Error: ".$err.'<br>';
        } else {
            if($this->debug){ $this->logMe("Binarios do boleto retornados com sucesso."); }
            return base64_encode($response);
        }
        if($this->debug){ $this->logMe("Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"]); }
        return "Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"];
    }

    public function consultaStatusBoleto($nossoNumero){

        $curl_consulta = curl_init();
        curl_setopt_array($curl_consulta, array(
            CURLOPT_URL => $this->link_cobranca_boletoV1.'?codigoBeneficiario='.$this->cedente.'&nossoNumero='.$nossoNumero,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'x-api-key: '.$this->api_key, //135345308-345kj-345ki-78fd-548fdaajfhd6'
                'Authorization: Bearer '.$this->access_token,
                'cooperativa: '.$this->agencia,
                'posto: '.$this->posto,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl_consulta);
        $info = curl_getinfo($curl_consulta);
        $err = curl_error( $curl_consulta) ;
        curl_close( $curl_consulta) ;
        if ($err) {
            if($this->debug){ $this->logMe("cURL Error: ".$err.'<br>'); }
            return "cURL Error: ".$err.'<br>';
        } else {
            if($this->debug){ $this->logMe(($response)); }
            return json_decode($response);
        }
        if($this->debug){ $this->logMe("Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"]); }
        return "Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"];
    }

    public function baixarBoleto($nossoNumero){
        $curl_baixa = curl_init();

        curl_setopt_array($curl_baixa, array(
          CURLOPT_URL => $this->link_cobranca_boletoV1.'/'.$nossoNumero.'/baixa',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_POSTFIELDS =>'{}',
          CURLOPT_HTTPHEADER => array(
            'x-api-key: '.$this->api_key,
            'Authorization: Bearer '.$this->access_token,
            'cooperativa: '.$this->agencia,
            'posto: '.$this->posto,
            'codigoBeneficiario: '.$this->cedente,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl_baixa);
        $info = curl_getinfo($curl_baixa);
        $err = curl_error( $curl_baixa) ;
        curl_close( $curl_baixa) ;
        if ($err) {
            if($this->debug){ $this->logMe("cURL Error: ".$err.'<br>'); }
            return "cURL Error: ".$err.'<br>';
        } else {
            if($this->debug){ $this->logMe(($response)); }
            return json_decode($response);
        }
        if($this->debug){ $this->logMe("Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"]); }
        return "Recurso Autenticacao -> HTTP Status Code: " . $info["http_code"];
    }

    public function debugar($r){
        $this->debug = $r;
    }

    public function logMe($msg){
        $fp = fopen("API_Sicredi.txt","a");
        $escreve = fwrite($fp, date('d/m/Y H:i').' - '.$msg.PHP_EOL);
        fclose($fp);
    }
}
?>
