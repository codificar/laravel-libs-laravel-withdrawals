<script>
import axios from "axios";
import moment from "moment";
export default {
  props: [
    "Settings"
  ],
  data() {
    return {
      settings: {}
    };
  },
  methods: {

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
				"/admin/libs/withdrawals_settings/save", 
				{ settings: this.settings },
				this.trans("withdrawals.success_withdrawals_settings")
			);

        }
      });
    },

  },
  created() {
	this.settings = (JSON.parse(this.Settings));
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
            <h4 class="m-b-0 text-white">{{trans('withdrawals.withdrawals_settings')}}</h4>
          </div>
          <div class="card-block">
            <div class="row">
              <form data-toggle="validator" class="col-lg-12" v-on:submit.prevent="setWithdrawalsSettings()">
				
				<div class="row">
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.with_draw_enabled') }}</label>
							<select
								v-model="settings.with_draw_enabled"
								name="with_draw_enabled_code"
								class="select form-control"
							>
							<option
								v-for="method in [
									{
										'value': 0, 
										'name': 'Não'
									},
									{
										'value': 1, 
										'name': 'Sim'
									}
								]"
								v-bind:value="method.value"
								v-bind:key="method.value"
							>{{ method.name }}</option>
							</select>
						</div>
					</div>

					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.with_draw_tax') }}</label>
							<input
							type="text"
							class="form-control"
							name="with_draw_tax"
							id="with_draw_tax"
							maxlength="30"
							required
							v-model="settings.with_draw_tax"
							/>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.with_draw_min_limit') }}</label>
							<input
							type="text"
							class="form-control"
							name="with_draw_min_limit"
							id="with_draw_min_limit"
							maxlength="30"
							required
							v-model="settings.with_draw_min_limit"
							/>
						</div>
					</div>

					<div class="col-md-6 col-sm-12">
						<div class="form-group">
							<label class="control-label">{{trans('withdrawals.with_draw_max_limit') }}</label>
							<input
							type="text"
							class="form-control"
							name="with_draw_max_limit"
							id="with_draw_max_limit"
							maxlength="30"
							required
							v-model="settings.with_draw_max_limit"
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