<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddWithdrawalsToMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $withdrawalsReport = Permission::updateOrCreate(['name' => 'Withdrawals Report'] , ['name' => 'Withdrawals Report', 'parent_id' => 2316, 'order' => 915, 'is_menu' => 1, 'url' => '/admin/libs/withdrawals', 'icon' => 'mdi mdi-file-chart']);

        $cnabSettings = Permission::updateOrCreate(['name' => 'Cnab Settings'] , ['name' => 'Cnab Settings', 'parent_id' => 2319, 'order' => 915, 'is_menu' => 1, 'url' => '/admin/libs/cnab_settings', 'icon' => 'mdi mdi-bank']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}