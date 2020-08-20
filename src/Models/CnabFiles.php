<?php

namespace Codificar\Withdrawals\Models;

use Illuminate\Database\Eloquent\Relations\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Eloquent;
use Finance;
use DB;
use DateTime;


class CnabFiles extends Eloquent
{

    protected $table = 'cnab_files';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    public static function updateColumn($id, $column, $value) {

        $update = DB::table('cnab_files')
            ->where('id', '=', $id)
            ->update(
                [
                    $column => $value
                ]
        );
        return $update;
    }

    public static function getCnabfiles()
    {
        $query = DB::table('cnab_files')->select('cnab_files.*')
            ->orderBy('cnab_files.id', 'desc')
            ->get();

        
        //Arruma as datas
        foreach($query as $row) {
            $row->date_rem = $row->date_rem ? (new DateTime($row->date_rem))->format('d/m/Y') : null;
            $row->date_ret = $row->date_ret ? (new DateTime($row->date_ret))->format('d/m/Y') : null;
        }

        return $query;
    }

}
