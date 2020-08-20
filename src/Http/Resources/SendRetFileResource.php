<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SendRetFileResource
 *
 * @package MotoboyApp
 *
 *
 * @OA\Schema(
 *         schema="SendRetFileResource",
 *         type="object",
 *         title="Withdrawals Details Resource",
 *        allOf={
 *           @OA\Schema(ref="#/components/schemas/SendRetFileResource"),
 *           @OA\Schema(
 *              required={"success", "request"},
 *           )
 *       }
 * )
 */
class SendRetFileResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {

        return [
            'success' => true,
            'message' => $this['message'],
            'link'    => $this['link'],
        ];
    }

}
