<?php

namespace Codificar\Withdrawals\Http\Controllers;

use Codificar\Withdrawals\Models\Withdrawals;
use Codificar\Withdrawals\Models\CnabFiles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use \Exception as Exception;
use File;

//FormRequest
use Codificar\Withdrawals\Http\Requests\ProviderAddWithdrawalFormRequest;
use Codificar\Withdrawals\Http\Requests\SaveCnabSettingsFormRequest;
use Codificar\Withdrawals\Http\Requests\ConfirmWithdrawFormRequest;
use Codificar\Withdrawals\Http\Requests\SendRetFileFormRequest;
use Codificar\Withdrawals\Http\Requests\SaveWithdrawalsSettingsFormRequest;

//Resource
use Codificar\Withdrawals\Http\Resources\ProviderWithdrawalsReportResource;
use Codificar\Withdrawals\Http\Resources\ProviderAddWithdrawalResource;
use Codificar\Withdrawals\Http\Resources\ConfirmWithdrawResource;
use Codificar\Withdrawals\Http\Resources\saveCnabSettingsResource;
use Codificar\Withdrawals\Http\Resources\SendRetFileResource;
use Codificar\Withdrawals\Http\Resources\getWithdrawSettingsResource;
use Codificar\Withdrawals\Http\Resources\saveWithdrawalsSettingsResource;

//Gerar arquivo de remessa CNAB
use \CnabPHP\Remessa;

use Input, Validator, View, Response;
use Provider, Settings, Ledger, Finance, Bank, LedgerBankAccount;

class WithdrawalsController extends Controller {

    public function getWithdrawalsReport()
    {
        // Get the provider id (some projects is 'provider_id' and others is just 'id')
        $providerId = Input::get('provider_id') ? Input::get('provider_id') : Input::get('id');
        $provider = Provider::find($providerId);
        
        $withdrawals_report = Withdrawals::getWithdrawals(false, "provider", $provider->ledger->id);
        // Return data
		return new ProviderWithdrawalsReportResource([
            'withdrawals_report' => $withdrawals_report
		]);
    }

    public function addWithDraw(ProviderAddWithdrawalFormRequest $request)
    {
        // Get the params
        $providerId = $request->get('provider_id');
        $value = $request->get('withdraw_value');
        $bankAccountId = $request->get('bank_account_id');

        // Get the ledger
        $ledger = Ledger::findByProviderId($providerId);
        
        // Get the current balance from ledger. 
        $currentBalance = Finance::sumValueByLedgerId($ledger->id);

        // Get the settings of withdraw
        $withDrawSettings = Withdrawals::getWithdrawalsSettings(false);
        

        // Return data
		return new ProviderAddWithdrawalResource([
            'ledger'            => $ledger,
            'withdraw_value'    => $value,
            'bank_account_id'   => $bankAccountId,
            'current_balance'   => $currentBalance,
            'withdraw_settings' => $withDrawSettings
		]);

    }

    public function getWithdrawSettings()
    {

        // Get the provider id (some projects is 'provider_id' and others is just 'id')
        $providerId = Input::get('provider_id') ? Input::get('provider_id') : Input::get('id');
        $provider = Provider::find($providerId);

        $withDrawSettings = Withdrawals::getWithdrawalsSettings(true);

        // Get the current balance from ledger. 
        $currentBalance = currency_format(Finance::sumValueByLedgerId($provider->ledger->id));
        // Get the current balance from ledger. 
        $providerBanks = Withdrawals::getledgerBankAccount($provider->ledger->id);

        // Return data
		return new getWithdrawSettingsResource([
            'withdraw_settings' => $withDrawSettings,
            'current_balance'   => $currentBalance,
            'provider_banks'    => $providerBanks
		]);

    }

    

     /**
     * View the withdrawals report
     * 
     * @return View
     */
    public function getCnabSettings() {

        $settings = Withdrawals::getCnabSettings();
        $cnabFiles = CnabFiles::getCnabfiles();
        $totalRequested = Withdrawals::getTotalValueRequestedWithdrawals();
        $totalAwaitingReturn = Withdrawals::getTotalValueAwaitingReturnWithdrawals();
        $TotalError = Withdrawals::getTotalErroWithdrawals();
        return View::make('withdrawals::cnab')
            ->with([
                'settings' => $settings,
                'cnabFiles' => $cnabFiles,
                'totalRequested' => $totalRequested,
                'totalAwaitingReturn' => $totalAwaitingReturn,
                'totalError' => $TotalError
            ]);
    
    }

     /**
     * View the withdrawals report
     * 
     * @return View
     */
    public function getWithdrawalsSettingsWeb() {

        $settings = Withdrawals::getWithdrawalsSettings(false);

        return View::make('withdrawals::withdrawals_settings')
            ->with([
                'settings' => $settings
            ]);
    
    }

    public function saveWithdrawalsSettings(SaveWithdrawalsSettingsFormRequest $request) {

        // Return data
		return new saveWithdrawalsSettingsResource([
            "message" => 'sucess',
            "with_draw_enabled" => $request->settings['with_draw_enabled'],
            "with_draw_max_limit" => $request->settings['with_draw_max_limit'],
            "with_draw_min_limit" => $request->settings['with_draw_min_limit'],
            "with_draw_tax" => $request->settings['with_draw_tax']
		]);
    }

    public function saveCnabSettings(SaveCnabSettingsFormRequest $request)
    {
        // Return data
		return new saveCnabSettingsResource([
            "message" => 'sucess',
            "rem_company_name"      => $request->settings['rem_company_name'],
            "rem_cpf_or_cnpj"       => $request->settings['rem_cpf_or_cnpj'],
            "rem_document"          => $request->settings['rem_document'],
            "rem_agency"            => $request->settings['rem_agency'],
            "rem_agency_dv"         => $request->settings['rem_agency_dv'],
            "rem_account"           => $request->settings['rem_account'],
            "rem_account_dv"        => $request->settings['rem_account_dv'],
            "rem_bank_code"         => $request->settings['rem_bank_code'],
            "rem_agreement_number"  => $request->settings['rem_agreement_number'],
            "rem_transfer_type"     => $request->settings['rem_transfer_type'],

            "rem_environment"       => $request->settings['rem_environment'],
            "rem_address"           => $request->settings['rem_address'],
            "rem_address_number"    => $request->settings['rem_address_number'],
            "rem_city"              => $request->settings['rem_city'],
            "rem_cep"               => $request->settings['rem_cep'],
            "rem_state"             => $request->settings['rem_state']
		]);
    }


    private function checkAllCnabSettings($settings) {
        $error = null;
        if(!$settings['rem_company_name']) 
            $error = "Preencha o nome da empresa";
        else if(!($settings['rem_cpf_or_cnpj'] == "cpf" || $settings['rem_cpf_or_cnpj'] == "cnpj"))
            $error = "Preencha o tipo de documento (cpf ou cnpj)";
        else if(!$settings['rem_document']) 
            $error = "Preencha o documento";
        else if(!$settings['rem_agency']) 
            $error = "Preencha a agencia";
        else if(!$settings['rem_agency_dv']) 
            $error = "Preencha o digito da agencia";
        else if(!$settings['rem_account']) 
            $error = "Preencha a conta bancaria";
        else if(!$settings['rem_account_dv']) 
            $error = "Preencha o digito da conta";
        else if(!$settings['rem_bank_code']) 
            $error = "Preencha o banco";
        else if(!$settings['rem_agreement_number']) 
            $error = "Preencha o convenio";
        else if(!($settings['rem_transfer_type'] == "ted" || $settings['rem_transfer_type'] == "doc"))
            $error = "Preencha o tipo de transferencia (ted ou doc)";
        else if(!($settings['rem_environment'] == "T" || $settings['rem_environment'] == "P"))
            $error = "Preencha o ambiente (T ou P) teste ou producao";
        else if(!$settings['rem_address']) 
            $error = "Preencha o endereco";
        else if(!$settings['rem_address_number']) 
            $error = "Preencha o numero do endereco";
        else if(!$settings['rem_city']) 
            $error = "Preencha a cidade";
        else if(!$settings['rem_cep']) 
            $error = "Preencha o cep";
        else if(!$settings['rem_state']) 
            $error = "Preencha o estado";

        if($error)
            return $error;
        else 
            return null;
    }

    public function createCnabFile()
    {
        $total = Withdrawals::getTotalValueRequestedWithdrawals();

        //Se o total eh maior que 0, entao gera um arquivo de remessa
        if($total > 0) {

            //Pega as configuracoes da geracao do arquivo de remessa
            $getSettings = Withdrawals::getCnabSettings();
            $settings = array();
            foreach($getSettings as $eachSetting){
                $settings[$eachSetting->key] = $eachSetting->value;
            }

            //Checa se foi preenchido todos os campos de configuracoes corretamente
            $cnabSettingsError = $this->checkAllCnabSettings($settings);
            if($cnabSettingsError) {
                $responseArray = array('success' => false, 'errors' => $cnabSettingsError, 'errorCode' => 401, 'messages' => $cnabSettingsError);
		        $responseCode = 200;
            } else {


                /**
                 * O fluxo eh o seguinte:
                 * 1º cria a linha do arquivo de remessa no banco de dados (apenas para pegar o id para gerar o arquivo depois)
                 * 2º em um try-catch, tenta gerar o arquivo de remessa. Esse arquivo de remessa tem o id da row do banco que foi gerada anteriormente.
                 * 3º Se funcionar (nao cair no catch), salva o arquivo dentro do projeto (pasta public/uploads) e depois insere o link na coluna ret_url_file da row que foi criada anteriormente
                 * 4º Se nao funcionar (cair no catch) entao remove a row que foi criada anteriormente. 
                 */


                //Cria a row do arquivo no banco (por enquanto sem o link. Isso eh feito para pegar o id da linha e inserir na hora de gerar o arquivo)
                $modelCnabFile = new CnabFiles;
                $modelCnabFile->save();

                try {
                    
                    //Pega as informacoes dos favorecidos
                    $beneficiariesData = Withdrawals::getProviderDataToCreateCnab();


                    /**
                     * documentos uteis:
                     * pag 24 - https://www.bb.com.br/docs/pub/emp/empl/dwn/000Completo.pdf
                     * http://suporte.quarta.com.br/LayOuts/Bancos/18-Santander%20(febraban).pdf
                     */
                    //Registro 0
                    $arquivo = new Remessa("104",'cnab240_transf',array(
                        'tipo_inscricao'        => $settings['rem_cpf_or_cnpj'] == "cpf" ? 1 : 2, // 1 para cpf, 2 cnpj 
                        'numero_inscricao'      => substr($settings['rem_document'],0,14), // seu cpf ou cnpj completo - max 14
                        'convenio_caixa'        => substr($settings['rem_agreement_number'],0,6), // informado pelo banco, ate 6 digitos
                        'param_transmissao'     => '01', // ate 2 digitos, fornecido pela caixa
                        'amb_cliente'           => $settings['rem_environment'], // T teste e P producao
                        'agencia'               => substr($settings['rem_agency'],0,5), // sua agencia (pagador), sem o digito verificador 
                        'agencia_dv'            => substr($settings['rem_agency_dv'], 0,1), // somente o digito verificador da agencia 
                        'conta'                 => substr($settings['rem_account'],0,12), // numero da sua conta
                        'conta_dv'              => substr($settings['rem_account_dv'],0,1), // digito da conta
                        'nome_empresa'          => mb_substr($settings['rem_company_name'],0,30), // seu nome de empresa max 30
                        'numero_sequencial_arquivo' => $modelCnabFile->id, // sequencial do arquivo um numero novo para cada arquivo gerado
                        'reservado_empresa'     => $modelCnabFile->id, // sequencial do arquivo um numero novo para cada arquivo gerado
                        'somatorio_valores'     => $total
                    ));

                    //Registro 1
                    $lote  = $arquivo->addLote(array(   //HEADER DO LOTE
                        'tipo_servico_transf'   => '98', // '98' = Pagamentos Diversos - tem a lista na pagina 39, G025 http://www.caixa.gov.br/Downloads/pagamentos-de-salarios-fornecedores-e-auto-pagamento/Leiaute_CNAB_240_Pagamentos.pdf
                        'forma_lancamento'      => $settings['rem_transfer_type'] == "doc" ? '03' : "41", // 03 DOC. e 41 TED. lista completa na pag 39, G029,  http://www.caixa.gov.br/Downloads/pagamentos-de-salarios-fornecedores-e-auto-pagamento/Leiaute_CNAB_240_Pagamentos.pdf
                        'convenio_caixa'        => substr($settings['rem_agreement_number'],0,6),    // informado pelo banco, convenio caixa (ate 6 digitos)
                        'tipo_compromisso'      => '01',     //01 Pagamento a Fornecedor - 02 Pagamento de Salarios - 03 Autopagamento - 06 Salario Ampliacao de Base - 11 Debito em Conta			
                        'codigo_compromisso'    => '0001', // informado pelo banco. 4 Digitos
                        'param_transmissao'     => '01', // informado pelo banco, ate 2 digitos,
                        'logradouro'            => mb_substr($settings['rem_address'],0,30),
                        'numero_endereco'       => substr($settings['rem_address_number'],0,5),
                        'cidade'                => mb_substr($settings['rem_city'],0,20),
                        'cep'                   => substr($settings['rem_cep'], 0, 5), //pega do 0 ate o 5 caracter
                        'complemento_cep'       => substr($settings['rem_cep'], 5, 3), //pega a partir do 5 caracter e os proximos 3
                        'estado'                => substr($settings['rem_state'],0,2)
                    )); 

                    foreach($beneficiariesData as $data) {
                        $lote->inserirTransferencia(array(

                            //Segmento A
                            'codigo_camera'     => $settings['rem_transfer_type'] == "doc" ? '700' : '018', // 018 TED - 700 DOC/OP - 000 Credito em Conta - 888 Boleted/ISPB 
                            'cod_banco_fav'     => substr($data->bank_code, 0, 3), //max 3 caracteres
                            'agen_cta_favor'    => substr($data->agency, 0, 5),
                            'dig_ver_agen'      => (isset($data->agency_digit) && strval($data->agency_digit)) ? substr(strval($data->agency_digit), 0, 1) : '0',
                            'conta_corrente_fav'=> substr($data->account, 0, 12), //max 12
                            'dig_conta_fav'     => substr($data->account_digit, 0, 1), //max 1
                            'nome_fav'          => mb_substr($data->favoredName, 0, 30), //max 30
                            'num_atribuido_empresa' => $data->id, //numero para identificar a transferencia. Retornado conforme recebido. Deve ser maior que 0.
                            'tipo_conta_ted'    => $data->account_type == "conta_corrente" || $data->account_type == "conta_corrente_conjunta" ? '1' : '2', // 1 – Conta corrente; 2 – Poupança;
                            'cod_finalidade_doc'=> $settings['rem_transfer_type'] == "doc" ? '01' : '00', //preencher apenas se a transferencia for do tipo doc. Valores na tabela P005, pag 39 - http://www.caixa.gov.br/Downloads/pagamentos-de-salarios-fornecedores-e-auto-pagamento/Leiaute_CNAB_240_Pagamentos.pdf
                            'data_pagamento'    => date("Y/m/d"),
                            'valor_pagamento'   => $data->totalValue,
                            
                            //Segmento B
                            'logradouro'        => isset($data->address) && $data->address ? mb_substr($data->address, 0, 30) : "NA", //max 30
                            'num_do_local'      => isset($data->address_number) && $data->address_number ? substr($data->address_number, 0, 5) : "0", //max 5
                            'bairro'            => isset($data->address_neighbour) && $data->address_neighbour ? mb_substr($data->address_neighbour, 0, 15) : "NA", //max 15
                            'cidade'            => isset($data->address_city) && $data->address_city ? mb_substr($data->address_city, 0, 20) : "NA", //max 20
                            'cep'               => isset($data->zipcode) && strlen($data->zipcode) == 8 ? substr($data->zipcode, 0, 5) : "99999", //pega do 0 ate o 5 caracter
                            'complemento_cep'   => isset($data->zipcode) && strlen($data->zipcode) == 8 ? substr($data->zipcode, 5, 3) : "999", //pega a partir do 5 caracter e os proximos 3
                            'sigla_estado'      => isset($data->state) && $data->state ? substr($data->state, 0, 2) : "NA", //max 2
                            'tipo_inscricao'    => $data->person_type != 'individual' ? 2 : 1, //campo fixo, escreva '1' se for pessoa fisica, 2 se for pessoa juridica
                            'numero_inscricao'  => substr($data->document, 0, 14), //cpf ou ncpj do favorecido - max 14
                        ));
                    }
                

                    //get file
                    $cnabFile = utf8_decode($arquivo->getText());
                        
                    // get nome
                    $file_name = time();
                    $file_name .= rand();
                    $file_name = sha1($file_name);

                    //Upload
                    $teste = file_put_contents(public_path() . "/uploads/" . $file_name . ".txt", $cnabFile);

                    // Upload to S3
                    $local_url = $file_name . ".txt";
                    $s3_url = upload_to_s3($file_name, $local_url);
                    
                    //Atualiza o link do arquivo no banco
                    $modelCnabFile->rem_total = $total;
                    $modelCnabFile->rem_url_file = $s3_url;
                    $modelCnabFile->date_rem = date("Y-m-d H:i:s");
                    $modelCnabFile->save();

                    //Se chegou ate aqui, troca o status do saque de 'solicitado' para 'aguardando retorno'
                    //Isso para todos os benificiarios que foram gerados no arquivo de remessa
                    //Porem somente trocara o status se o ambiente for producao. Se o ambiente for teste, nao altera o status dos saques
                    if($settings['rem_environment'] == "P") {
                        foreach($beneficiariesData as $data) {
                            Withdrawals::updateStatusAndFileAssociated($data->id, $modelCnabFile->id);
                        }
                    }

                    $responseArray = array('success' => true);
                    $responseCode = 200;

                } 
                //Se ocorreu algum erro ao gerar o arquivo de remessa, entao eh necessario remover a row q foi criada no banco anteriormente
                catch (Exception $e) {
                    \Log::error($e);

                    //Por ter dado erro, deleta a row que foi gerada no banco.
                    $modelCnabFile->delete();

                    $responseArray = array('success' => false, 'errors' => 'Ocorreu um erro ao gerar o arquivo de remessa', 'errorCode' => 401, 'messages' => 'Ocorreu um erro ao gerar o arquivo de remessa');
                    $responseCode = 200;
                }
            }
        } else {
            $responseArray = array('success' => false, 'errors' => 'Valor precisa ser maior que 0', 'errorCode' => 401, 'messages' => 'Valor precisa ser maior que 0');
		    $responseCode = 200;
        }
        // Return data
		$response = Response::json($responseArray, $responseCode);
		return $response;
    }



    public function sendRetFile(SendRetFileFormRequest $request) {

        //get the cnab id
        $cnab_id = $request->cnab_id;

        //get the ret file
        $retFile = $request->cnab_ret_file;
       
       // #TODO
       //FAZER A LOGICA DO ARQUIVO DE RETORNO AQUI.


        // Return data
		return new SendRetFileResource([
            "message" => 'sucess',
            "link" => 'link'
		]);
    }



    public function deleteCnabFile()
    {
        $cnabId = Input::get('cnab_id');
        $cnab = CnabFiles::find($cnabId);
        if($cnab) {
            //Verifica se esse arquivo nao tem retorno associado. Arquivos com retorno associados nao podem ser excluidos
            if(!$cnab->ret_url_file) {
                
                //altera o status dos saques relacionados a esse arquivo, de "aguardando remessa" para "solicitado" e remove o arquivo de remessa associado
                Withdrawals::updateWithdrawWhenDeleteCnab($cnab->id);

                //Deleta a row que contem o arquivo rem
                $cnab->delete();
                
                $responseArray = array('success' => true);
		        $responseCode = 200;
            } else {
                $responseArray = array('success' => false, 'errors' => 'Somente arquivos de remessa sem retorno atrelados podem ser excluidos', 'messages' => 'error');
                $responseCode = 200;
            }
        } else {
            $responseArray = array('success' => false, 'errors' => 'id nao encontrado', 'errorCode' => 412, 'messages' => 'Id nao encontrado');
		    $responseCode = 200;
        }
        // Return data
		$response = Response::json($responseArray, $responseCode);
		return $response;
    }



    public function getWithdrawalsReportWebAdmin() {
        return $this->getWithdrawalsReportWeb("admin");
    }
    public function getWithdrawalsReportWebProvider() {
        return $this->getWithdrawalsReportWeb("provider");
    }

    /**
     * View the withdrawals report
     * 
     * @return View
     */
    private function getWithdrawalsReportWeb($enviroment) {

        $ledgerId = null;
        if($enviroment == "provider") {
            $id = \Auth::guard("providers")->user()->id;
            $provider = Provider::find($id);
            $ledgerId = $provider->ledger->id;
        }

        //Get the filters
		$status = Input::get('status');
		$receipt = Input::get('receipt');

        //Get the withdrawals report
        $withdrawals_report = Withdrawals::getWithdrawals(true, $enviroment, $ledgerId, $status, $receipt);

        $types = Finance::TYPES; //Prepares Finance types array to be used on vue component
        $banks = Bank::orderBy('code', 'asc')->get(); //List of banks
        $account_types = LedgerBankAccount::getAccountTypes(); //List of AccountTypes
        
        $currentBalance = Finance::sumValueByLedgerId($ledgerId);

        $withDrawSettings = Withdrawals::getWithdrawalsSettings(false);

        return View::make('withdrawals::withdrawals_report')
                        ->with([
                            'id' => $enviroment == "provider" ? $id : null,
                            'enviroment' => $enviroment,
                            'user_provider_type'  => $enviroment,
                            'holder' => '',
                            'ledger' => isset($provider) ? $provider : "",
                            'withdrawals_report' => json_encode($withdrawals_report),
                            'current_balance' => $currentBalance,
                            'types' => $types,
                            'bankaccounts' => isset($provider->ledger->bankAccounts) ? $provider->ledger->bankAccounts : '',
                            'banks' => $banks,
                            'account_types' => $account_types,
                            'withdrawsettings' => $withDrawSettings
        ]);
        
    }

    public function confirmWithdraw(ConfirmWithdrawFormRequest $request) {

        //get date
        $date = $request->date;
        
        //get picture
        $fileWithdraw = $request->file_withdraw;
       
        // get nome
        $file_name = time();
		$file_name .= rand();
		$file_name = sha1($file_name);

        //get file extension
        $ext = $fileWithdraw->getClientOriginalExtension();

        //Upload
        $fileWithdraw->move(public_path() . "/uploads", $file_name . "." . $ext);
        $local_url = $file_name . "." . $ext;

        // Upload to S3
        $s3_url = upload_to_s3($file_name, $local_url);

        // save image in withdraw table and confirm withdraw
        Withdrawals::addWithdrawReceiptAndConfirm($request->withdraw_id, $s3_url);


        // Return data
		return new ConfirmWithdrawResource([
            "message" => 'sucess',
            "link" => $s3_url
		]);
    }

	public function upload_to_s3($file_name, $local_url)
    {
        // Upload to S3
        if (Settings::findByKey('s3_bucket') != "") {
            $s3 = App::make('aws')->get('s3');
            $pic = $s3->putObject(array(
                'Bucket' => Settings::findByKey('s3_bucket'),
                'Key' => $file_name,
                'SourceFile' => public_path() . "/uploads/" . $local_url
            ));
    
            $s3->putObjectAcl(array(
                'Bucket' => Settings::findByKey('s3_bucket'),
                'Key' => $file_name,
                'ACL' => 'public-read'
            ));
    
            $s3_url = $s3->getObjectUrl(Settings::findByKey('s3_bucket'), $file_name);
        } else {
            $s3_url = asset_url() . '/uploads/' . $local_url;
        }
        return $s3_url;
    }
    




    //
    /**
	 *  Create new user Bank Account
     * 
     * 
	 */
	public function createUserBankAccount() {
        $userId = Input::get('user_id');
        $providerId = Input::get('provider_id');
		$ledgerId = Input::get('ledger_id');

		/**conta bancária*/
		$holder = Input::get('holder');
		$document = Input::get('document');
		$document = preg_replace('/\D/', '', $document); //deixa apenas numeros
		$bankId = Input::get('bank_id');
		$agency = Input::get('agency');
		$agency_digit = Input::get('agency_digit');
		$account = Input::get('account');
		$accountDigit = Input::get('account_digit');
		$optionDocument = Input::get('option_document');
		$account_types = Input::get('account_type');
		$validator = Validator::make(
			array(
				trans('finance.holder_name') 		=> $holder,
				trans('finance.holder_document')	=> $document,
				'bank_id' 							=> $bankId,
				trans('finance.agency_number' )		=> $agency,
				trans('finance.agency_digit') 		=> $agency_digit,
				trans('finance.account_number') 	=> $account,
				trans('finance.account_digit')	 	=> $accountDigit,
				'option_document'					=> $optionDocument,
			),
			array(
				trans('finance.holder_name') 		=> 'required',
				trans('finance.holder_document') 	=> 'required',
				'bank_id' 							=> 'required',
				trans('finance.agency_number') 		=> 'required',
				trans('finance.agency_digit')		=> 'required',
				trans('finance.account_number') 	=> 'required',
				trans('finance.account_digit') 		=> 'required',
				'option_document' 					=> 'required'
			),
			array(
				trans('finance.holder_name' )		=> trans('user_provider_controller.holder_required'),
				trans('finance.holder_document')	=> trans('user_provider_controller.document_required'),
				'bank_id' 							=> trans('user_provider_controller.bank_required'),
				trans('finance.agency_number')		=> trans('user_provider_controller.agency_required'),
				trans('finance.agency_digit')		=> trans('user_provider_controller.agency_digit_required'),
				trans('finance.account_number') 	=> trans('user_provider_controller.account_required'),
				trans('finance.account_digit')	 	=> trans('user_provider_controller.account_digit_required'),
				'option_document'				 	=> trans('user_provider_controller.option_document_required'),
			)
		);

		if($optionDocument == LedgerBankAccount::INDIVIDUAL){
			$validatorDocument = Validator::make(
							array(
								'cpf' => $document,
							),
							array(
								'cpf' => 'cpf'
							),
							array(
								'cpf' => trans('providerController.cpf_invalid')
							)
			);
		}

		else{
			$validatorDocument = Validator::make(
							array(
								'cnpj' => $document,
							),
							array(
								'cnpj' => 'cnpj'
							),
							array(
								'cnpj' => trans('providerController.cnpj_invalid')
							)
			);

        }
        if ($validator->fails()) {
			$errorMessages = $validator->messages()->all();
			$responseArray = array('success' => false, 'error' => trans('accountController.invalid_input'), 'errorCode' => 401, 'messages' => $errorMessages);
		}else if($validatorDocument->fails()){
			$errorMessages = $validatorDocument->messages();
			$responseArray = array('success' => false, 'error' => trans('accountController.invalid_input'), 'errorCode' => 401, 'messages' => $errorMessages);
		} else {
			// salvar informações da conta bancária
			
			$ledgerBankAccount = new LedgerBankAccount();
            

			$ledgerBankAccount->ledger_id = $ledgerId;
			$ledgerBankAccount->holder = $holder;
			$ledgerBankAccount->document = $document;
			$ledgerBankAccount->bank_id = $bankId;
			$ledgerBankAccount->agency = $agency;
			$ledgerBankAccount->agency_digit = $agency_digit;
			$ledgerBankAccount->account = $account;
			$ledgerBankAccount->account_type = $account_types;
			$ledgerBankAccount->account_digit = $accountDigit;
			$ledgerBankAccount->recipient_id = 'empty';
            $ledgerBankAccount->person_type = $optionDocument;
            if ($userId > 0) 		$ledgerBankAccount->user_id = $userId;
            if ($providerId > 0) 	$ledgerBankAccount->provider_id = $providerId;
            $ledgerBankAccount->save();
            

			$responseArray = array('success' => true, 'bank_account' => $ledgerBankAccount);

        }
        $responseCode = 200;
		$response = Response::json($responseArray, $responseCode);
		return $response;
    }


    public function downloadWithdrawalsReportAdmin() {
        //Get the filters
		$status = Input::get('status');
		$receipt = Input::get('receipt');

        //Get the withdrawals report
        $withdrawals = Withdrawals::getWithdrawals(false, "admin", null, $status, $receipt);
        return $this->downloadWithdrawalsReportCSV($withdrawals);	
    }

    public function downloadWithdrawalsReportProvider() {
        //Get the filters
		$status = Input::get('status');
        $receipt = Input::get('receipt');
        
        $id = \Auth::guard("providers")->user()->id;
        $provider = Provider::find($id);
        $ledgerId = $provider->ledger->id;

        //Get the withdrawals report
        $withdrawals = Withdrawals::getWithdrawals(false, "provider", $ledgerId, $status, $receipt);
        return $this->downloadWithdrawalsReportCSV($withdrawals);	
    }
    

    /**
	 * Download csv of withdrawals report
	 *
	 * @return void
	 */	
	private function downloadWithdrawalsReportCSV($withdrawals){

		// Setting the output filename 
		$filename = "relatorio-saques-".date("Y-m-d-hms", time()).".csv";
		$handle = fopen(storage_path('tmp/').$filename, 'w+');
		fputs( $handle, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
		// Setting the csv header
		fputcsv($handle,
			array(
                trans("libTans::withdrawals.id"),
                trans("libTans::withdrawals.name"),
                trans("libTans::withdrawals.bank"),
                trans("libTans::withdrawals.agency"),
                trans("libTans::withdrawals.agency_digit"),
                trans("libTans::withdrawals.account"),
                trans("libTans::withdrawals.account_digit"),
                trans("libTans::withdrawals.holder_document"),
                trans("libTans::withdrawals.status"),
                trans("libTans::withdrawals.finance_date"),
                trans("libTans::withdrawals.finance_value")
			),
			";"
		);
		
		foreach ($withdrawals as $key => $withdraw) {

			// Formats the csv file
			fputcsv($handle,
				array(
                    $withdraw->id,
                    $withdraw->name,
                    $withdraw->bank,
                    $withdraw->agency,
                    $withdraw->agency_digit,
                    $withdraw->account,
                    $withdraw->account_digit,
                    $withdraw->document,
                    $this->getWithdrawStatus($withdraw->type),
                    $withdraw->date,
                    currency_format($withdraw->value),
				),
				";"
			);
		}
		// Close the pointer file
		fclose($handle);
		$headers = array(
			'Content-Type' => 'text/csv; charset=utf-8',
			'Content-Disposition' => 'attachment; filename='. $filename,
		);
		return Response::download(storage_path('tmp/').$filename, $filename, $headers);		
    }
    
    private function getWithdrawStatus($status) {
        switch ($status) {
            case "requested":
                $value = trans("libTans::withdrawals.withdrawal_requested");
                break;
            case "awaiting_return":
                $value = trans("libTans::withdrawals.awaiting_return");
                break;
            case "concluded":
                $value = trans("libTans::withdrawals.concluded");
                break;
            case "error":
                $value = trans("libTans::withdrawals.error");
                break;
        }
        return $value;
    } 


}