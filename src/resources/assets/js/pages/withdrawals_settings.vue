<script>
import axios from "axios";
import moment from "moment";
export default {
  props: [
    "Settings",
	"CnabFiles"
  ],
  data() {
    return {
      settings: {},
	  cnab_files: {}
    };
  },
  methods: {

    setWithdrawalsSettings() {
      this.$swal({
        title: this.trans("withdrawals.save_question"),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.trans("withdrawals.yes"),
        cancelButtonText: this.trans("withdrawals.no")
      }).then(result => {
        if (result.value) {
          
          new Promise((resolve, reject) => {
            axios
              .post("/admin/libs/cnab_settings/save", {
                settings: this.settings
              })
              .then(response => {
                console.log(response);
                if (response.data.success) {
                  this.$swal({
                    title: this.trans("withdrawals.success_set_withdrawals"),
                    type: "success"
                  }).then(result => {
						location.reload();
                  });
                } else {
                  this.$swal({
                    title: this.trans("withdrawals.failed_set_withdrawals"),
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
        }
      });
    },
	createNewRemFile() {
		new Promise((resolve, reject) => {
            axios
              .post("/admin/libs/cnab_settings/create_cnab_file", {
                settings: this.settings
              })
              .then(response => {
                console.log(response);
                if (response.data.success) {
                  this.$swal({
                    title: this.trans("withdrawals.success_set_withdrawals"),
                    type: "success"
                  }).then(result => {
						location.reload();
                  });
                } else {
                  this.$swal({
                    title: this.trans("withdrawals.failed_set_withdrawals"),
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
	showModalCreateCnab() {
		//open modal
		$("#modalCreateCnab").modal("show");
	},
	
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
					<div class="col-md-6 col-sm-12">
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
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_agreement_number') }}</label>
							<input
								type="text"
								class="form-control"
								name="rem_agreement_number"
								id="rem_agreement_number"
								required
								v-model="settings.rem_agreement_number"
							/>
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
							required
							v-model="settings.rem_company_name"
							/>
						</div>
					</div>

					<!-- TED ou DOC -->
					<div class="col-md-6 col-sm-12">
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
				</div>

				<div class="row">
					<!-- TIPO: CPF ou CNPJ -->
					 <div class="col-md-6 col-sm-12">
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

					<!-- Valor do CPF ou CNPJ -->
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_document') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_document"
							id="rem_document"
							required
							v-model="settings.rem_document"
							/>
						</div>
					</div>
				</div>

				<div class="row">
					<!-- Agencia -->
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_agency') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_agency"
							id="rem_agency"
							required
							v-model="settings.rem_agency"
							/>
						</div>
					</div>

					<!-- Digito da agencia -->
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_agency_dv') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_agency_dv"
							id="rem_agency_dv"
							required
							v-model="settings.rem_agency_dv"
							/>
						</div>
					</div>
				</div>


				<div class="row">
					<!-- Conta -->
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_account') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_account"
							id="rem_account"
							required
							v-model="settings.rem_account"
							/>
						</div>
					</div>

					<!-- Digito da conta -->
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.rem_account_dv') }}</label>
							<input
							type="text"
							class="form-control"
							name="rem_account_dv"
							id="rem_account_dv"
							required
							v-model="settings.rem_account_dv"
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
							</h3>
							<div class="card-block">
								<div style="margin-bottom: 20px !important;">
									<label>Valor a pagar (novo arquivo):</label>
									<br>
									<label>Valor aguardando retorno:</label>
									<br>
									<label>Valor de saques não realizados (retornou erro):</label>
									<br>
									<button class="btn btn-success" v-on:click="showModalCreateCnab()">{{ trans('withdrawals.create_file') }}</button>
									<br>
								</div>

								<table class="table table-bordered">
									<tr>
										<th>{{ trans("withdrawals.id") }}</th>
										<th>{{ trans("withdrawals.status") }}</th>
										<th>{{ trans("withdrawals.rem_file") }}</th>
										<th>{{ trans("withdrawals.rem_date") }}</th>
										<th>{{ trans("withdrawals.ret_file") }}</th>
										<th>{{ trans("withdrawals.ret_date") }}</th>
										<th>{{ trans("withdrawals.total_estimated") }}</th>
										<th>{{ trans("withdrawals.total_paid") }}</th>
									</tr>
									<tr v-for="rem_file in cnab_files" v-bind:key="rem_file.id">
										<td>{{ rem_file.id }}</td>
										<td>{{ 'Aguardando retorno' }}</td>
										<td>{{ 'Visualizar' }}</td>
										<td>{{ rem_file.created_at }}</td>
										<td>{{ 'Visualizar' }}</td>
										<td>{{ rem_file.updated_at }}</td>
										<td>{{ rem_file.total_estimated }}</td>
										<td>{{ rem_file.total_paid }}</td>
										
									</tr>
								</table>


								 <!-- modal -->
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

  </div>
</template>