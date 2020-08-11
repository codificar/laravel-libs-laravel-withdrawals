<script>

import axios from 'axios'

export default {
  props: [
    "Ledger",
    "BankAccounts",
    "BankList",
    "BankAccountId",
    "AvailableBalance",
    "WithDrawSettings",
    "WithDrawRequestRoute",
    "currencySymbol"
  ],
  data() {
    return {
      ledger: "",
      with_draw_value: "",
      available_balance: 0,
      error_messages: [],
      banks: [],
      bank_accounts: [],
      bank_account: "",
      bank_account_id: ""
    };
  },
  methods: {
    requestWithdraw() {
      console.log("valor do ledger: ", this.ledger);
      // Make the post request do Add an Withdraw Request
        axios.post( this.WithDrawRequestRoute, {
              provider_id: this.ledger.id,
              withdraw_value: this.with_draw_value,
              bank_account_id: this.bank_account_id
          })
          .then(
            response => {
              console.log("resposta:", response);
              if (response.data.success == false) {
                this.error_messages = response.data.messages;
              } else {
                this.$emit("addWithDrawRequest");
                //Emit action to parent component to show success message and reload page
              }
            }
          ).catch(error => {
            console.log(error);
          });;
    },
    addNewBankAccount() {
      this.$emit("newBankAccount");
    }
  },
  // Watches for changes on props or data
  watch: {
    BankAccountId(newId, oldId) {
      this.bank_account_id = newId;
    },
    BankAccounts(newArray, oldArray) {
      this.bank_accounts = newArray;
    }
  },
  created() {
    this.ledger = this.Ledger;
    this.available_balance = this.AvailableBalance;
    this.bank_accounts = this.BankAccounts;
    this.banks = this.BankList;
    this.bank_Account_id = this.BankAccountId;
  }
};
</script>
<template>
  <div>
    <div id="modal-request-withdraw" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">{{ trans('withdrawals.request_withdraw') }}</h4>
          </div>
          <div class="modal-body">
            <form data-toggle="validator">
              <div class="row">
                <div class="col-sm-4 text-center align-middle">
                  <!-- Adicionando foto genérica -->
                  <img v-if="ledger.picture == ''" src="http://www.enactus.ufscar.br/wp-content/uploads/2016/09/boneco-gen%C3%A9rico.png" style="width:80px; height: 80px" alt="" class="img-circle">
                  <img v-else class="profile-user-img img-circle" :src="ledger.picture" style="width: 80px; height: 80px" alt="">
                </div>
                <div class="col-sm-8" style="word-wrap: break-word;">
                  <div class="profile-username">{{ ledger.first_name+' '+ledger.last_name }}</div>
                  <div class="text-muted profile-phone">{{ ledger.phone }}</div>
                  <div class="text-muted profile-email">{{ ledger.email }}</div>
                </div>
              </div><br>
              <div v-if="!isEmpty(error_messages)" id="field-errors" class="alert alert-danger alert-dismissable">
                <div v-for="message in error_messages" :key="message">
                  <p>{{message}}</p>
                </div>
              </div><br>
              <label>{{trans('withdrawals.bank_account') }} *</label>
              <div class="input-group">
                <select v-model="bank_account_id" name="bank_account" class="form-control" required>
                  <option v-if="bank_accounts.length == 0" value=""> {{trans('withdrawals.bank_account') }} </option>
                  <option v-for="account in bank_accounts" v-bind:value="account.id" v-bind:key="account.id">
                    {{ banks.find(bank => bank.id == account.bank_id).name + " - " + account.account + " - " + account.holder }}
                  </option>
                </select>
                <div class="help-block with-errors"></div>
                <div class="input-group-append"><div> </div>
                  <button v-on:click="addNewBankAccount" type="button" class="btn btn-success">{{ trans('withdrawals.add_new_bank_account') }}</button>
                </div>
              </div>
              <p></p>
              <div class="form-group">
                <label class="card-text">{{ trans('withdrawals.available_balance') }} :
                  <span v-if="available_balance > 0" class="label label-success"> {{ currency_format(available_balance, currencySymbol) }}</span>
                  <span v-else class="label label-danger"> {{ currency_format(available_balance, currencySymbol) }}</span>
                </label>
                <div>
                  <input type="number" class="form-control" v-model="with_draw_value" maxlength="60" :placeholder="trans( 'withdrawals.value') + '*'" required :data-error="trans( 'withdrawals.value_required') ">
                  <div class="help-block with-errors"></div>
                </div>
                <label class="card-text">{{ trans('withdrawals.required_balance') }} :
                  <span v-if="isNaN(parseFloat(with_draw_value) + parseFloat(WithDrawSettings.with_draw_tax)) && parseFloat(WithDrawSettings.with_draw_tax) <= parseFloat(available_balance) " class="label label-success"> {{ currency_format(parseFloat(WithDrawSettings.with_draw_tax), currencySymbol) }}</span>
                  <span v-else-if="isNaN(parseFloat(with_draw_value) + parseFloat(WithDrawSettings.with_draw_tax)) && parseFloat(WithDrawSettings.with_draw_tax) > parseFloat(available_balance)" class="label label-danger"> {{ currency_format(parseFloat(WithDrawSettings.with_draw_tax), currencySymbol) }}</span>
                  <span v-else-if="parseFloat(with_draw_value) + parseFloat(WithDrawSettings.with_draw_tax) <= parseFloat(available_balance)" class="label label-success"> {{ currency_format(parseFloat(with_draw_value) + parseFloat(WithDrawSettings.with_draw_tax), currencySymbol) }}</span>
                  <span v-else class="label label-danger"> {{ currency_format(parseFloat(with_draw_value) + parseFloat(WithDrawSettings.with_draw_tax), currencySymbol) }}</span>
                </label>
                <div class="card bg-light mb-3">
                  <div class="card-body text-center">
                    <label class="card-text">{{trans('withdrawals.minimum_value') }} :
                      <span class="label label-info">{{ currency_format(parseFloat(WithDrawSettings.with_draw_min_limit), currencySymbol) }}</span>
                      {{trans('withdrawals.maximum_value') }} :
                      <span class="label label-info">{{ currency_format(parseFloat(WithDrawSettings.with_draw_max_limit), currencySymbol) }}</span>
                    </label>
                    <label class="card-text"> {{trans('withdrawals.withdraw_tax') }} :
                      <span class="label label-warning">{{ currency_format(parseFloat(WithDrawSettings.with_draw_tax), currencySymbol) }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer ">
            <div class="pull-rigth ">
              <button v-on:click="requestWithdraw" type="button" class="btn btn-success " :disabled="!with_draw_value || !bank_account_id">{{ trans('withdrawals.request_withdraw') }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>