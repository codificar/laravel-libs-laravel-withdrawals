<?php

namespace Codificar\Withdrawals\Database\seeds;

use Illuminate\Database\Seeder;
use Permission;
use ProfilePermission;

class AddWithdrawalsMenu extends Seeder
{
    /**
     * Run the database seeds for withdrawals
     *
     * @return void
     */
    public function run()
    {
        $withdrawalsReport = Permission::updateOrCreate(['name' => 'Withdrawals Report'] , ['name' => 'Withdrawals Report', 'parent_id' => 2316, 'order' => 915, 'is_menu' => 1, 'url' => '/admin/libs/withdrawals', 'icon' => 'mdi mdi-file-chart']);

        $cnabSettings = Permission::updateOrCreate(['name' => 'Cnab Settings'] , ['name' => 'Cnab Settings', 'parent_id' => 2319, 'order' => 915, 'is_menu' => 1, 'url' => '/admin/libs/cnab_settings', 'icon' => 'mdi mdi-bank']);

        ProfilePermission::updateOrCreate(['permission_id' => $withdrawalsReport->id], ['profile_id' => 3, 'permission_id' => $withdrawalsReport->id]);
        ProfilePermission::updateOrCreate(['permission_id' => $cnabSettings->id], ['profile_id' => 3, 'permission_id' => $cnabSettings->id]);

        $this->command->info('Withdrawals Permissions created!');
    }
}