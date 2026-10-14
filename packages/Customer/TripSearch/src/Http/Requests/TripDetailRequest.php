<?php

declare(strict_types=1);

namespace FuteBus\TripSearch\Http\Requests;

class TripDetailRequest extends TripSearchRequest
{
    public function rules(): array
    {
        return parent::rules() + ['direction' => ['required', 'in:outbound,return']];
    }
}
