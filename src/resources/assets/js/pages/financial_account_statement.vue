<script>

export default {
	props: [
		"Id",
		"Enviroment",
		"WithdrawalsSummaryRoute",
		"WithDrawRequestRoute",
		"CreateBankAccountRoute",
		"FinancialEntryRoute",
		"FinanceTypes",
		"Holder",
		"Ledger",
		"Balance",
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
			holder: "", // Name of the user
			ledger: "",
			type_entry: "0", //Select field default option
			balance: "", // Object with all entries received from the controller : .detailed_balance .previous_balance and .current_balance
			current_balance: 0, //Balance of filtered entries
			banks: [],
			account_types: [],
			bank_account_id: 0,
			bank_accounts: [],
			with_draw_settings: [],
			url: window.location.href,
            status: 0,
            receipt: 0,
		};
	},
	components: {

	},
	methods: {
		//Calculates total balance from an array
		totalize(balance) {
			var totalizer = 0;
			for (var i = 0; i < balance.length; i++) {
				totalizer += parseFloat(balance[i].value);
			}
			return totalizer;
		},
		getWithdrawalsSummary(page = 1) {
			// TODO getWithdrawalsSummary
			console.log("aqui");
			
		},
		downloadFinancialSummary() {
			// TODO downloadFinancialSummary
			console.log("download");
		},
		
		ModalNewBankClosed(newBankAccount) {
			if (newBankAccount != "" && newBankAccount.id != "") {
				this.bank_accounts.push(newBankAccount);
				this.bank_account_id = newBankAccount.id;
			}
			$("#modal-new-bank-account").modal("hide");
			$("#modal-request-withdraw").modal("show");
		},
		reloadPage() {
			this.$swal({
					title: this.trans("finance.transaction_add_success"),
					type: 'success'
				}).then((result) => { location.reload(); } );
		},
	},
	mounted() {
		
	},
	
	created() {
		this.id = this.Id;
		this.types = JSON.parse(this.FinanceTypes); //Types of transactions
		this.holder = JSON.parse(this.Holder); //User/Provider Name
		this.ledger = JSON.parse(this.Ledger); //User/Provider Object
		this.balance = JSON.parse(this.Balance);
		this.total = 0;
		this.current_balance = this.totalize(this.balance.detailed_balance);
		this.bank_accounts = JSON.parse(this.BankAccounts); // User/provider BankAccounts
		this.banks = JSON.parse(this.BankList); // Banks list
		this.account_types = JSON.parse(this.AccountTypes); // Account types list
		this.with_draw_settings = JSON.parse(this.WithDrawSettings); // System withdraw configs
	}
};
</script>
<template>
	<div>

		<div class="col-lg-12">
			<div class="card card-outline-info">
				<div class="card-header">
					<h4 class="m-b-0 text-white">{{ trans('finance.withdrawals_report') }}</h4>
				</div>
				<div class="card-block">
					<div class="row">
						<div class="col-md-12">
							<div class="box box-warning">
								<div class="box-header">
									<h3 class="box-title">
										{{ holder }}
									</h3>
								</div>
								<form id="filter-account-statement" method="get" v-bind:action="url ">
									<div class="box-body">
										
                                        <div class="row">
											
											<div v-if="Enviroment == 'admin'" class="col-md-3">
                                                <div class="form-group">
													<div class="form-group">
														<label for="name" class=" control-label">{{ trans('finance.name') }}*</label>
														<input name="name" type="text" id="name" class="form-control input-lg" maxlenght="255" auto-focus="" :placeholder="trans('finance.name')">
													</div>
                                                </div>
                                            </div>
                                            <div v-if="Enviroment == 'admin'" class="col-md-3">
                                                <div class="form-group">
													<div class="form-group">
														<label for="bank" class=" control-label">{{ trans('finance.bank') }}*</label>
														<input name="bank" type="text" id="bank" class="form-control input-lg" maxlenght="255" auto-focus="" :placeholder="trans('finance.bank')">
													</div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <!--Select transaction type filter-->
                                                    <label for="giveName">{{trans('finance.status') }}</label>
                                                    <select v-model="status" name="" class="select form-control">
														<option value="0" selected="selected"></option>
                                                        <option value="1">{{trans('finance.withdrawal_requested') }}</option>
                                                        <option value="2">{{trans('finance.withdrawal_made') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <!--Select transaction type filter-->
                                                    <label for="giveName">{{trans('finance.receipt') }}</label>
                                                    <select v-model="receipt" name="" class="select form-control">
														<option value="0" selected="selected"></option>
                                                        <option value="1">{{trans('finance.no') }}</option>
                                                        <option value="2">{{trans('finance.yes') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>


                                        
										<!--/ end-row-->
										<div class="box-footer pull-right">
											<button v-on:click="downloadFinancialSummary" class="btn btn-info right" type="button">
												<i class="mdi mdi-download"></i> {{trans('finance.download_withdrawals')}}</button>
											<button v-on:click="getWithdrawalsSummary" class="btn btn-success right" type="button" value="Filter_Data">
												<i class="fa fa-search"></i> {{ trans('finance.search') }}</button>
										</div>

                                        <div v-if="Enviroment != 'admin'">
                                            <button v-show="with_draw_settings.with_draw_enabled == true" v-on:click="showModalRequestWithdraw" class="btn btn-info" type="button">
                                                <i class="fa fa-money"></i> {{ trans('finance.request_withdraw') }}
                                            </button>
                                            <div>
                                            <hr>
                                                <i class="fa fa-money"></i>
                                                <strong>{{ trans('finance.available_balance') }} : </strong>
                                                <span v-if="balance.total_balance >= 0" class="text-success">{{ currency_format(balance.total_balance, currencySymbol) }}</span>
                                                <span v-else class="text-danger">{{ currency_format(balance.total_balance, currencySymbol) }}</span>
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
		<div class="col-lg-12" v-if="!isEmpty(balance.detailed_balance)">
			<div class="card">
				<div class="card-block">
					</h3>
					<div class="card-block">
						<table class="table table-bordered">
							<tr>
                                <th>{{ trans("finance.id") }}</th>

                                <th>{{ trans("finance.name") }}</th>
                                <th>{{ trans("finance.email") }}</th>
                                <th>{{ trans("finance.bank") }}</th>
                                <th>{{ trans("finance.agency") }}</th>
                                <th>{{ trans("finance.account") }}</th>
                                <th>{{ trans("finance.cpf") }}</th>
                                <th>{{ trans("finance.status") }}</th>
                                <th>{{ trans("finance.receipt") }}</th>

								<th>{{ trans("finance.finance_date") }}</th>
								<th>{{ trans("finance.finance_time") }}</th>
								<th>{{ trans("finance.finance_value") }}</th>
							</tr>
							<tr v-for="entry in balance.withdrawals_list" v-bind:key="entry.id" total=0>
								<td>{{ entry.id }}</td>
                                <td>{{	
										(entry.provider_first_name || entry.user_first_name)
										+ " " + 
										(entry.provider_last_name  || entry.user_last_name)
								}}</td>
                                <td>{{ entry.provider_email || entry.user_email }}</td>
                                <td>{{ entry.bank }}</td>
                                <td>{{ entry.agency }}</td>
                                <td>{{ entry.account }}</td>
                                <td>{{ entry.document }}</td>
                                <td>{{ entry.status }}</td>
                                <td>{{ entry.receipt }}</td>
                                <td><p v-if="((new Date(entry.compensation_date)).toLocaleString().split(' ')[0]) != '31/12/1969'">{{ (new Date(entry.compensation_date)).toLocaleString().split(" ")[0] }}</p></td>
								<td>{{ (new Date(entry.compensation_date)).toTimeString().split(":")[0] }}:{{ (new Date(entry.compensation_date)).toTimeString().split(":")[1] }}</td>
								<td><p class="text-success">{{ currency_format(entry.value, currencySymbol) }}</p></td>
							</tr>
						</table>
					</div>
				</div>
				<pagination :data="balance.detailed_balance" @pagination-change-page="getWithdrawalsSummary"></pagination>
			</div>
		</div>
	</div>
</template>