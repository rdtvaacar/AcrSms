<?php

namespace Acr\Sms;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Sms_grup_tel extends Model

{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table    = 'sms_grup_tel';
    protected $uye_id   = '';
    protected $kurum_id = '';

    function grup_telefonlar()
    {
        Sms_grup_tel::where('kurum_id', $this->kurum_id)->where('sil', 0)->get();
    }
}
