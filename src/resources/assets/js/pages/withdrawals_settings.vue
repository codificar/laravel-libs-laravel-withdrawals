<script>
import axios from "axios";
import moment from "moment";
export default {
  props: [
    "Settings",
  ],
  data() {
    return {
      settings: {},
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
  </div>
</template>