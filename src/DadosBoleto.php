<?php 
namespace PSTrennepohl\Sicredi;

    class DadosBoleto{
        /**
         * SicrediAPI container
         *
         * @var $DadosBoleto
         *          
         */
        /* (String 7) NORMAL ou HIBRIDO */
            private $tipoCobranca       = null; // Obrigatorio
        /* (String 5) Código do Convênio de Cobrança */
            private $codigoBeneficiario = null; // Obrigatorio
        /* (array()) Informações do Pagador */
            private $pagador            = null; // Obrigatorio
                /*  tipoPessoa(15)      = PESSOA_JURIDICA ou PESSOA_FISICA  - OBRIGATÓRIO
                *   documento(14)       = CPF ou CNPJ do Pagador do boleto  - OBRIGATÓRIO
                *   nome(40)            = Nome do Pagador                   - OBRIGATÓRIO
                *   endereco(40)        = Endereço do Pagador
                *   cidade(25)          = Cidade do Pagador
                *   uf(2)               = UF do Pagador
                *   cep(8) [0-9]        = CEP do Pagador
                *   telefone(11) [0-9]  = Telefone do Pagador
                *   email(40)           = Email do Pagador
                */
        /* (array()) Informações do Beneficiário */
            private $beneficiarioFinal  = null;
                /*  tipoPessoa(15)      = PESSOA_JURIDICA ou PESSOA_FISICA      - OBRIGATÓRIO
                *   documento(14)       = CPF ou CNPJ do Beneficiário do boleto - OBRIGATÓRIO
                *   nome(40)            = Nome do Beneficiário                  - OBRIGATÓRIO
                *   endereco(40)        = Endereço do Beneficiário
                *   cidade(25)          = Cidade do Beneficiário
                *   uf(2)               = UF do Beneficiário
                *   cep(8) [0-9]        = CEP do Beneficiário
                *   telefone(11) [0-9]  = Telefone do Beneficiário
                *   email(40)           = Email do Beneficiário
                */
        /* (String 29) Espécie de documento do título */
            private $especieDocumento   = null; // Obrigatorio
                /*  A - DUPLICATA MERCANTIL INDICAÇÃO
                *   B - DUPLICATA RURAL
                *   C - NOTA PROMISSORIA
                *   D - NOTA PROMISSORIA RURAL
                *   E - NOTA DE SEGUROS
                *   G – RECIBO
                *   H - LETRA DE CAMBIO
                *   I - NOTA DE DÉBITO
                *   J - DUPLICATA DE SERVICO INDICAÇÃO
                *   K – OUTROS
                *   O - BOLETO PROPOSTA
                *   P - CARTÃO DE CRÉDITO
                *   Q – BOLETO DEPÓSITO
                */
        /* (String 9) Nosso número Sicredi Gera utomaticamenteo */
            private $nossoNumero        = null;
        /* (String 10) Número de controle interno do beneficiário que faz referência ao pagador, seu número para utilizar como referencia para baixas no sistema interno */
            private $seuNumero          = null; // Obrigatorio
        /* (Date 'YYYY-MM-DD') Data de vencimento do boleto */
            private $dataVencimento     = null; // Obrigatorio
        /* (number 14,2) Valor do Boleto */
            private $valor              = null; // Obrigatorio
        /* (String 10) Tipo de desconto podendo ser: A-VALOR, B-PERCENTUAL */
            private $tipoDesconto       = null;
        /* (number 14,2) */
            private $valorDesconto1     = null;
        /* (date 'YYYY-MM-DD') Data limite para concessão do desconto */
            private $dataDesconto1      = null;
        /* (number 14,2) */    
            private $valorDesconto2     = null;
        /* (date 'YYYY-MM-DD') Data limite para concessão do desconto */
            private $dataDesconto2      = null;
        /* (number 14,2) */
            private $valorDesconto3     = null;
        /* (date 'YYYY-MM-DD') Data limite para concessão do desconto */
            private $dataDesconto3      = null;
        /* (string 10) Tipo de Juros, podendo ser: A-VALOR, B-PERCENTUAL */
            private $tipoJuros          = null;
        /* (number 14,2) Valor de Juros a cobrar por dia */
            private $juros              = null;
        /* (number 5,2) Percentual de multa a cobrar */
            private $multa              = null;
        /* (array(até 5) - 400) Permitido informar uma lista de até 5 informativos, com limite de 80 caracteres para cada. */
            private $informativos       = null;
        /* (array(até 4) - 320) Permitido informar uma lista de até 4 mensagens, com limite de 80 caracteres para cada. */
            private $mensagens          = null;

        public function getDadosBoleto($opt = null){
            if(!$opt){
                return "Escolha uma forma de saída getDadosBoleto('JSON') ou getDadosBoleto('CLASSE').";
            }
            $erro = null;
            if(!$this->tipoCobranca){ $erro['ERRO']['tipoCobranca'] = 'Falta tipoCobranca.'; }
            if(!$this->codigoBeneficiario){ $erro['ERRO']['codigoBeneficiario'] = 'Falta codigoBeneficiario.'; }
            if(!$this->pagador || !is_array($this->pagador)){ $erro['ERRO']['pagador'] = 'Pagador Deve ser um array() com os dados do pagador dentro (*Obrigatório:* tipoPessoa, documento, nome).'; }
            if(!$this->especieDocumento){ $erro['ERRO']['especieDocumento'] = 'Falta especieDocumento.'; }
            if(!$this->seuNumero){ $erro['ERRO']['seuNumero'] = 'Falta seuNumero.'; }
            if(!$this->dataVencimento){ $erro['ERRO']['dataVencimento'] = 'Falta dataVencimento.'; }
            if(!$this->valor){ $erro['ERRO']['valor'] = 'Falta valor.'; }

            if(is_array($this->pagador)){
                if(!array_key_exists('tipoPessoa', $this->pagador)){ $erro['ERRO']['pagador'] = 'Falta tipoPessoa'; }
                if(!array_key_exists('documento', $this->pagador)){ $erro['ERRO']['pagador'] = 'Falta documento'; }
                if(!array_key_exists('nome', $this->pagador)){ $erro['ERRO']['pagador'] = 'Falta nome'; }
            }

            if(is_array($this->beneficiarioFinal)){
                if(!array_key_exists('tipoPessoa', $this->beneficiarioFinal)){ $erro['ERRO']['beneficiarioFinal'] = 'Falta tipoPessoa'; }
                if(!array_key_exists('documento', $this->beneficiarioFinal)){ $erro['ERRO']['beneficiarioFinal'] = 'Falta documento'; }
                if(!array_key_exists('nome', $this->beneficiarioFinal)){ $erro['ERRO']['beneficiarioFinal'] = 'Falta nome'; }
            }

            if($erro){
                if(strtoupper($opt) == 'JSON'){
                    return json_encode($erro);
                }else if(strtoupper($opt) == 'CLASSE'){
                    return $erro;
                }else{
                    return "Opção inválida, escolha uma forma de saída getDadosBoleto('JSON') ou getDadosBoleto('CLASSE').";
                }
            }


            $data['tipoCobranca']       = $this->tipoCobranca;
            $data['codigoBeneficiario'] = $this->codigoBeneficiario;
            $data['pagador']            = $this->pagador;
            $data['beneficiarioFinal']  = $this->beneficiarioFinal;
            $data['especieDocumento']   = $this->especieDocumento;
            $data['nossoNumero']        = $this->nossoNumero;
            $data['seuNumero']          = $this->seuNumero;
            $data['dataVencimento']     = $this->dataVencimento;
            $data['valor']              = $this->valor;
            $data['tipoDesconto']       = $this->tipoDesconto;
            $data['valorDesconto1']     = $this->valorDesconto1;
            $data['dataDesconto1']      = $this->dataDesconto1;
            $data['valorDesconto2']     = $this->valorDesconto2;
            $data['dataDesconto2']      = $this->dataDesconto2;
            $data['valorDesconto3']     = $this->valorDesconto3;
            $data['dataDesconto3']      = $this->dataDesconto3;
            $data['tipoJuros']          = $this->tipoJuros;
            $data['juros']              = $this->juros;
            $data['multa']              = $this->multa;
            $data['informativos']       = $this->informativos;
            $data['mensagens']          = $this->mensagens;

            // remover os registros nulos
            $data = (array_filter($data, fn($value) => !is_null($value) && $value !== ''));
            // retorna o json do boleto
            if(strtoupper($opt) == 'JSON'){
                return json_encode($data);
            }else if(strtoupper($opt) == 'CLASSE'){
                return $data;
            }else{
                return "Opção inválida, escolha uma forma de saída getDadosBoleto('JSON') ou getDadosBoleto('CLASSE').";
            }
        }

        public function getVariaveis(){
            $data['setTipoCobranca']        = '[OBRIGATÓRIO] (String 7) NORMAL ou HIBRIDO.';
            $data['setCodigoBeneficiario']  = '[OBRIGATÓRIO] (String 5) Código do Convênio de Cobrança.';
            $data['setPagador']             = array(
                                                '[OBRIGATÓRIO] (array()) Informações do Pagador:.',
                                                array(
                                                    'tipoPessoa'=> '(String 15) PESSOA_JURIDICA ou PESSOA_FISICA [OBRIGATÓRIO].',
                                                    'documento' => '(String 14) CPF ou CNPJ do Pagador do boleto [OBRIGATÓRIO].',
                                                    'nome'      => '(String 40) Nome do Pagador [OBRIGATÓRIO].',
                                                    'endereco'  => '(String 40) Endereço do Pagador',
                                                    'cidade'    => '(String 25) Cidade do Pagador',
                                                    'uf'        => '(String 2) UF do Pagador',
                                                    'cep'       => '(String 8) [0-9] CEP do Pagador',
                                                    'telefone'  => '(String 11) [0-9] Telefone do Pagador',
                                                    'email'     => '(String 40) Email do Pagador'
                                                )
                                            );
            $data['setBeneficiarioFinal']   = array(
                                                '(array()) Informações do Beneficiário [OPCIONAL, SE PREENCHIDO OS SUB-ITENS TORNAM-SE OBRIGATÓRIOS]',
                                                array(
                                                    'tipoPessoa'=> '(String 15) PESSOA_JURIDICA ou PESSOA_FISICA [OBRIGATÓRIO].',
                                                    'documento' => '(String 14) CPF ou CNPJ do Beneficiário do boleto [OBRIGATÓRIO].',
                                                    'nome'      => '(String 40) Nome do Beneficiário [OBRIGATÓRIO].',
                                                    'endereco'  => '(String 40) Endereço do Beneficiário',
                                                    'cidade'    => '(String 25) Cidade do Beneficiário',
                                                    'uf'        => '(String 2) UF do Beneficiário',
                                                    'cep'       => '(String 8) [0-9] CEP do Beneficiário',
                                                    'telefone'  => '(String 11) [0-9] Telefone do Beneficiário',
                                                    'email'     => '(String 40) Email do Beneficiário'
                                                )
                                            );
            $data['setEspecieDocumento']    = array('[OBRIGATÓRIO] (String 29) Espécie de documento do título:',
                                                array(
                                                    'DUPLICATA MERCANTIL INDICAÇÃO',
                                                    'DUPLICATA RURAL',
                                                    'NOTA PROMISSORIA',
                                                    'NOTA PROMISSORIA RURAL',
                                                    'NOTA DE SEGUROS',
                                                    'RECIBO',
                                                    'LETRA DE CAMBIO',
                                                    'NOTA DE DÉBITO',
                                                    'DUPLICATA DE SERVICO INDICAÇÃO',
                                                    'OUTROS',
                                                    'BOLETO PROPOSTA',
                                                    'CARTÃO DE CRÉDITO',
                                                    'BOLETO DEPÓSITO'
                                                )
                                            );
            $data['setNossoNumero']         = '(String 9) Nosso número, Sicredi Gera utomaticamenteo';
            $data['setSeuNumero']           = '[OBRIGATÓRIO] (String 10) Número de controle interno do beneficiário que faz referência ao pagador, seu número para utilizar como referencia para baixas no sistema interno';
            $data['setDataVencimento']      = '[OBRIGATÓRIO] (Date \'YYYY-MM-DD\') Data de vencimento do boleto';
            $data['setValor']               = '[OBRIGATÓRIO] (number 14,2) Valor do Boleto';
            $data['setTipoDesconto']        = '(String 10) Tipo de desconto podendo ser: VALOR ou PERCENTUAL';
            $data['setValorDesconto1']      = '(number 14,2)';
            $data['setDataDesconto1']       = '(date \'YYYY-MM-DD\') Data limite para concessão do desconto';
            $data['setValorDesconto2']      = '(number 14,2)';
            $data['setDataDesconto2']       = '(date \'YYYY-MM-DD\') Data limite para concessão do desconto';
            $data['setValorDesconto3']      = '(number 14,2)';
            $data['setDataDesconto3']       = '(date \'YYYY-MM-DD\') Data limite para concessão do desconto';
            $data['setTipoJuros']           = '(string 10) Tipo de Juros, podendo ser: VALOR ou PERCENTUAL';
            $data['setJuros']               = '(number 14,2) Valor de Juros a cobrar por dia';
            $data['setMulta']               = '(number 5,2) Percentual de multa a cobrar';
            $data['setInformativos']        = '(array(até 5) - 400) Permitido informar uma lista de até 5 informativos, com limite de 80 caracteres para cada';
            $data['setMensagens']           = '(array(até 4) - 320) Permitido informar uma lista de até 4 mensagens, com limite de 80 caracteres para cada';
            return json_encode($data);
        }
        /**
         * @param $DadosBoleto $tipoCobranca
         *
         * @return self
         */
        public function setTipoCobranca($tipoCobranca){
            $this->tipoCobranca = $tipoCobranca;
        }

        /**
         * @param $DadosBoleto $codigoBeneficiario
         *
         * @return self
         */
        public function setCodigoBeneficiario($codigoBeneficiario){
            $this->codigoBeneficiario = $codigoBeneficiario;
        }

        /**
         * @param $DadosBoleto $pagador
         *
         * @return self
         */
        public function setPagador($pagador){
            $this->pagador = $pagador;
        }

        /**
         * @param $DadosBoleto $beneficiarioFinal
         *
         * @return self
         */
        public function setBeneficiarioFinal($beneficiarioFinal){
            $this->beneficiarioFinal = $beneficiarioFinal;
        }

        /**
         * @param $DadosBoleto $especieDocumento
         *
         * @return self
         */
        public function setEspecieDocumento($especieDocumento){
            $this->especieDocumento = $especieDocumento;
        }

        /**
         * @param $DadosBoleto $nossoNumero
         *
         * @return self
         */
        public function setNossoNumero($nossoNumero){
            $this->nossoNumero = $nossoNumero;
        }

        /**
         * @param $DadosBoleto $seuNumero
         *
         * @return self
         */
        public function setSeuNumero($seuNumero){
            $this->seuNumero = $seuNumero;
        }

        /**
         * @param $DadosBoleto $dataVencimento
         *
         * @return self
         */
        public function setDataVencimento($dataVencimento){
            $this->dataVencimento = $dataVencimento;
        }

        /**
         * @param $DadosBoleto $valor
         *
         * @return self
         */
        public function setValor($valor){
            $this->valor = $valor;
        }

        /**
         * @param $DadosBoleto $tipoDesconto
         *
         * @return self
         */
        public function setTipoDesconto($tipoDesconto){
            $this->tipoDesconto = $tipoDesconto;
        }

        /**
         * @param $DadosBoleto $valorDesconto1
         *
         * @return self
         */
        public function setValorDesconto1($valorDesconto1){
            $this->valorDesconto1 = $valorDesconto1;
        }

        /**
         * @param $DadosBoleto $dataDesconto1
         *
         * @return self
         */
        public function setDataDesconto1($dataDesconto1){
            $this->dataDesconto1 = $dataDesconto1;
        }

        /**
         * @param $DadosBoleto $valorDesconto2
         *
         * @return self
         */
        public function setValorDesconto2($valorDesconto2){
            $this->valorDesconto2 = $valorDesconto2;
        }

        /**
         * @param $DadosBoleto $dataDesconto2
         *
         * @return self
         */
        public function setDataDesconto2($dataDesconto2){
            $this->dataDesconto2 = $dataDesconto2;
        }

        /**
         * @param $DadosBoleto $valorDesconto3
         *
         * @return self
         */
        public function setValorDesconto3($valorDesconto3){
            $this->valorDesconto3 = $valorDesconto3;
        }

        /**
         * @param $DadosBoleto $dataDesconto3
         *
         * @return self
         */
        public function setDataDesconto3($dataDesconto3){
            $this->dataDesconto3 = $dataDesconto3;
        }

        /**
         * @param $DadosBoleto $tipoJuros
         *
         * @return self
         */
        public function setTipoJuros($tipoJuros){
            $this->tipoJuros = $tipoJuros;
        }

        /**
         * @param $DadosBoleto $juros
         *
         * @return self
         */
        public function setJuros($juros){
            $this->juros = $juros;
        }

        /**
         * @param $DadosBoleto $multa
         *
         * @return self
         */
        public function setMulta($multa){
            $this->multa = $multa;
        }

        /**
         * @param $DadosBoleto $informativos
         *
         * @return self
         */
        public function setInformativos($informativos){
            $this->informativos = $informativos;
        }

        /**
         * @param $DadosBoleto $mensagens
         *
         * @return self
         */
        public function setMensagens($mensagens){
            $this->mensagens = $mensagens;
        }
}