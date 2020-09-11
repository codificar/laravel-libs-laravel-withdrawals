<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsCnabFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Settings::create([
            "key"           => "rem_environment",
            "value"         => 'T',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Ambiente T de teste ou P de producao para gerar arquivo de remessa. EN: Environment T or P to generate remittance file in standard febraban',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_address",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Endereco (logradouro) da empresa. EN: Address of company.',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_address_number",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: numero do endereco da empresa. EN: Address number of company',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_city",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Cidade da empresa. Apenas numeros. EN: city of company.',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_cep",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: cep da empresa. EN: cep of company',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_state",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: sigla do estado da empresa (2 digitos). EN: address state of company',
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
