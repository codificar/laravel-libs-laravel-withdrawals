<?php

namespace Codificar\Withdrawals\Http\Controllers;

use Codificar\Withdrawals\Models\Withdrawals;
use Codificar\Withdrawals\Models\CnabFiles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use File;

//FormRequest
use Codificar\Withdrawals\Http\Requests\ProviderAddWithdrawalFormRequest;
use Codificar\Withdrawals\Http\Requests\SaveWithdrawalSettingsFormRequest;
use Codificar\Withdrawals\Http\Requests\ConfirmWithdrawFormRequest;
use Codificar\Withdrawals\Http\Requests\SendRetFileFormRequest;

//Resource
use Codificar\Withdrawals\Http\Resources\ProviderWithdrawalsReportResource;
use Codificar\Withdrawals\Http\Resources\ProviderAddWithdrawalResource;
use Codificar\Withdrawals\Http\Resources\ConfirmWithdrawResource;
use Codificar\Withdrawals\Http\Resources\saveCnabSettingsResource;
use Codificar\Withdrawals\Http\Resources\SendRetFileResource;
use Codificar\Withdrawals\Http\Resources\getWithdrawSettingsResource;

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
        
        $withdrawals_report = Withdrawals::getWithdrawalsSummary($provider->ledger->id, 'provider');
        
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
        $withDrawSettings = array(
            'with_draw_enabled' => Settings::getWithDrawEnabled(),
            'with_draw_max_limit' => Settings::getWithDrawMaxLimit(),
            'with_draw_min_limit' => Settings::getWithDrawMinLimit(),
            'with_draw_tax' => Settings::getWithDrawTax()
        );
        

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

        $withDrawSettings = array(
            'with_draw_enabled' => Settings::getWithDrawEnabled(),
            'with_draw_max_limit' => Settings::getWithDrawMaxLimit(),
            'with_draw_min_limit' => Settings::getWithDrawMinLimit(),
            'with_draw_tax' => Settings::getWithDrawTax()
        );

        // Get the current balance from ledger. 
        $currentBalance = Finance::sumValueByLedgerId($provider->ledger->id);
        

        // Return data
		return new getWithdrawSettingsResource([
            'withdraw_settings' => $withDrawSettings,
            'current_balance'    => $currentBalance
		]);

    }

    

     /**
     * View the withdrawals report
     * 
     * @return View
     */
    public function getCnabSettings() {

        $settings = Withdrawals::getWithdrawalsSettings();
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

    public function saveCnabSettings(SaveWithdrawalSettingsFormRequest $request)
    {
        \Log::debug($request->settings['rem_bank_code']);

        // Return data
		return new saveCnabSettingsResource([
            "message" => 'sucess',
            "rem_company_name" => $request->settings['rem_company_name'],
            "rem_cpf_or_cnpj" => $request->settings['rem_cpf_or_cnpj'],
            "rem_document" => $request->settings['rem_document'],
            "rem_agency" => $request->settings['rem_agency'],
            "rem_agency_dv" => $request->settings['rem_agency_dv'],
            "rem_account" => $request->settings['rem_account'],
            "rem_account_dv" => $request->settings['rem_account_dv'],
            "rem_bank_code" => $request->settings['rem_bank_code'],
            "rem_agreement_number" => $request->settings['rem_agreement_number'],
            "rem_transfer_type" => $request->settings['rem_transfer_type'],
		]);
    }


    public function createCnabFile()
    {
        $total = Withdrawals::getTotalValueRequestedWithdrawals();

        //Se o total eh maior que 0, entao gera um arquivo de remessa
        if($total > 0) {

            //Pega as configuracoes do banco
            $getSettings = Withdrawals::getWithdrawalsSettings();
            $settings = array();
            foreach($getSettings as $eachSetting){
                $settings[$eachSetting->key] = $eachSetting->value;
            }
            
            //Pega as informacoes dos favorecidos
            $beneficiariesData = Withdrawals::getUserProviderDataToCreateCnab();

            /**
             * documentos uteis:
             * pag 24 - https://www.bb.com.br/docs/pub/emp/empl/dwn/000Completo.pdf
             * http://suporte.quarta.com.br/LayOuts/Bancos/18-Santander%20(febraban).pdf
             */
            //Registro 0
            $arquivo = new Remessa("104",'cnab240_transf',array(
                'tipo_inscricao'        => $settings['rem_cpf_or_cnpj'] == "cpf" ? 1 : 2, // 1 para cpf, 2 cnpj 
                'numero_inscricao'      => $settings['rem_document'], // seu cpf ou cnpj completo
                'convenio_caixa'        => $settings['rem_agreement_number'], // informado pelo banco, ate 6 digitos
                'param_transmissao'     => '12', // ate 2 digitos, fornecido pela caixa
                'amb_cliente'           => "T", // T teste e P producao
                'agencia'               => $settings['rem_agency'], // sua agencia (pagador), sem o digito verificador 
                'agencia_dv'            => $settings['rem_agency_dv'], // somente o digito verificador da agencia 
                'conta'                 => $settings['rem_account'], // numero da sua conta
                'conta_dv'              => $settings['rem_account_dv'], // digito da conta
                'nome_empresa'          => $settings['rem_company_name'], // seu nome de empresa max 30
                'numero_sequencial_arquivo' => 1, // sequencial do arquivo um numero novo para cada arquivo gerado
            ));

            //Registro 1
            $lote  = $arquivo->addLote(array(   //HEADER DO LOTE
                'tipo_servico_transf'=> '98', // '98' = Pagamentos Diversos - tem a lista na pagina 39, G025 http://www.caixa.gov.br/Downloads/pagamentos-de-salarios-fornecedores-e-auto-pagamento/Leiaute_CNAB_240_Pagamentos.pdf
                'forma_lancamento' => $settings['rem_transfer_type'] == "doc" ? '03' : "41", // 03 DOC. e 41 TED. lista completa na pag 39, G029,  http://www.caixa.gov.br/Downloads/pagamentos-de-salarios-fornecedores-e-auto-pagamento/Leiaute_CNAB_240_Pagamentos.pdf
                'convenio_caixa'=> $settings['rem_agreement_number'],    // informado pelo banco, convenio caixa (ate 6 digitos)
                'tipo_compromisso'=> '11',     //01 Pagamento a Fornecedor - 02 Pagamento de Salarios - 03 Autopagamento - 06 Salario Ampliacao de Base - 11 Debito em Conta			
                'codigo_compromisso'=> '1234', // informado pelo banco. 4 Digitos
                'param_transmissao' => '12', // informado pelo banco, ate 2 digitos,
                'logradouro' => 'Rua dos Goitacazes',
                'numero_endereco' => 375,
                'cidade' => 'Belo Horizonte',
                'cep' => 30190,
                'complemento_cep' =>'050',
                'estado' => 'MG'
            )); 

            foreach($beneficiariesData as $data) {
                $lote->inserirTransferencia(array(

                    //Segmento A
                    'codigo_camera'     => $settings['rem_transfer_type'] == "doc" ? '700' : '018', // 018 TED - 700 DOC/OP - 000 Credito em Conta - 888 Boleted/ISPB 
                    'cod_banco_fav'     => $data->bank_code,
                    'agen_cta_favor'    => $data->agency,
                    'dig_ver_agen'      => $data->agency_digit,
                    'conta_corrente_fav'=> $data->account,
                    'dig_conta_fav'     => $data->account_digit,
                    'nome_fav'          => $data->favoredName,
                    'num_atribuido_empresa' => $data->id, //numero para identificar a transferencia. Retornado conforme recebido. Deve ser maior que 0.
                    'tipo_conta_ted'    => '1', //preencher apenas se a transferencia for do tipo TED. 1 – Conta corrente; 2 – Poupança;
                    'cod_finalidade_doc'=> '01', //preencher apenas se a transferencia for do tipo. Valores na tabela P005, pag 39 - http://www.caixa.gov.br/Downloads/pagamentos-de-salarios-fornecedores-e-auto-pagamento/Leiaute_CNAB_240_Pagamentos.pdf
                    'data_pagamento'    => date("Y/m/d"),
                    'valor_pagamento'   => $data->totalValue,
                    
                    //Segmento B
                    'logradouro'        => 'Nome da rua',
                    'num_do_local'      => 1,
                    'bairro'            => 'Bairro',
                    'cidade'            => 'Belo Horizonte',
                    'cep'               => 30190,
                    'complemento_cep'   => '050',
                    'sigla_estado'      => 'MG',
                    'tipo_inscricao'    => $data->person_type != 'individual' ? 2 : 1, //campo fixo, escreva '1' se for pessoa fisica, 2 se for pessoa juridica
                    'numero_inscricao'  => $data->document,//cpf ou ncpj do favorecido
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
            
            //Salva o arquivo no banco
            $modelCnabFile = new CnabFiles;
            $modelCnabFile->rem_total = $total;
            $modelCnabFile->rem_url_file = $s3_url;
            $modelCnabFile->date_rem = date("Y-m-d H:i:s");
            $modelCnabFile->save();

            //Se chegou ate aqui, troca o status do saque de 'solicitado' para 'aguardando retorno'
            //Isso para todos os benificiarios que foram gerados no arquivo de remessa
            foreach($beneficiariesData as $data) {
                Withdrawals::updateStatusAndFileAssociated($data->id, $modelCnabFile->id);
            }


            $responseArray = array('success' => true);
		    $responseCode = 200;
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




    /**
     * View the withdrawals report
     * 
     * @return View
     */
    public function getWithdrawalsReportWeb() {
       
        $type = \Request::segment(1);
        \Log::debug($type);
        switch ($type) {
            case Finance::TYPE_USER:
                $id = \Auth::guard("clients")->user()->id;
                $holder = User::find($id);
                $notfound = 'user_panel.userLogin';
                $enviroment = 'user';
                break;
            case Finance::TYPE_PROVIDER:
                $id = \Auth::guard("providers")->user()->id;
                $holder = Provider::find($id);
                $notfound = 'provider_panel.login';
                $enviroment = 'provider';
                break;
            case Finance::TYPE_CORP:
                $admin_id = \Auth::guard("web")->user()->id;
                $holder = AdminInstitution::getUserByAdminId($admin_id);
                $id = $holder->id;
                $notfound = 'corp.login';
                $enviroment = 'corp';
                break;
            case Finance::TYPE_ADMIN:
                $admin_id = \Auth::guard("web")->user()->id;
                $holder = null;
                $id = null;
                $notfound = 'corp.login';
                $enviroment = 'admin';
                break;
        }
        if (($holder && $holder->ledger) || $type == Finance::TYPE_ADMIN) {
            
            if(isset($holder->ledger->id) && $holder->ledger->id) {
                $ledger_id = $holder->ledger->id;
            } else {
                $ledger_id = null;
            }
            $balance = Withdrawals::getWithdrawalsSummaryWeb($ledger_id, $enviroment);
            if (Input::get('submit') && Input::get('submit') == 'Download_Report') {
                return $this->downloadFinancialReport($type, $holder, $balance);
            } else {
                $types = Finance::TYPES; //Prepares Finance types array to be used on vue component
                $banks = Bank::orderBy('code', 'asc')->get(); //List of banks
                $account_types = LedgerBankAccount::getAccountTypes(); //List of AccountTypes

                $withDrawSettings = array(
                    'with_draw_enabled' => Settings::getWithDrawEnabled(),
                    'with_draw_max_limit' => Settings::getWithDrawMaxLimit(),
                    'with_draw_min_limit' => Settings::getWithDrawMinLimit(),
                    'with_draw_tax' => Settings::getWithDrawTax()
                );

                return View::make('withdrawals::withdrawals_report')
                                ->with([
                                    'id' => $id,
                                    'enviroment' => $enviroment,
                                    'user_provider_type'  => $enviroment,
                                    'holder' => isset($holder->first_name) ? $holder->first_name . ' ' . $holder->last_name : '',
                                    'ledger' => $holder,
                                    'balance' => $balance,
                                    'types' => $types,
                                    'bankaccounts' => isset($holder->ledger->bankAccounts) ? $holder->ledger->bankAccounts : '',
                                    'banks' => $banks,
                                    'account_types' => $account_types,
                                    'withdrawsettings' => $withDrawSettings
                ]);
            }
        } else { //if($holder && $holder->ledger)
            return View::make($notfound)->with('title', trans('adminController.page_not_found'))->with('page', trans('adminController.page_not_found'));
        }
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

}