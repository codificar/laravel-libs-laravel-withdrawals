<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddWithdrawalsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::updateOrCreate(array('key' => 'with_draw_enabled'), array('value' => '0', 'page' => 1, 'category' => 6, 'tool_tip' => 'Habilita opcao de saque para usuario'));
        Settings::updateOrCreate(array('key' => 'with_draw_max_limit'), array('value' => '1000.00', 'page' => 1, 'category' => 6, 'tool_tip' => 'Define valor maximo permitido para saque'));
        Settings::updateOrCreate(array('key' => 'with_draw_min_limit'), array('value' => '10.00', 'page' => 1, 'category' => 6, 'tool_tip' => 'Define valor minimo permitido para saque'));
        Settings::updateOrCreate(array('key' => 'with_draw_tax'), array('value' => '4.00', 'page' => 1, 'category' => 6, 'tool_tip' => 'Define a taxa do saque'));
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