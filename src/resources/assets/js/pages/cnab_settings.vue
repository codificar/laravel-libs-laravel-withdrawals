<script>
import axios from "axios";
import moment from "moment";
export default {
  props: [
    "Settings",
	"CnabFiles",
	"TotalRequested",
	"TotalAwaitingReturn",
	"TotalError",
	"currencySymbol",
	"WithdrawalsReport"
  ],
  data() {
    return {
      settings: {},
	  cnab_files: {},
	  retFile: '',
	  current_ret_file_id: null,
	  withdrawals_report: "",
	  checkedWithdraws:[]
    };
  },
  methods: {

	showErrorMsg(errors) {
		this.$swal({
			title: this.trans("withdrawals.error"),
			html:
			'<label class="alert alert-danger alert-dismissable text-left">' +
			errors +
			"</label>",
			type: "error"
		}).then(result => {});
	},

	/**
	* This method call a api and realod the page after success response
	*/
	postApiAndRealod(route, params, successMsg) {
		console.log('params:: ', successMsg);
		new Promise((resolve, reject) => {
            axios
              .post(route, params)
              .then(response => {
                console.log(response);
                if (response.data.success) {
					this.$swal({
						title: successMsg,
						type: "success"
					}).then(result => {
						location.reload();
					});
                } else {
                  this.$swal({
                    title: this.trans("withdrawals.error"),
                    html:
                      '<label class="alert alert-danger alert-dismissable text-left">' +
                      response.data.errors +
                      "</label>",
                    type: "error"
                  }).then(result => {});
                }
              })
              .catch(error => {
                console.log(error);
                reject(error);
                return false;
              });
          });
	},

    setWithdrawalsSettings() {
      this.$swal({
        title: this.trans("withdrawals.save_question"),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.trans("withdrawals.yes"),
        cancelButtonText: this.trans("withdrawals.no")
      }).then(result => {
        if (result.value) {

			//Call api
			this.postApiAndRealod(
				"/admin/libs/cnab_settings/save", 
				{ settings: this.settings },
				this.trans("withdrawals.success_set_withdrawals")
			);

        }
      });
    },
	deleteRemFile(rem_id) {
		this.$swal({
			title: this.trans('withdrawals.delete'),
			text: this.trans('withdrawals.delete_msg'),
			type: 'question',
			showCancelButton: true,
			confirmButtonText: this.trans('withdrawals.yes'),
			cancelButtonText: this.trans('withdrawals.no')
			}).then((result) => {
			if (result.value) {

				//Chama a api para deletar o arquivo remessa delete_cnab_file
				this.postApiAndRealod(
					"/admin/libs/cnab_settings/delete_cnab_file", 
					{ cnab_id: rem_id },
					this.trans("withdrawals.file_was_deleted")
				);
			
			}
		});
	},
	createNewRemFile(selectWithdralsToCnabFile = false) {

		if (selectWithdralsToCnabFile == true) {
			this.postApiAndRealod(
				"/admin/libs/cnab_settings/create_cnab_file", 
				{ settings: this.settings, selectedWithdrawals: this.checkedWithdraws },
				this.trans("withdrawals.success_created_cnab")
			);
		} else {
			this.postApiAndRealod(
				"/admin/libs/cnab_settings/create_cnab_file", 
				{ settings: this.settings },
				this.trans("withdrawals.success_created_cnab")
			);
		}	
	},

	reloadPageWithMessage(message) {
		console.log("caiu no reload");
		this.$swal({
				title: message,
				type: 'success'
			}).then((result) => { location.reload(); } );
	},

	showModalCreateCnab() {
		//open modal
		$("#modalCreateCnab").modal("show");
	},

	handleFileUpload: function(id) {
		console.log(this.$refs.myFiles.files);
		this.retFile = this.$refs.myFiles.files[0];
	},

	sendRetFile(currentId) {

		//when click in modal, reset the image
		this.retFile = '';
		$("#modalFormRet").trigger("reset");

		//set the current modal associed id
		this.current_ret_file_id = currentId;

		//open modal
		$("#modalSendRetFile").modal("show");
	},

	confirmSendRetFile(id) {

		let formData = new FormData();
		formData.append('cnab_id', this.current_ret_file_id);
		formData.append('cnab_ret_file', this.retFile);

		axios.post( '/admin/libs/cnab_settings/send_ret_file', formData, {
			headers: {
				'Content-Type': 'multipart/form-data'
			}
		}).then(response => {
			console.log('dados:', response);
			$('#modalSendRetFile').modal('hide');

			if (response.data.success) {
				this.reloadPageWithMessage(this.trans("withdrawals.ret_file_uploaded"));
			} else {
				this.showErrorMsg(response.data.errors);
			}
		})
		.catch(error => {
			$('#modalSendRetFile').modal('hide');
			console.log(error);
			this.showErrorMsg(this.trans("withdrawals.error"));
			return false;
		});

	},

	selectWithdralsToCnabFile() {
		//open modal
		$("#modalSelectWithdrawals").modal("show");
	},

	filterWithdrawals(page = 1) {
		var status = "";
		var receipt = "";
		

		if(this.Enviroment == "admin") {
			var route = "/admin/libs/withdrawals"
		} else if(this.Enviroment == "provider") {
			var route = "/provider/libs/withdrawals"
		}

		if(this.status == "requested" || this.status == "awaiting_return" || this.status == "concluded" || this.status == "error" || this.status == "rejected") {
			status = this.status;
		}
		if(this.receipt == 1 || this.receipt == "2" || this.receipt == 2 || this.receipt == "2") {
			receipt = parseInt(this.receipt)
		}

		if(!parseInt(page)) {
			page = 1;
		}

		window.location.assign(
			route + '?' +
			'status=' + status + "&" +
			'receipt=' + receipt + "&" +
			'page=' + page

		);
	}
	
  },
  created() {
	var aux;
	var settingsJson = {};
	this.Settings ? (aux = JSON.parse(this.Settings)) : null;
	aux.forEach(function myFunction(item, index) {
		settingsJson[item.key] = item.value
	});
	this.settings = settingsJson;
	this.cnab_files = JSON.parse(this.CnabFiles );
	this.withdrawals_report = JSON.parse(this.WithdrawalsReport);
  }
};
</script>
<template>
  <div>
    <!-- Row -->
    <div class="tab-content">
      <div class="col-lg-12">
        <div class="card card-outline-info">
          <div class="card-header">
            <h4 class="m-b-0 text-white">{{trans('withdrawals.rem_settings')}}</h4>
          </div>
          <div class="card-block">
            <div class="row">
              <form data-toggle="validator" class="col-lg-12" v-on:submit.prevent="setWithdrawalsSettings()">
				
				<div class="row">
					<!-- Banco -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_bank') }}</label>
							<select
								v-model="settings.rem_bank_code"
								name="rem_bank_code"
								class="select form-control"
							>
							<option
								v-for="method in [
									{
										'value': '104', 
										'name': 'Caixa'
									},
									{
										'value': '01', 
										'name': 'Banco do Brasil (em desenvolvimento)'
									}
								]"
								v-bind:value="method.value"
								v-bind:key="method.value"
							>{{ method.name }}</option>
							</select>
						</div>
					</div>

					<!-- Codigo do convenio do banco -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_agreement_number') }}</label>
							<input
								type="text"
								class="form-control"
								name="rem_agreement_number"
								id="rem_agreement_number"
								required
								v-mask="['######']"
								v-model="settings.rem_agreement_number"
							/>
						</div>
					</div>

					<!-- Ambiente (teste ou producao T OU P) -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_environment') }}</label>
							<select
								v-model="settings.rem_environment"
								name="rem_environment"
								class="select form-control"
							>
							<option
								v-for="method in [
									{
										'value': 'T', 
										'name': 'Teste'
									},
									{
										'value': 'P', 
										'name': 'Produção'
									}
								]"
								v-bind:value="method.value"
								v-bind:key="method.value"
							>{{ method.name }}</option>
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					<!-- Nome da empresa -->
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_company_name') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_company_name"
							id="rem_company_name"
							maxlength="30"
							required
							v-model="settings.rem_company_name"
							/>
						</div>
					</div>

					<!-- TED ou DOC -->
					<div class="col-md-3 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_transfer_type') }}</label>
							<select
								v-model="settings.rem_transfer_type"
								name="rem_transfer_type"
								class="select form-control"
							>
							<option
								v-for="method in [
									{
										'value': 'ted', 
										'name': 'TED'
									},
									{
										'value': 'doc', 
										'name': 'DOC'
									}
								]"
								v-bind:value="method.value"
								v-bind:key="method.value"
							>{{ method.name }}</option>
							</select>
						</div>
					</div>


					<!-- TIPO: CPF ou CNPJ -->
					 <div class="col-md-3 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_cpf_or_cnpj') }}</label>
							<select
								v-model="settings.rem_cpf_or_cnpj"
								name="rem_cpf_or_cnpj"
								class="select form-control"
							>
							<option
								v-for="method in [
									{
										'value': 'cpf', 
										'name': 'CPF'
									},
									{
										'value': 'cnpj', 
										'name': 'CNPJ'
									}
								]"
								v-bind:value="method.value"
								v-bind:key="method.value"
							>{{ method.name }}</option>
							</select>
						</div>
					</div>
				</div>

				<div class="row">
					
					<!-- Valor do CPF ou CNPJ -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_document') }}</label>
							<the-mask
							type="text"
							class="form-control"
							name="rem_document"
							id="rem_document"
							:mask="['###.###.###-##', '##.###.###/####-##']"
							required
							v-model="settings.rem_document"
							/>
						</div>
					</div>

					<!-- Agencia -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_agency') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_agency"
							id="rem_agency"
							v-mask="['#####']"
							required
							v-model="settings.rem_agency"
							/>
						</div>
					</div>

					<!-- Digito da agencia -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_agency_dv') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_agency_dv"
							id="rem_agency_dv"
							v-mask="['#']"
							required
							v-model="settings.rem_agency_dv"
							/>
						</div>
					</div>
				</div>

				<div class="row">
					<!-- operacao -->
					<div class="col-md-3 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_operation') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_operation"
							id="rem_operation"
							v-mask="['####']"
							required
							v-model="settings.rem_operation"
							/>
						</div>
					</div>

					<!-- Conta -->
					<div class="col-md-2 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_account') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_account"
							id="rem_account"
							v-mask="['############']"
							required
							v-model="settings.rem_account"
							/>
						</div>
					</div>

					<!-- Digito da conta -->
					<div class="col-md-2 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_account_dv') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_account_dv"
							id="rem_account_dv"
							v-mask="['#']"
							required
							v-model="settings.rem_account_dv"
							/>
						</div>
					</div>

					<!-- Endereco (max 30) -->
					<div class="col-md-5 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_address') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_address"
							id="rem_address"
							maxlength="30"
							v-model="settings.rem_address"
							/>
						</div>
					</div>
				</div>


				<div class="row">
					<!-- numero do endereco  -->
					<div class="col-md-2 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_address_number') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_address_number"
							id="rem_address_number"
							v-mask="['#####']"
							required
							v-model="settings.rem_address_number"
							/>
						</div>
					</div>

					<!-- Cidade -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_city') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_city"
							id="rem_city"
							maxlength="20"
							required
							v-model="settings.rem_city"
							/>
						</div>
					</div>
				
					<!-- Cep -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_cep') }}</label>
							<the-mask
							type="text"
							class="form-control"
							name="rem_cep"
							id="rem_cep"
							:mask="['#####-###']"
							required
							v-model="settings.rem_cep"
							/>
						</div>
					</div>

					<!-- sigla estado -->
					<div class="col-md-2 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_state') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_state"
							id="rem_state"
							maxlength="2"
							required
							v-model="settings.rem_state"
							/>
						</div>
					</div>

				</div>

				<div class="row">
					<!-- Tipo de compromisso  -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_type_compromise') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_type_compromise"
							id="rem_type_compromise"
							v-mask="['##']"
							required
							v-model="settings.rem_type_compromise"
							/>
						</div>
					</div>
					
					<!-- Codigo do compromisso  -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_code_compromise') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_code_compromise"
							id="rem_code_compromise"
							v-mask="['####']"
							required
							v-model="settings.rem_code_compromise"
							/>
						</div>
					</div>

					<!-- Parametro de transmissao  -->
					<div class="col-md-4 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_param_transmission') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_param_transmission"
							id="rem_param_transmission"
							v-mask="['##']"
							required
							v-model="settings.rem_param_transmission"
							/>
						</div>
					</div>
				

				</div>

				<button type="submmit" class="btn btn-success pull-right">{{trans('withdrawals.save')}}</button>
              
			  </form>
            </div>
          </div>
        </div>
      </div>
    </div>


	<!-- Row -->
    <div class="tab-content">
      <div class="col-lg-12">
        <div class="card card-outline-info">
          <div class="card-header">
            <h4 class="m-b-0 text-white">{{trans('withdrawals.generate_file')}}</h4>
          </div>
            <div class="row">
				<div class="col-lg-12">
					<div class="card">
						<div class="card-block">
							<div class="card-block">

								<table class="table table-bordered">
									<tr>
										<th>Valor a pagar</th>
										<th>Aguardando retorno</th>
										<th>Valor com erros</th>
									</tr>
									<tr>
										<td class="text-success">{{ currency_format(this.TotalRequested, this.currencySymbol) }}</td>
										<td>{{ currency_format(this.TotalAwaitingReturn, this.currencySymbol) }}</td>
										<td>{{ currency_format(this.TotalError, this.currencySymbol) }}</td>
										
									</tr>
								</table>

								<button class="btn btn-success" v-on:click="showModalCreateCnab()">{{ trans('withdrawals.create_file') }}</button>

								<table style="margin-top: 15px !important" class="table table-bordered">
									<tr>
										<th>{{ trans("withdrawals.id") }}</th>
										<th>{{ trans("withdrawals.status") }}</th>
										<th>{{ trans("withdrawals.rem_file") }}</th>
										<th>{{ trans("withdrawals.rem_date") }}</th>
										<th>{{ trans("withdrawals.ret_file") }}</th>
										<th>{{ trans("withdrawals.ret_date") }}</th>
										<th>{{ trans("withdrawals.total_estimated") }}</th>
										<th>{{ trans("withdrawals.total_paid") }}</th>
										<th>{{ trans("withdrawals.action") }}</th>
									</tr>
									<tr v-for="row in cnab_files" v-bind:key="row.id">
										<td>{{ row.id }}</td>
										<td>
											<a v-if="row.ret_url_file"  >{{ trans("withdrawals.concluded") }}</a>
											<a v-else class="text-danger">{{ trans("withdrawals.awaiting_return") }}</a>
										</td>
										<td><a :href="row.rem_url_file" download>Baixar</a></td>
										<td>{{ row.date_rem }}</td>
										<td>
											<a v-if="row.ret_url_file" :href="row.ret_url_file" download>Baixar</a>
											<a v-else v-on:click="sendRetFile(row.id)" style="cursor: pointer;" class="text-primary">Enviar</a>
										</td>
										<td>{{ row.date_ret }}</td>
										<td>{{ currency_format(row.rem_total, currencySymbol) }}</td>
										<td>{{ currency_format(row.ret_total, currencySymbol) }}</td>
										
										<td>
											<!-- So pode excluir um arquivo de remessa se nao tiver um retorno atrelado -->
											<a v-if="row.ret_url_file">N/A</a>
											<div v-else-if="!row.ret_url_file" class="dropdown">
												<button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
													{{trans('withdrawals.action') }}
													<span class="caret"></span>
												</button>

												<div class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropdownMenu1">
													<a 
														class="dropdown-item" 
														style="cursor: pointer;" 
														v-on:click="deleteRemFile(row.id)"
													>
														{{ trans('withdrawals.delete') }}
													</a>
												</div>
											</div>
										</td>

									</tr>
								</table>


								 <!-- modal enviar arquivo remessa-->
								<div class="modal" :id="'modalCreateCnab'" tabindex="-1" role="dialog" aria-labelledby="modalCreateCnabLabel">
									<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h4 class="modal-title">Criar arquivo de remessa</h4>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											</div>
											<div class="modal-body">
												<form id="modalForm">
													<label>Observações</label>
													<label>1. Caso seja gerado um arquivo de remessa, mas você não enviar ao banco no mesmo dia, o banco irá recusar. Nesse caso, é possível deletar o arquivo de remessa e gerar um novo.</label>
													<label>2. Ao deletar um arquivo de remessa, todos os saques com status "Aguardando remessa" atrelados ao arquivo, voltarão a ser "Solicitado"</label>
													<label>3. Apenas arquivos remessa que não possui retorno atrelados podem ser deletados.</label>
													<label>4. Se nas configurações acima estiver configurado como ambiente "Teste", os saques com status "Solicitado" não serão afetados.</label>
													
													<button type="button" v-on:click="createNewRemFile()" class="btn btn-success right">Gerar arquivo remessa</button>									
												</form><br>
												<button type="button" class="btn btn-success" v-on:click="selectWithdralsToCnabFile()">Selecionar saques</button>
											</div>
											
										</div>
									</div>
								</div>
								<!-- /.modal -->


								<!-- modal enviar arquivo retorno-->
								<div class="modal" :id="'modalSendRetFile'" tabindex="-1" role="dialog" aria-labelledby="modalmodalSendRetFileLabel">
									<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h4 class="modal-title">Arquivo de retorno</h4>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											</div>
											<div class="modal-body">
												<form id="modalFormRet">
													<label for="confirm_withdraw_picture">Envie o arquivo de retorno</label>
													<input 
														type="file" 
														:id="'file'" 
														:ref="'myFiles'" 
														class="form-control-file" 
														@change="handleFileUpload"
													>

													
													<br>
													
													<button type="button" v-on:click="confirmSendRetFile(this.current_ret_file_id)" class="btn btn-success right">Enviar</button>
													
												</form>
											</div>
											
										</div>
									</div>
								</div>
								<!-- /.modal -->

							</div>
						</div>
					</div>
				</div>
            </div>
        </div>
      </div>
    </div>

	<div class="modal" id="modalSelectWithdrawals" tabindex="-1" role="dialog" aria-labelledby="modalSelectWithdrawals">
		<div class="col-lg-12" v-if="!isEmpty(withdrawals_report.data)">
			<div class="card">
				<div class="card-block">
					<div class="card-block">
						<table class="table table-bordered">
							<tr>
                                <th>{{ trans("withdrawals.id") }}</th>

                                <th>{{ trans("withdrawals.name") }}</th>
                                <th>{{ trans("withdrawals.email") }}</th>
                                <th>{{ trans("withdrawals.bank") }}</th>
                                <th>{{ trans("withdrawals.agency") }}</th>
                                <th>{{ trans("withdrawals.account") }}</th>
                                <th>{{ trans("withdrawals.holder_document") }}</th>
								<th>{{ trans("withdrawals.finance_date") }}</th>
								<th>{{ trans("withdrawals.finance_time") }}</th>
								<th>{{ trans("withdrawals.finance_value") }}</th>
								<th>Selecionar</th>
							</tr>
							<tr v-for="entry in withdrawals_report.data" v-bind:key="entry.id" total=0>
								<td>{{ entry.id }}</td>
                                <td>{{ entry.name }}</td>
                                <td>{{ entry.email }}</td>
                                <td>{{ entry.bank }}</td>
                                <td>{{ entry.agency + "-" + entry.agency_digit }}</td>
                                <td>{{ entry.account + "-" + entry.account_digit }}</td>
                                <td>{{ entry.document }}</td>
                                <td><p v-if="((new Date(entry.date)).toLocaleString().split(' ')[0]) != '31/12/1969'">{{ (new Date(entry.date)).toLocaleString().split(" ")[0] }}</p></td>
								<td>{{ (new Date(entry.date)).toTimeString().split(":")[0] }}:{{ (new Date(entry.date)).toTimeString().split(":")[1] }}</td>
								<td><p class="text-success">{{ currency_format(entry.value, currencySymbol) }}</p></td>		
								<td><input :id="entry.id" :value="entry.id" name="entry" type="checkbox" v-model="checkedWithdraws" /></td>				
							</tr>
						</table>		
					</div>
					<pagination :data="withdrawals_report" @pagination-change-page="filterWithdrawals"></pagination>
					<button type="button" v-on:click="createNewRemFile(true)" class="btn btn-success right">Gerar arquivo remessa</button>
				</div>
			</div>
		</div>
	</div>
  </div>
</template>