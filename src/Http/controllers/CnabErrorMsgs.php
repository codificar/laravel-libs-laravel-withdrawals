<?php

namespace Codificar\Withdrawals\Http\Controllers;

class CnabErrorMsgs {
    public $errorMsgs = array(
        '00' => 'Crédito ou Débito Efetivado -> Este código indica que o pagamento foi confirmado',
        '01' => 'Insuficiência de Fundos - Débito não efetuado',
        '02' => 'Crédito ou Débito Cancelado pelo Pagador/Credor',
        '03' => 'Débito Autorizado pela Agência - Efetuado',
        'HA' => 'Lote não aceito',
        'HB' => 'Inscrição da Empresa Inválida para o Contrato',
        'HC' => 'Convênio com a Empresa Inexistente/Inválido para o Contrato',
        'HD' => 'Agência/Conta Corrente da Empresa Inexistente/Inválido para o Contrato',
        'HE' => 'Tipo de Serviço Inválido para o Contrato',
        'HF' => 'Conta Corrente da Empresa com Saldo Insuficiente',
        'HG' => 'Lote de Serviço fora de Sequência',
        'HH' => 'Lote de serviço inválido',
        'HI' => 'Número da remessa inválido',
        'HJ' => 'Arquivo sem “HEADER”',
        'HK' => 'Código remessa/retorno inválido',
        'HL' => 'Versão de layout inválida',
        'HM' => 'Versão do arquivo inválido',
        'HV' => 'Quantidade de parcela inválida',
        'AA' => 'Controle inválido',
        'AB' => 'Tipo de operação inválido',
        'AC' => 'Tipo de serviço inválido',
        'AD' => 'Forma de Lançamento inválida',
        'AE' => 'Tipo/Número de inscrição inválido',
        'AF' => 'Código de convênio inválido',
        'AG' => 'Agência/Conta corrente/DV inválido',
        'AH' => 'Número sequencial do registro no lote inválido',
        'AI' => 'Código de segmento de detalhe inválido',
        'AJ' => 'Tipo de movimento inválido',
        'AK' => 'Código da câmara de compensação do banco favorecido/depositário inválido',
        'AL' => 'Código do banco favorecido ou depositário inválido',
        'AM' => 'Agência mantenedora da conta corrente do favorecido inválida',
        'AN' => 'Conta Corrente / DV do favorecido inválido',
        'AO' => 'Nome do favorecido não informado',
        'AP' => 'Data de lançamento inválido',
        'AQ' => 'Tipo/quantidade de moeda inválida',
        'AR' => 'Valor do lançamento inválido',
        'AS' => 'Aviso ao favorecido - identificação inválida',
        'AT' => 'Tipo/número de inscrição do favorecido inválido',
        'AU' => 'Logradouro do favorecido não informado',
        'AV' => 'Número do local do favorecido não informado',
        'AW' => 'Cidade do favorecido não informada',
        'AX' => 'CEP/complemento do favorecido inválido',
        'AY' => 'Sigla do Estado do Favorecido Inválido',
        'AZ' => 'Código/nome do banco depositário inválido',
        'BA' => 'Código/nome da agência depositária não informado',
        'BB' => 'Seu número inválido',
        'BC' => 'Nosso número inválido',
        'BD' => 'Inclusão efetuada com sucesso',
        'BE' => 'Alteração efetuada com sucesso',
        'BF' => 'Exclusão efetuada com sucesso',
        'BG' => 'Agência/conta impedida legalmente',
        'BL' => 'Valor da parcela inválido',
        'BV' => 'Tipo boleto não admite juros/multa/desc/abatimento',
        'BX' => 'Data limite para pagamento inválido',
        'BY' => 'Validação do título indisponível',
        'BZ' => 'Inclusão efetuada sem validação do título',
        'CA' => 'Código de barras - código do banco inválido',
        'CB' => 'Código de barras - código da moeda inválida',
        'CC' => 'Código de barras - dígito verificador geral inválido',
        'CD' => 'Código de barras - valor do título inválido',
        'CE' => 'Código de barras - campo livre inválido ',
        'CF' => 'Valor do documento inválido',
        'CG' => 'Valor do abatimento inválido',
        'CH' => 'Valor do desconto inválido',
        'CI' => 'Valor de mora inválido',
        'CJ' => 'Valor da multa inválido',
        'CK' => 'Valor do IR inválido',
        'CL' => 'Valor do ISS inválido',
        'CM' => 'Valor do IOF inválido',
        'CN' => 'Valor de outras deduções inválido',
        'CO' => 'Valor de outros acréscimos inválido',
        'CP' => 'Valor do INSS inválido',
        'CQ' => 'Código de barras inválido',
        'DA' => 'Beneficiário não cadastrado',
        'DB' => 'Situação do beneficiário não permite pagamento',
        'TA' => 'Lote não aceito - totais de lote com diferença',
        'TB' => 'Lote sem trailler',
        'TC' => 'Lote de Arquivo sem trailler',
        'YA' => 'Título não encontrado',
        'YB' => 'Identificador registro opcional inválido',
        'YC' => 'Código padrão inválido',
        'YD' => 'Código de ocorrência inválido',
        'YE' => 'Complemento de ocorrência inválido',
        'YF' => 'Alegação já informada',
        'ZA' => 'Agência/conta do favorecido substituída -> As ocorrências iniciadas com "ZA" tem caráter informativo para o cliente',
        'ZE' => 'Título bloqueado na base',
        'ZJ' => 'Limite de pagamentos parciais excedidos',
        'ZK' => 'Pagamento Rejeitado - Boleto Já Liquidado',
        'ZY' => 'Pagamento Rejeitado - Beneficiário Divergente'
    );

    public function getErrorMsg($errorCodes) {
        $msg = "";
        if($errorCodes) {
            $errosCodeArray = str_split($errorCodes, 2); //cada erro tem 2 caracteres. O total maximo de erros possiveis sao 5 erros (uma string de 10 caracteres em sequencia)
            foreach($errosCodeArray as $key=>$errorCode) {
                if(isset($this->errorMsgs[$errorCode])) {
                    if($key == 0)
                        $msg =  "1: " . $this->errorMsgs[$errorCode];
                    else
                        $msg = $msg . " - " . strval($key+1) . ": " . $this->errorMsgs[$errorCode];
                }
            }
        }
        $msg = $msg ? $msg : "O banco não retornou o motivo do erro";
        return $msg;
    }
}