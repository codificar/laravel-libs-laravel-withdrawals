<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class UpdateWithdrawalsMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //create the Withdrawals menu
        $adminWithdrawals = Permission::updateOrCreate(['name' => 'Admin Withdrawals'] , ['name' => 'Admin Withdrawals', 'parent_id' => 2319, 'order' => 915, 'is_menu' => 1 ]);
        DB::statement("UPDATE `permission` SET `icon`='mdi mdi-bank' WHERE `id` = $adminWithdrawals->id ;");
        
        //create the withdrawals report in menu
        $withdrawalsReport = Permission::updateOrCreate(['name' => 'Withdrawals Report'] , ['name' => 'Withdrawals Report', 'parent_id' => $adminWithdrawals->id, 'order' => 915, 'is_menu' => 1, 'url' => '/admin/libs/withdrawals']);
        DB::statement("UPDATE `permission` SET `icon`='' WHERE `id` = $withdrawalsReport->id ;");

        //create the withdrawals settings in menu
        $WithdrawalsSettings = Permission::updateOrCreate(['name' => 'Withdrawals Settings'] , ['name' => 'Withdrawals Settings', 'parent_id' => $adminWithdrawals->id, 'order' => 916, 'is_menu' => 1, 'url' => '/admin/libs/withdrawals_settings']);
        DB::statement("UPDATE `permission` SET `icon`='' WHERE `id` = $WithdrawalsSettings->id ;");
        
        //create the cnab settings in menu
        $cnabSettings = Permission::updateOrCreate(['name' => 'Cnab Settings'] , ['name' => 'Cnab Settings', 'parent_id' => $adminWithdrawals->id, 'order' => 917, 'is_menu' => 1, 'url' => '/admin/libs/cnab_settings']);
        DB::statement("UPDATE `permission` SET `icon`='' WHERE `id` = $cnabSettings->id ;");
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