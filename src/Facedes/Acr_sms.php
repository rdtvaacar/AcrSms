<?php
namespace Acr\Sms\Facedes;

use Illuminate\Support\Facades\Facade;

class Acr_sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'acr-sms';
    }
}