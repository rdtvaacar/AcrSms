<?php

namespace Acr\Sms;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Sms extends Model

{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sms';

    function uye_id()
    {
        if (Auth::check()) {
            return Auth::user()->id;
        } else {
            return 0;
        }

    }

    function kurum_id()
    {
        if (Auth::check()) {
            return Auth::user()->kurum_id;
        } else {
            return 0;
        }
    }

    function gruplar()
    {
        return Sms_grup::where('kurum_id', self::kurum_id())->where('sil', 0)->get();
    }

    function numaralar()
    {
        return Sms_rehber::where('kurum_id', self::kurum_id())->where('sil', 0)->get();
    }

    function miktar()
    {
        if (!empty($miktar = @Sms_paketi::where('kurum_id', self::kurum_id())->first()->miktar)) {
            return $miktar;
        } else {
            return 0;
        }
    }

    function imza_id()
    {
        if (!empty($id = @Sms_imza::where('kurum_id', self::kurum_id())->where('sil', 0)->first()->id)) {
            return $id;
        } else {
            return 0;
        }
    }

    function imza()
    {
        if (!empty($sms_imza = @Sms_imza::where('kurum_id', self::kurum_id())->where('sil', 0)->first()->sms_imza)) {
            return $sms_imza;
        } else {
            return '';
        }
    }

    function smsler()
    {
        return Sms::where('kurum_id', self::kurum_id())->where('sil', 0)->get();
    }

    function sms_list($sms_id)
    {
        return Sms_list::where('kurum_id', self::kurum_id())->where('sms_id', $sms_id)->where('sil', 0)->get();
    }

}
