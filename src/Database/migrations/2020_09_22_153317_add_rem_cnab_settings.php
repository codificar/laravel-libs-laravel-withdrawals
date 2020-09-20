<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemCnabSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::create([
            "key"           => "rem_type_compromise",
            "value"         => '01',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Numero do tipo de compromisso. EN: Number of compromisse type',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_code_compromise",
            "value"         => '0001',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Numero codigo de compromisso. EN: Number of compromise code',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_param_transmission",
            "value"         => '01',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Numero do parametro de transmissao. EN: Number oftransmission param',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
