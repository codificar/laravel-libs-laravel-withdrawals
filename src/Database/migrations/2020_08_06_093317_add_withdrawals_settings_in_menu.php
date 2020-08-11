<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWithdrawalsSettingsInMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Settings::create([
            "key"           => "rem_company_name",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Nome da empresa para gerar arquivo de remessa. EN: Company name to generate remittance file in standard febraban',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_cpf_or_cnpj",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Tipo de documento, o campo value deve ser cpf ou cnpj. EN: Document type, the value field must be cpf or cnpj.',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_document",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: numero do cpf ou do cnpj, deve conter apenas numeros. EN: cpf or cnpj number, must contain only numbers',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_agency",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Numero da agencia sem digito verificador. Apenas numeros. EN: Agency number without check digit. Only numbers.',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_agency_dv",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Digito verificador da agencia. EN: agency digit number',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_account",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: numero da conta sem digito verificador. EN: account number without digit',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_account_dv",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Digito verificador da conta. EN: digit of account',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_bank_code",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Codigo do banco. EN: Bank code',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_agreement_number",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: Numero de convenio com o banco. EN: Agreement number',
            "page"          => '0',
            "category"      => "0",
            "sub_category"  => "0"
        ]);
        Settings::create([
            "key"           => "rem_transfer_type",
            "value"         => '',
            "tool_tip"      => '(ARQUIVO DE REMESSA) PT-BR: tipo de transferencia: ted ou doc. EN: ted or doc transfer type',
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
