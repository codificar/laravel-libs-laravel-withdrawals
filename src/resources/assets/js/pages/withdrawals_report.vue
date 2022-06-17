<script>

import axios from "axios";

import ModalRequestWithdraw from "./modal_request_withdraw.vue";
import ModalNewBankAccount from "./modal_new_bank_account.vue";

export default {
	props: [
		"Id",
		"Enviroment",
		"WithdrawalsSummaryRoute",
		"WithDrawRequestRoute",
		"CreateBankAccountRoute",
		"FinancialEntryRoute",
		"Ledger",
		"WithdrawalsReport",
		"CurrentBalance",
		"BankAccounts",
		"BankList",
		"AccountTypes",
		"WithDrawSettings",
		"currencySymbol"
	],
	data() {
		return {
			id: "",
			types: "", // Gets financial types to make the select field and use in comparations
			ledger: "",
			type_entry: "0", //Select field default option
			withdrawals_report: "",
			current_balance: 0,
			banks: [],
			account_types: [],
			bank_account_id: 0,
			bank_accounts: [],
			with_draw_settings: [],
			url: window.location.href,
            status: this.getUrlParams("status"),
            receipt: this.getUrlParams("receipt"),
			page: this.getUrlParams("page"),
			confirm_withdraw_date: '',
			fileWithdraw: '',
			current_modal_id: null,
			error_msg: ""
		};
	},
	components: {
		modalrequestwithdraw: ModalRequestWithdraw,
		modalnewbankaccount: ModalNewBankAccount
	},
	methods: {

		downloadWithdrawals() {
				var status = "";
				var receipt = "";

				if(this.Enviroment == "admin") {
					var route = "/admin/libs/withdrawals/download";
				} else if(this.Enviroment == "provider") {
					var route = "/provider/libs/withdrawals/download";
				}
				

				if(this.status == "requested" || this.status == "awaiting_return" || this.status == "concluded" || this.status == "error" || this.status == "rejected") {
					status = this.status;
				}
				if(this.receipt == 1 || this.receipt == "2" || this.receipt == 2 || this.receipt == "2") {
					receipt = parseInt(this.receipt)
				}

				window.location.assign(
					route + '?' +
					'status=' + status + "&" +
					'receipt=' + receipt
				);
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
		},

		showModalRequestWithdraw() {
			if (this.bank_account_id == 0 && this.bank_accounts.length > 0)
				this.bank_account_id = this.bank_accounts[0].id;
			$("#modal-request-withdraw").modal("show");
		},
		showModalNewBankAccount() {
			$("#modal-request-withdraw").modal("hide");
			$("#modal-new-bank-account").modal("show");
		},

		ModalNewBankClosed(newBankAccount) {
			if (newBankAccount != "" && newBankAccount.id != "") {
				this.bank_accounts.push(newBankAccount);
				this.bank_account_id = newBankAccount.id;
			}
			$("#modal-new-bank-account").modal("hide");
			$("#modal-request-withdraw").modal("show");
		},
		reloadPageWithMessage(message) {
			console.log("caiu no reload");
			this.$swal({
					title: message,
					type: 'success'
				}).then((result) => { location.reload(); } );
		},
		transactionSuccess() {
			this.reloadPageWithMessage(this.trans("withdrawals.transaction_add_success"));
		},
		

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

		showModalConfirmWithdraw(currentId) {

			//when click in modal, reset the image
			this.fileWithdraw = '';
			$("#modalForm").trigger("reset");

			//set the current modal associed id
			this.current_modal_id = currentId;

			console.log('inferno',this.current_modal_id);

			//open modal
			$("#modalConfirmWithdraw").modal("show");
		},

		showModalErrorMsg(msg) {

			this.error_msg = msg;
			//open modal
			$("#modalErrorMsg").modal("show");
		},


		handleFileUpload: function(id) {
			console.log(this.$refs.myFiles.files);
			this.fileWithdraw = this.$refs.myFiles.files[0];
		},
		confirmWithdraw() {
			console.log("id: ", this.current_modal_id);
			console.log("img: ", this.fileWithdraw);
			console.log("date: ", this.confirm_withdraw_date);

			let formData = new FormData();
			formData.append('withdraw_id', this.current_modal_id);
			formData.append('file_withdraw', this.fileWithdraw);
			formData.append('date', this.confirm_withdraw_date);

			axios.post( '/admin/libs/withdrawals/confirm_withdraw', formData, {
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			}).then(response => {
					console.log('dados:', response);
					$('#modalConfirmWithdraw').modal('hide');

					if (response.data.success) {
						this.reloadPageWithMessage(this.trans("withdrawals.success_confirm_withdrawals"));
					} else {
						this.showErrorMsg(response.data.errors);
					}
				})
				.catch(error => {
					$('#modalConfirmWithdraw').modal('hide');
					console.log(error);
					this.showErrorMsg(this.trans("withdrawals.error"));
					return false;
				});

		},
		getUrlParams(name){
			if(name=(new RegExp('[?&]'+encodeURIComponent(name)+'=([^&]*)')).exec(location.search))
				return decodeURIComponent(name[1]);
		},
		rejectWithdraw() {
			let formData = new FormData();
			formData.append('withdraw_id', this.current_modal_id);
			
			axios.post( '/admin/libs/withdrawals/reject_withdraw', formData, {
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			}).then(response => {
				console.log('dados:', response);
				$('#modalRejectWithdraw').modal('hide');

				if (response.data.success) {
					this.reloadPageWithMessage(this.trans("withdrawals.success_confirm_withdrawals"));
				} else {
					this.showErrorMsg(response.data.errors);
				}
			}).catch(error => {
				$('#modalRejectWithdraw').modal('hide');
				console.log(error);
				this.showErrorMsg(this.trans("withdrawals.error"));
				return false;
			});
		},
		showModalRejectWithdraw(currentId) {

			//when click in modal, reset the image
			this.fileWithdraw = '';
			$("#modalForm").trigger("reset");

			//set the current modal associed id
			this.current_modal_id = currentId;

			//open modal
			$("#modalRejectWithdraw").modal("show");
		},
	},	 

	mounted() {
		
	},
	
	created() {
		this.id = this.Id;
		this.ledger = JSON.parse(this.Ledger); //User/Provider Object
		this.withdrawals_report = JSON.parse(this.WithdrawalsReport);
		this.current_balance = JSON.parse(this.CurrentBalance)
		this.total = 0;
		this.bank_accounts = JSON.parse(this.BankAccounts); // User/provider BankAccounts
		this.banks = JSON.parse(this.BankList); // Banks list
		this.account_types = JSON.parse(this.AccountTypes); // Account types list
		this.with_draw_settings = JSON.parse(this.WithDrawSettings); // System withdraw configs
	}
};
</script>
<template>
	<div>

		<div v-if="Enviroment != 'admin'">
			<modalrequestwithdraw v-show="with_draw_settings.with_draw_enabled == true" v-on:newBankAccount="showModalNewBankAccount" 
					v-on:addWithDrawRequest="transactionSuccess" 
					:ledger="ledger" :bank-accounts="bank_accounts" :bank-account-id="bank_account_id" 
					:bank-list="banks" :with-draw-settings="with_draw_settings" :available-balance="current_balance"
					:with-draw-request-route="WithDrawRequestRoute"
					:currency-symbol="currencySymbol">
			</modalrequestwithdraw>
			<modalnewbankaccount v-on:newModalBankClosed="ModalNewBankClosed" :ledger="ledger" :bank-list="banks" :account-types="account_types"
					:create-bank-account-route="CreateBankAccountRoute">
			</modalnewbankaccount>
		</div>

		<div class="col-lg-12">
			<div class="card card-outline-info">
				<div class="card-header">
					<h4 class="m-b-0 text-white">{{ trans('withdrawals.withdrawals_report') }}</h4>
				</div>
				<div class="card-block">
					<div class="row">
						<div class="col-md-12">
							<div class="box box-warning">
								<div class="box-header">
									<h3 class="box-title">
									</h3>
								</div>
								<form id="filter-account-statement" method="get" v-bind:action="url ">
									<div class="box-body">
										
                                        <div class="row">
											
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <!--Select transaction type filter-->
                                                    <label for="giveName">{{trans('withdrawals.status') }}</label>
                                                    <select v-model="status" name="" class="select form-control">
														<option value="0" selected="selected"></option>
                                                        <option value="requested">{{trans('withdrawals.withdrawal_requested') }}</option>
														<option value="awaiting_return">{{trans('withdrawals.awaiting_return') }}</option>
														<option value="concluded">{{trans('withdrawals.concluded') }}</option>
														<option value="error">{{trans('withdrawals.error') }}</option>
														<option value="rejected">{{trans('withdrawals.rejected') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <!--Select transaction type filter-->
                                                    <label for="giveName">{{trans('withdrawals.receipt') }}</label>
                                                    <select v-model="receipt" name="" class="select form-control">
														<option value="0" selected="selected"></option>
                                                        <option value="1">{{trans('withdrawals.no') }}</option>
                                                        <option value="2">{{trans('withdrawals.yes') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>


                                        
										<!--/ end-row-->
										<div class="box-footer pull-right">
											<button v-on:click="downloadWithdrawals" class="btn btn-info right" type="button">
												<i class="mdi mdi-download"></i> {{trans('withdrawals.download_withdrawals')}}</button>
											<button v-on:click="filterWithdrawals" class="btn btn-success right" type="button" value="Filter_Data">
												<i class="fa fa-search"></i> {{ trans('withdrawals.search') }}</button>
										</div>

                                        <div v-if="Enviroment != 'admin'">
                                            <button v-show="with_draw_settings.with_draw_enabled == true" v-on:click="showModalRequestWithdraw" class="btn btn-info" type="button">
                                                <i class="fa fa-money"></i> {{ trans('withdrawals.request_withdraw') }}
                                            </button>
                                            <div>
                                            <hr>
                                                <i class="fa fa-money"></i>
                                                <strong>{{ trans('withdrawals.available_balance') }} : </strong>
                                                <span v-if="current_balance >= 0" class="text-success">{{ currency_format(current_balance, currencySymbol) }}</span>
                                                <span v-else class="text-danger">{{ currency_format(current_balance, currencySymbol) }}</span>
									        </div>
                                        </div>
                                    </div>
								</form>
								<!--/ box-body -->
							</div>
							<!--/ box-warning -->
						</div>
					</div>
				</div>
			</div>
		</div>
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
                                <th>{{ trans("withdrawals.status") }}</th>
                                <th>{{ trans("withdrawals.receipt") }}</th>

								<th>{{ trans("withdrawals.finance_date") }}</th>
								<th>{{ trans("withdrawals.finance_time") }}</th>
								<th>{{ trans("withdrawals.finance_value") }}</th>
								<th v-if="Enviroment == 'admin'">{{ trans("withdrawals.action") }}</th>
							</tr>
							<tr v-for="entry in withdrawals_report.data" v-bind:key="entry.id" total=0>
								<td>{{ entry.id }}</td>
                                <td>{{ entry.name }}</td>
                                <td v-if="entry.email">{{ entry.email }}</td>
                                <td v-if="entry.user_email">{{ entry.user_email }}</td>
                                <td v-if="entry.provider_email">{{ entry.provider_email }}</td>
                                <td>{{ entry.bank }}</td>
                                <td>{{ entry.agency + "-" + entry.agency_digit }}</td>
                                <td>{{ entry.account + "-" + entry.account_digit }}</td>
                                <td>{{ entry.document }}</td>
                                <td>
									<p v-if="entry.type == 'requested'">Solicitado</p>
									<p v-if="entry.type == 'awaiting_return'">Aguardando arq. retorno</p>
									<p v-if="entry.type == 'concluded'">Concluído</p>
									<p v-if="entry.type == 'rejected'">Rejeitado</p>
									<a 
										v-if="entry.type == 'error'"
										style="cursor: pointer; color: red" 
										v-on:click="showModalErrorMsg(entry.error_msg)"
									>
										Ver erro
									</a>
								</td>

                                <td><a v-if="entry.bank_receipt_url" target="_blank" :href="entry.bank_receipt_url">Visualizar</a></td>

                                <td><p v-if="((new Date(entry.date)).toLocaleString().split(' ')[0]) != '31/12/1969'">{{ (new Date(entry.date)).toLocaleString().split(" ")[0] }}</p></td>
								<td>{{ (new Date(entry.date)).toTimeString().split(":")[0] }}:{{ (new Date(entry.date)).toTimeString().split(":")[1] }}</td>
								<td><p class="text-success">{{ currency_format(entry.value, currencySymbol) }}</p></td>

								<td v-if="Enviroment == 'admin'">
									<div class="dropdown">
										<button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
											{{trans('withdrawals.action') }}
											<span class="caret"></span>
										</button>

										<div v-if="entry.type !== 'concluded' && entry.type !== 'rejected'" class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropdownMenu1">
										 	<a											 	 
											 	class="dropdown-item" 
												style="cursor: pointer;" 
												v-on:click="showModalConfirmWithdraw(entry.id)"
											>
												{{ trans('withdrawals.confirm') }}
											</a>
											<a											 	 
											 	class="dropdown-item" 
												style="cursor: pointer;" 
												v-on:click="showModalRejectWithdraw(entry.id)"
											>
												{{ trans('withdrawals.reject') }}
											</a>
										</div>
									</div>
								</td>
							</tr>
						</table>




						 <!-- modal -->
						<div v-if="Enviroment == 'admin'" class="modal" :id="'modalConfirmWithdraw'" tabindex="-1" role="dialog" aria-labelledby="modalmodalConfirmWithdrawLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">Dar baixa no saque</h4>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									</div>
									<div class="modal-body">
										<form id="modalForm">
											<label for="confirm_withdraw_picture">Envie o comprovante de transferência</label>
											<input 
												type="file" 
												:id="'file'" 
												:ref="'myFiles'" 
												class="form-control-file" 
												@change="handleFileUpload"
											>

											
											<br>
											
											<label for="withdraw_date">Data da transferência:</label>
											<input type="date" v-model="confirm_withdraw_date" id="withdraw_date" name="withdraw_date">
											
											<br>

											<button type="button" v-on:click="confirmWithdraw()" class="btn btn-success right">Enviar</button>
											
										</form>
									</div>
									
								</div>
							</div>
						</div>
						<!-- /.modal -->

						<!-- Modal rejeitar saque -->
						<div v-if="Enviroment == 'admin'" class="modal" :id="'modalRejectWithdraw'" tabindex="-1" role="dialog" aria-labelledby="modalmodalRejectWithdrawLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">Rejeitar saque</h4>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									</div>
									<div class="modal-body">
										<form id="modalForm">
											<label for="confirm_withdraw_picture">Você tem certeza que deseja rejeitar essa solicitação de saque?</label>
											<br>
											<button type="button" v-on:click="rejectWithdraw(this.current_modal_id)" class="btn btn-success right">Enviar</button>											
										</form>
									</div>
									
								</div>
							</div>
						</div>
						<!-- Fim do modal de rejeitar saque -->



						 <!-- modal error msg -->
						<div class="modal" :id="'modalErrorMsg'" tabindex="-1" role="dialog" aria-labelledby="modalmodalErrorMsgLabel">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">Motivo do Erro ao realizar saque</h4>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									</div>
									<div class="modal-body">
										<form id="modalFormErrorMsg">
											<label>{{this.error_msg}}</label>
											<br>
										</form>
									</div>
									
								</div>
							</div>
						</div>
						<!-- /.modal -->
					</div>
					<pagination :data="withdrawals_report" @pagination-change-page="filterWithdrawals"></pagination>
				</div>
			</div>
		</div>
	</div>
</template>