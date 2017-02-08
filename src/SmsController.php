<?php
namespace Acr\Sms;

use Acr\Sms\Facedes\Acr_sms;
use App\Handlers\Commands\my;
use Acr\Sms\Sms_grup;
use Acr\Sms\Sms_grup_tel;
use Acr\Sms\Sms_imza;
use Acr\Sms\Sms_list;
use Acr\Sms\Sms_paketi;
use Acr\Sms\Sms_rehber;
use DB;
use Acr\Sms\Sms;
use Form;
use Illuminate\Support\Facades\Input;
use View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;


class SmsController extends Controller
{

    protected $basarili           = '<div class="alert alert-success">Başarıyla Eklendi</div>';
    protected $smsGonderildi      = '<div class="alert alert-success">SMS Gönderimi Başarıyla Gerçekleşti</div>';
    protected $basariliGuncelleme = '<div class="alert alert-success">Başarıyla Güncellendi</div>';
    protected $kullaniciAdi       = '';
    protected $kullaniciSifre     = '';
    protected $config;

    function __construct()
    {
        $this->config = Config::get("AcrSmsConfig");
    }

    function index()
    {
        $my  = new my();
        $sms = new SmsController();
        return View::make('acr_sms.index', compact('sms', 'my'));
    }

    function grup_secim()
    {
        $sms     = new Sms();
        $gruplar = $sms->gruplar();
        $form    = '<div class="bax" style=" padding: 4px; overflow-y:scroll; max-height:300px;">';
        foreach ($gruplar as $item) {
            $form .= '<input name="sms_grup_id[]" id="sms_grup_id' . $item->id . '" type="checkbox" style="width:22px; height:22px; " value="' . $item->id . '"/>
            <label style="cursor:pointer; " for="sms_grup_id' . $item->id . '">' . $item->sg_isim . '</label>
            <div style="clear:both;"></div>';
        }
        $form .= '</div>';
        return $form;
    }

    function yeni_grup()
    {
        return '<button class="btn btn-success btn-sm" onclick="acr_sms_grup_duzenle(0)">Yeni Grup Oluştur</button>';
    }

    function yeni_numara()
    {
        return '<button class="btn btn-success btn-sm" onclick="acr_sms_rehber_duzenle(0,0)">Yeni Numara Ekle</button>';
    }

    function yeni_sms()
    {
        return '<button class="btn btn-success btn-sm" onclick="acr_sms_form(0)">Yeni SMS Gönder</button>';
    }

    function yeni_imza()
    {
        $sms     = new Sms();
        $imza_id = $sms->imza_id();
        return '<button class="btn btn-success btn-sm" onclick="acr_sms_imza_duzenle(' . $imza_id . ')">Yeni İmza  Ekle</button>';
    }

    function smsMiktar()
    {
        $sms_paketi = new Sms();
        return $sms_paketi->miktar();
    }

    function gonderilen_smsler()
    {
        $sms    = new Sms();
        $smsler = $sms->smsler();
        $form   = '<div  class="bax" style=" overflow-y:scroll; padding: 4px; max-height:300px;">';
        foreach ($smsler as $item) {
            $form .= '<div style="float: left; width: 80%">' . $item->sms_isim . '</div>';
            $form .= '<div class="btn btn-sm btn-warning" onclick="acr_gonderilen_sms_goster(' . $item->id . ')" style="float: left">İncele</div>';
            $form .= '<div style="clear:both;"></div>';
        }
        $form .= '</div>';
        return $form;
    }

    function gonderilen_sms_incele($sms_id)
    {

        $sms_model        = new Sms();
        $sms_grup_model   = new Sms_grup();
        $sms_rehber_model = new Sms_rehber();

        $sms           = $sms_model->where('id', $sms_id)->where('kurum_id', $sms_model->kurum_id())->first();
        $smsListesi    = $sms->sms_list($sms->id);
        $sms_gruplar   = [];
        $sms_rehber_id = [];
        $sms_tel       = [];
        foreach ($smsListesi as $item) {
            $sms_gruplar[]   = $item->sms_grup_id;
            $sms_rehber_id[] = $item->sms_rehber_id;
            if (empty($item->sms_grup_id) && empty($item->sms_rehber_id)) {
                $sms_tel[] = $item->list_tel;
            }

        }
        $form = '<div style="float: left; width: 80%"><strong>SMS İsim</strong><br>' . $sms->sms_isim . '</div>';
        $form .= '<div style="float: left; width: 80%"><strong>Gönderilen Mesaj</strong><br>' . $sms->s_mesaj . '</div>';
        $sms_grup = $sms_grup_model->whereIn('id', $sms_gruplar)->get();
        if (!empty($sms_grup)) {
            $form .= '<div  class="bax" style="float: right; width: 45%; padding: 4px;"><strong>Gruplar</strong>';
            $form .= '<ol>';
            foreach ($sms_grup as $item) {
                $form .= '<li>' . $item->sg_isim . '</li>';
            }
            $form .= '</ol>';
            $form .= '</div>';
        }
        $sms_rehber = $sms_rehber_model->whereIn('id', $sms_rehber_id)->get();
        if (!empty($sms_rehber)) {
            $form .= '<div class="bax" style="float: left; width: 45%; padding: 4px;"><strong>Numaralar</strong>';
            $form .= '<ol>';
            foreach ($sms_rehber as $item) {
                $form .= '<li>' . $item->sr_isim . ' (' . $item->sr_tel . ')</li>';
            }
            $form .= '</ol>';
            $form .= '</div>';
        }
        if (!empty($sms_tel)) {
            $form .= '<div class="bax" style="float: left; width: 100%; padding: 4px;">';
            $form .= '<div style="float: left; width: 30%"><strong>İsimsiz Numaralar</strong> </div>';
            $form .= '<div style="float: right;" class="btn btn-warning btn-xs" onclick="acr_numaralardan_grup_olustur(\'' . $sms_id . '\')">İsimsiz Numaraları Gruba Ekle</div>';
            $form .= '<div style="clear:both;"></div>';
            $form .= '<ol>';
            foreach ($sms_tel as $item) {
                $form .= '<li>' . $item . '<div style=" margin-left:40px;" class="btn btn-success btn-xs" onclick="acr_sms_rehber_duzenle(0,\'' . $item . '\')">Rehbere Ekle</div></li>';
            }
            $form .= '</ol>';
            $form .= '</div>';
        }
        return $form;
    }

    function gonderilen_sms_goster()
    {

        $sms_id = Input::get('id');

        return ['Gönderilen SMS', self::gonderilen_sms_incele($sms_id)];
    }

    function sms_form()
    {
        $form = Form::open(['url' => 'acr_sms_gonder']);
        $form .= '<label>Sms Adı</label>';
        $form .= '<input name="sms_isim" id="sms_isim"  class="form-control"/>';
        $form .= '<label>Mesajınız</label>';
        $form .= '<textarea style="height: 70px;" name="s_mesaj" id="s_mesaj" class="form-control"></textarea>';
        $form .= '<div style=" float: left; width: 45%; "><strong>Grup Ekle</strong><br>' . self::grup_secim() . '</div>';
        $form .= '<div style=" float: right; width: 45%; "><strong>Numara Ekle</strong><br>' . self::numara_secim() . '</div>';
        $form .= '<label>Rehber Dışında Numara (Aralarında virgül olmalı ÖRN: 555 555 5555, 5444444444)</label>';
        $form .= '<textarea style="height: 70px;" name="sms_tel" id="sms_tel" class="form-control"></textarea>';
        $form .= '<button class="btn btn-block btn-info">SMS Gönder</button>';
        $form .= Form::close();
        return $form;
    }

    function imza()
    {
        $sms = new Sms();
        return $sms->imza();
    }

    function sms_form_gonder()
    {

        $sms          = new Sms();
        $grup         = new Sms_grup();
        $rehber       = new Sms_rehber();
        $grup_tel     = new Sms_grup_tel();
        $sms_list     = new Sms_list();
        $mesaj        = Input::get('s_mesaj') . ' ' . $sms->imza();
        $data_sms     = [
            'kurum_id' => $sms->kurum_id(),
            'uye_id'   => $sms->uye_id(),
            'sms_isim' => Input::get('sms_isim'),
            's_mesaj'  => $mesaj
        ];
        $sms_id       = $sms->insertGetId($data_sms);
        $rehber_id    = [];
        $sms_grup_id  = Input::get('sms_grup_id');
        $sms_grup_tel = $grup_tel->whereIn('sms_grup_id', $sms_grup_id)->get();
        foreach ($sms_grup_tel as $item) {
            $rehber_id[] = $item->sms_rehber_id;
            $data[]      = [
                'kurum_id'      => $sms->kurum_id(),
                'uye_id'        => $sms->uye_id(),
                'sms_id'        => $sms_id,
                'sms_grup_id'   => $item->sms_grup_id,
                'sms_rehber_id' => $item->sms_rehber_id,
            ];

        }
        if (!empty($data)) {
            $sms_list->insert($data);
        }
        $sms_rehber_id = Input::get('sr_tel_id');
        if (is_array($sms_rehber_id)) {
            foreach ($sms_rehber_id as $item) {
                $rehber_id[] = $item;
                $data2[]     = [
                    'kurum_id'      => $sms->kurum_id(),
                    'uye_id'        => $sms->uye_id(),
                    'sms_id'        => $sms_id,
                    'sms_rehber_id' => $item,
                ];
            }
            $sms_list->insert($data2);
        }

        $rehberData = $rehber->whereIn('id', $rehber_id)->get();
        foreach ($rehberData as $rehberDatum) {
            $tel[] = $rehberDatum->sr_tel;
        }
        if (!empty(Input::get('sms_tel'))) {
            $sms_telDizi = explode(",", Input::get('sms_tel'));
            foreach ($sms_telDizi as $item) {
                $tel[]   = $item;
                $data3[] = [
                    'kurum_id' => $sms->kurum_id(),
                    'uye_id'   => $sms->uye_id(),
                    'sms_id'   => $sms_id,
                    'list_tel' => $item,
                ];
            }
            if (!empty($data3)) {
                $sms_list->insert($data3);
            }
        }

        if (strlen($mesaj) > 0) {
            $smsGonderim = self::smsGonderUcretli($mesaj, $tel);
            if ($smsGonderim[0] == 0) {


                return redirect()->back()->with('msg', '<div class="alert alert-success">SMS Gönderildi Kalan SMS :' . $smsGonderim[1] . ' </div>');
            } else {
                return redirect()->back()->with('msg', '<div class="alert alert-danger">SMS Paketiniz yetersiz lütfen <a href="/smsPaketiTanimla">SMS paketi </a>satın alın.</div>');
            }
        } else {
            return redirect()->back()->with('msg', '<div class="alert alert-danger">Boş mesaj gönderemezsiniz</div>');
        }

    }

    function smsGonderUcretli($mesaj = null, $tel = null)
    {
        $mesaj      = $mesaj;
        $tel        = $tel;
        $smsKontrol = self::kalanSmsHesapla($mesaj, $tel);
        if ($smsKontrol[0] == 1) {
            self::smsGonder($mesaj, $tel);
            return [0, $smsKontrol[1]];
        } else {
            return [1, $smsKontrol[1]];
        }

    }

    private
    function smsGonder($mesaj = null, $tel = null)
    {
        if (Input::get('tumUyeler') == 1) {
            $tel    = [];
            $uyeler = DB::table('users')->whereNotNull('tel')->where('tel', '!=', '')->get();
            foreach ($uyeler as $uye) {
                if (strlen(trim($uye->tel)) > 9 && $uye->sms != 1) {
                    $tel[] = $uye->tel;
                }
            }
        }
        if (empty($mesaj)) {

            $mesaj = my::ingilizceYap(strip_tags(trim(Input::get('mesaj'))));
        } else {

            $mesaj = $mesaj;
        }
        if (empty($tel) && !is_array(Input::get('tel'))) {
            $telDizi = explode("/*", trim(Input::get('tel')));
        } else if (is_array(Input::get('tel'))) {
            $telDizi = Input::get('tel');
        } else {
            $telDizi = $tel;
        }
        array_unique($telDizi);


        $mesajData['user']      = array(
            'name' => $this->config['kullaniciAdi'],
            'pass' => $this->config['kullaniciSifre']
        );
        $mesajData['msgBaslik'] = '850';
        $mesajData['msgData'][] = array(
            'tel' => $telDizi,
            'msg' => $mesaj,
        );
        self::MesajPaneliGonder($mesajData);
        return 1;

    }

    function MesajPaneliGonder($request)
    {
        $request = "data=" . base64_encode(json_encode($request));
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.mesajpaneli.com/json_api/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode(base64_decode($result), TRUE);
    }

    function kalanSmsHesapla($mesaj = null, $tel = null)
    { // sms gönderimi sırasında kullanılacak sms miktarını hesaplar
        $sms         = new Sms();
        $Sms_paketi  = new Sms_paketi();
        $telefonSayi = count($tel);
        $mesajUzun   = ceil(strlen($mesaj) / 155);
        $smsSayi     = $telefonSayi * $mesajUzun;
        $uyeSms      = $Sms_paketi->where('kurum_id', $sms->kurum_id())->first();
        if (@$uyeSms->miktar >= $smsSayi) {
            $Sms_paketi->where('kurum_id', $sms->kurum_id())->update(array('miktar' => $uyeSms->miktar - $smsSayi));
            return [1, ($uyeSms->miktar - $smsSayi)];
        } else {
            return [2, @$uyeSms->miktar];
        }
    }

    function yeni_sms_gonder()
    {

        $id = Input::get('id');
        return ['Yeni SMS Gönder', self::sms_form($id)];
    }

    function gruplar()
    {
        $sms     = new Sms();
        $gruplar = $sms->gruplar();
        $form    = '<div class="bax" style=" padding: 4px; overflow-y:scroll; max-height:300px;">';
        foreach ($gruplar as $item) {
            $form .= '<div id="sms_grup_id_' . $item->id . '">';
            $form .= '<div style=" float: left; width:150px;">' . $item->sg_isim . '</div>';
            $form .= '<div style=" float:right;"  onclick="acr_sms_grup_sil(' . $item->id . ')" class="btn btn-danger btn-sm">SİL</div>';
            $form .= '<div style=" float:right;"  onclick="acr_sms_grup_duzenle(' . $item->id . ')" class="btn btn-warning btn-sm">DÜZ</div>';
            $form .= '<div style="clear:both;"></div>';
            $form .= '</div>';
        }
        $form .= '</div>';
        return $form;
    }

    function grup_olustur()
    {

        $sms           = new Sms();
        $sms_grup      = new Sms_grup();
        $sms_rehber    = new Sms_rehber();
        $sms_grup_tel  = new Sms_grup_tel();
        $sms_rehber_id = [];
        if (!empty(Input::get('grup_form_olustur_tel'))) {
            $numaralar_kayitsiz = explode(',', Input::get('grup_form_olustur_tel'));
            foreach ($numaralar_kayitsiz as $item) {
                $data            = [
                    'uye_id'   => $sms->uye_id(),
                    'kurum_id' => $sms->kurum_id(),
                    'sr_tel'   => $item
                ];
                $sms_rehber_id[] = $sms_rehber->insertGetId($data);
            }
        }
        $sr_tel_id = Input::get('sr_tel_id');
        $numaralar = array_merge($sr_tel_id, $sms_rehber_id);
        if (!empty(Input::get('sms_grup_id'))) {
            $sms_grup = $sms_grup->where('kurum_id', $sms->kurum_id())->find(Input::get('sms_grup_id'));
            $sms_grup_tel->where('sms_grup_id', $sms_grup->id)->delete();
            self::grup_rehber_numara_ekle($numaralar, $sms_grup->id);

            $sms_grup->sg_isim     = Input::get('sg_isim');
            $sms_grup->sg_aciklama = Input::get('sg_aciklama');

            $sms_grup->update();
            return redirect()->back()->with('msg', $this->basarili);
        }

        $data_grup = [
            'sg_isim'     => Input::get('sg_isim'),
            'sg_aciklama' => Input::get('sg_aciklama'),
            'uye_id'      => $sms->uye_id(),
            'kurum_id'    => $sms->kurum_id(),
        ];
        $grup_id   = $sms_grup->insertGetId($data_grup);
        self::grup_rehber_numara_ekle($numaralar, $grup_id);
        return redirect()->back()->with('msg', $this->basarili);
    }

    function numaralardan_grup_olustur()
    {
        $sms        = new Sms();
        $sms_id     = Input::get('num');
        $smsListesi = $sms->sms_list($sms_id);
        $sms_tel    = [];
        foreach ($smsListesi as $item) {
            if (empty($item->sms_grup_id) && empty($item->sms_rehber_id)) {
                $sms_tel[] = $item->list_tel;
            }
        }
        return ['Numaralar için grup tanımla', self::numaralardan_grup_form($sms_tel)];
    }

    function numaralardan_grup_form($numaralar)
    {
        $sms        = new Sms();
        $sms_rehber = new Sms_rehber();

        foreach ($numaralar as $item) {
            $data            = [
                'uye_id'   => $sms->uye_id(),
                'kurum_id' => $sms->kurum_id(),
                'sr_tel'   => $item
            ];
            $sms_rehber_id[] = $sms_rehber->insertGetId($data);
        }
        $form = Form::open(['url' => 'acr_sms_grup_olustur', 'name' => 'sms_grup_form', 'id' => 'sms_grup_form']);
        $form .= '<label>İsim</label>';
        $form .= '<input name="sg_isim" id="sg_isim" class="form-control"/>';
        $form .= '<label>Açıklama</label>';
        $form .= '<textarea name="sg_aciklama" id="sg_aciklama" class="form-control"></textarea>';
        foreach ($sms_rehber_id as $item) {
            $form .= '<input name="sr_tel_id[]" id="sr_tel_id" value="' . $item . '" type="hidden"/>';
        }
        $form .= '<button class="btn btn-block btn-info">Grup Oluştur</button>';
        $form .= Form::close();
        return $form;

    }

    function grup_sil($id = null)
    {
        $sms          = new Sms();
        $id           = empty($id) ? Input::get('id') : $id;
        $sms_grup     = new Sms_grup();
        $sms_grup_tel = new Sms_grup_tel();
        $sms_grup_tel->where('sms_grup_id', $id)->delete();
        $sms_grup->where('kurum_id', $sms->kurum_id())->where('id', $id)->update(['sil' => 1]);
    }

    function grup_rehber_numara_ekle($numaralar, $grup_id)
    {
        $sms          = new Sms();
        $sms_grup_tel = new Sms_grup_tel();
        foreach ($numaralar as $item) {
            $data_rehber[] = [
                'sms_rehber_id' => $item,
                'uye_id'        => $sms->uye_id(),
                'kurum_id'      => $sms->kurum_id(),
                'sms_grup_id'   => $grup_id
            ];
        }
        $sms_grup_tel->insert($data_rehber);
    }

    function grup_form($id = null)
    {
        $sms      = new Sms();
        $sms_grup = new Sms_grup();
        if (empty($id)) {
            $sg_isim     = '';
            $sg_aciklama = '';
        } else {
            $grup        = $sms_grup->find($id);
            $sg_isim     = $grup->sg_isim;
            $sg_aciklama = $grup->sg_aciklama;
        }
        $sms_grup_tel = new Sms_grup_tel();
        $grup_tel     = $sms_grup_tel->where('sms_grup_id', $id)->get();
        $dataNum      = [];
        foreach ($grup_tel as $item) {
            $dataNum[] = $item->sms_rehber_id;
        }
        $form = Form::open(['url' => 'acr_sms_grup_olustur', 'name' => 'sms_grup_form', 'id' => 'sms_grup_form']);
        $form .= '<label>İsim</label>';
        $form .= '<input name="sg_isim" id="sg_isim" value="' . $sg_isim . '" class="form-control"/>';
        $form .= '<label>Açıklama</label>';
        $form .= '<textarea name="sg_aciklama" id="sg_aciklama" class="form-control">' . $sg_aciklama . '</textarea>';
        $form .= self::numara_secim($dataNum);
        $form .= '<label>Rehber Dışında Numara (Aralarında virgül olmalı ÖRN: 555 555 5555, 5444444444)</label>';
        $form .= '<textarea style="height: 70px;" name="grup_form_olustur_tel" id="grup_form_olustur_tel" class="form-control"></textarea>';
        $form .= empty($id) ? '' : '<input name="sms_grup_id" id="sms_grup_id" value="' . $id . '" type="hidden"/>';
        $form .= '<button class="btn btn-block btn-info">Grup Oluştur</button>';
        $form .= Form::close();
        return $form;

    }

    function numaralar()
    {
        $sms       = new Sms();
        $numaralar = $sms->numaralar();
        $form      = '<div class="bax" style=" overflow-y:scroll; padding: 4px; max-height:300px;">';
        foreach ($numaralar as $item) {
            $form .= '<div id="sms_rehber_id_' . $item->id . '">';
            $form .= '<div style=" float: left; width: 150px; ">' . $item->sr_isim . '</div>';
            $form .= '<div style=" float:right;" onclick="acr_sms_rehber_sil(' . $item->id . ')" class="btn btn-danger btn-sm">SİL</div>';
            $form .= '<div style=" float:right;"  onclick="acr_sms_rehber_duzenle(' . $item->id . ')" class="btn btn-warning btn-sm">DÜZ</div>';
            $form .= '<div style="clear:both;"></div>';
            $form .= '</div>';
        }
        $form .= '</div>';
        return $form;
    }

    function numara_secim($dataNum = null)
    {
        $dataNum   = is_array($dataNum) ? $dataNum : [];
        $sms       = new Sms();
        $numaralar = $sms->numaralar();
        $form      = '<div class="bax" style="padding: 4px; overflow-y:scroll; max-height:300px;">';
        $form .= '<label>Rehberden Numara Seç</label><div style="clear:both;"></div>';
        foreach ($numaralar as $item) {
            $checked = in_array($item->id, $dataNum) ? 'checked="checked"' : '';

            $form .= '<input ' . $checked . ' name="sr_tel_id[]" id="sr_tel_id' . $item->id . '" type="checkbox" style="width:22px; height:22px; " value="' . $item->id . '"/>
            <label style="cursor:pointer; " for="sr_tel_id' . $item->id . '">' . $item->sr_isim . ' (' . $item->sr_tel . ')' . '</label>
            <div style="clear:both;"></div>';
        }
        $form .= '</div>';
        return $form;
    }

    function rehber_form($id = null, $num = null)
    {
        $data_model = new Sms_rehber();
        if (empty($id)) {
            if (empty($num)) {
                $sr_tel = '';
            } else {
                $sr_tel = $num;
            }
            $sr_isim = '';
        } else {
            $data    = $data_model->find($id);
            $sr_tel  = $data->sr_tel;
            $sr_isim = $data->sr_isim;
        }
        $form = Form::open(['url' => 'acr_sms_rehber_kaydet']);
        $form .= '<label>İsim</label>';
        $form .= '<input name="sr_isim" id="sr_isim" value="' . $sr_isim . '" class="form-control"/>';
        $form .= '<label>Numara</label>';
        $form .= '<input name="sr_tel" id="sr_tel"  value="' . $sr_tel . '" class="form-control"/>';
        $form .= empty($id) ? '' : '<input name="sms_rehber_id" id="sms_rehber_id" value="' . $id . '" type="hidden"/>';
        $form .= '<button class="btn btn-block btn-info">Rehbere Ekle</button>';
        $form .= Form::close();
        return $form;
    }

    function rehber_kaydet()
    {
        $sms        = new Sms();
        $sms_rehber = new Sms_rehber();
        $data       = [
            'kurum_id' => $sms->kurum_id(),
            'uye_id'   => $sms->uye_id(),
            'sr_isim'  => Input::get('sr_isim'),
            'sr_tel'   => Input::get('sr_tel'),
        ];
        if (!empty(Input::get('sms_rehber_id'))) {
            $sms_rehber->where('id', Input::get('sms_rehber_id'))->where('kurum_id', $sms->kurum_id())->update($data);
        } else {
            $sms_rehber->insert($data);
        }

        return redirect()->back()->with('msg', $this->basarili);
    }

    function rehber_sil($id = null)
    {
        $sms          = new Sms();
        $id           = empty($id) ? Input::get('id') : $id;
        $sms_rehber   = new Sms_rehber();
        $sms_grup_tel = new Sms_grup_tel();
        $sms_grup_tel->where('sms_rehber_id', $id)->delete();
        $sms_rehber->where('id', $id)->where('kurum_id', $sms->kurum_id())->update(['sil' => 1]);
    }

    function sms_rehber_duzenle()
    {
        $id  = Input::get('id');
        $num = Input::get('num');
        return ['SMS Numara Ekle', self::rehber_form($id, $num)];
    }

    function sms_grup_duzenle()
    {
        $id = Input::get('id');

        return ['SMS Grup Oluştur', self::grup_form($id)];
    }

    function imza_form($id = null)
    {
        $sms = new Sms();
        if (empty($id)) {
            $sms_imza = '';
        } else {
            $sms_imza = $sms->imza();
        }
        $form = Form::open(['url' => 'acr_sms_imza_kaydet']);
        $form .= '<label>İmzanız</label>';
        $form .= '<input name="sms_imza" id="sms_imza" value="' . $sms_imza . '" class="form-control"/>';
        $form .= empty($id) ? '' : '<input name="imza_id" id="imza_id" value="' . $id . '" type="hidden"/>';
        $form .= '<button class="btn btn-block btn-info">İmzanızı Ekleyin</button>';
        $form .= Form::close();
        return $form;
    }

    function imza_duzenle()
    {
        $id = Input::get('id');

        return ['SMS İmza Düzenle', self::imza_form($id)];
    }

    function imza_kaydet()
    {
        $data_model = new Sms_imza();
        $sms        = new Sms();
        if (Input::get('imza_id')) {
            $data_model->where('id', Input::get('imza_id'))->where('kurum_id', $sms->kurum_id())->update(['sms_imza' => Input::get('sms_imza')]);
            return redirect()->back()->with('msg', $this->basariliGuncelleme);
        } else {
            $data_model->uye_id   = $sms->uye_id();
            $data_model->kurum_id = $sms->kurum_id();
            $data_model->sms_imza = Input::get('sms_imza');
            $data_model->save();
            return redirect()->back()->with('msg', $this->basarili);
        }
    }

    function sms_modal()
    {
        return '<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><span aria-hidden="true"><img src="icon/close48.png"/></span></span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Kapat</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>';
    }

    function sms_script()
    {
        return 'function acr_sms_grup_duzenle(id) {
        $.ajax({
            type   : \'post\',
            url    : \'acr_sms_grup_duzenle\',
            data   : \'id=\' + id,
            success: function (veri) {
                $(\'.modal-title\').html(veri[0]);
                $(\'.modal-body\').html(veri[1]);
                $(\'#myModal\').modal(\'show\');

            }
        });
    }
    function acr_sms_form(id) {
        $.ajax({
            type   : \'post\',
            url    : \'acr_sms_form\',
            data   : \'id=\' + id,
            success: function (veri) {
                $(\'.modal-title\').html(veri[0]);
                $(\'.modal-body\').html(veri[1]);
                $(\'#myModal\').modal(\'show\');

            }
        });
    }
    function acr_numaralardan_grup_olustur(num) {
        $.ajax({
            type   : \'post\',
            url    : \'acr_numaralardan_grup_olustur\',
            data   : \'num=\' + num,
            success: function (veri) {
                $(\'.modal-title\').html(veri[0]);
                $(\'.modal-body\').html(veri[1]);
                $(\'#myModal\').modal(\'show\');

            }
        });
    }
    
    function acr_gonderilen_sms_goster(id) {
        $.ajax({
            type   : \'post\',
            url    : \'acr_gonderilen_sms_goster\',
            data   : \'id=\' + id,
            success: function (veri) {
                $(\'.modal-title\').html(veri[0]);
                $(\'.modal-body\').html(veri[1]);
                $(\'#myModal\').modal(\'show\');

            }
        });
    }
    
    function acr_sms_imza_duzenle(id) {
        $.ajax({
            type   : \'post\',
            url    : \'acr_sms_imza_duzenle\',
            data   : \'id=\' + id,
            success: function (veri) {
                $(\'.modal-title\').html(veri[0]);
                $(\'.modal-body\').html(veri[1]);
                $(\'#myModal\').modal(\'show\');

            }
        });
    }
    function acr_sms_rehber_sil(id) {
        if (confirm(\'Numara rehberden silinsin mi?\') == true) {
            $.ajax({
                type   : \'post\',
                url    : \'acr_sms_rehber_sil\',
                data   : \'id=\' + id,
                success: function () {
                    $(\'#sms_rehber_id_\' + id).hide();
                }
            });
        }
    }
    function acr_sms_rehber_duzenle(id,num) {
        $.ajax({
            type   : \'post\',
            url    : \'acr_sms_rehber_duzenle\',
            data   : \'id=\' + id+"&num="+num,
            success: function (veri) {
                $(\'.modal-title\').html(veri[0]);
                $(\'.modal-body\').html(veri[1]);
                $(\'#myModal\').modal(\'show\');

            }
        });
    }
    function acr_sms_grup_sil(id) {
        if (confirm(\'SMS grubu silinsin mi?\') == true) {
            $.ajax({
                type   : \'post\',
                url    : \'acr_sms_grup_sil\',
                data   : \'id=\' + id,
                success: function () {
                    $(\'#sms_grup_id_\' + id).hide();
                }
            });
        }
    }';
    }

    function smsTabloKur()
    {

        $sql = '-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 05 Şub 2017, 10:27:08
-- Sunucu sürümü: 10.1.16-MariaDB
-- PHP Sürümü: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Veritabanı: `oaso`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms`
--

CREATE TABLE `sms` (
  `id` int(11) NOT NULL,
  `kurum_id` int(11) DEFAULT NULL,
  `uye_id` int(11) DEFAULT NULL,
  `sms_isim` varchar(200) COLLATE utf8_turkish_ci DEFAULT NULL,
  `s_mesaj` text COLLATE utf8_turkish_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sil` int(11) NOT NULL DEFAULT \'0\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms_grup`
--

CREATE TABLE `sms_grup` (
  `id` int(11) NOT NULL,
  `kurum_id` int(11) NOT NULL,
  `uye_id` int(11) NOT NULL,
  `sg_isim` varchar(250) COLLATE utf8_turkish_ci DEFAULT NULL,
  `sg_aciklama` varchar(250) COLLATE utf8_turkish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sil` int(11) NOT NULL DEFAULT \'0\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms_grup_tel`
--

CREATE TABLE `sms_grup_tel` (
  `id` int(11) NOT NULL,
  `sms_grup_id` int(11) DEFAULT NULL,
  `sms_rehber_id` int(11) DEFAULT NULL,
  `kurum_id` int(11) DEFAULT NULL,
  `uye_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms_imza`
--

CREATE TABLE `sms_imza` (
  `id` int(11) NOT NULL,
  `kurum_id` int(11) DEFAULT NULL,
  `uye_id` int(11) DEFAULT NULL,
  `sms_imza` varchar(100) COLLATE utf8_turkish_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sil` int(11) NOT NULL DEFAULT \'0\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms_list`
--

CREATE TABLE `sms_list` (
  `id` int(11) NOT NULL,
  `kurum_id` int(11) DEFAULT NULL,
  `uye_id` int(11) DEFAULT NULL,
  `sms_id` int(11) DEFAULT NULL,
  `sms_rehber_id` int(11) DEFAULT NULL,
  `sms_grup_id` int(11) DEFAULT NULL,
  `list_tel` varchar(20) COLLATE utf8_turkish_ci DEFAULT NULL,
  `s_iletim` tinyint(4) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sil` int(11) DEFAULT \'0\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sms_rehber`
--

CREATE TABLE `sms_rehber` (
  `id` int(11) NOT NULL,
  `kurum_id` int(11) DEFAULT NULL,
  `uye_id` int(11) DEFAULT NULL,
  `sr_isim` varchar(150) COLLATE utf8_turkish_ci DEFAULT NULL,
  `sr_tel` varchar(20) COLLATE utf8_turkish_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sil` int(11) NOT NULL DEFAULT \'0\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `sms`
--
ALTER TABLE `sms`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms_grup`
--
ALTER TABLE `sms_grup`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms_grup_tel`
--
ALTER TABLE `sms_grup_tel`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms_imza`
--
ALTER TABLE `sms_imza`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms_list`
--
ALTER TABLE `sms_list`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sms_rehber`
--
ALTER TABLE `sms_rehber`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `sms`
--
ALTER TABLE `sms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tablo için AUTO_INCREMENT değeri `sms_grup`
--
ALTER TABLE `sms_grup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tablo için AUTO_INCREMENT değeri `sms_grup_tel`
--
ALTER TABLE `sms_grup_tel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tablo için AUTO_INCREMENT değeri `sms_imza`
--
ALTER TABLE `sms_imza`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tablo için AUTO_INCREMENT değeri `sms_list`
--
ALTER TABLE `sms_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Tablo için AUTO_INCREMENT değeri `sms_rehber`
--
ALTER TABLE `sms_rehber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;';
        foreach ($sql as $item) {
            // DB::statement($item);
        }
        DB::statement($sql);

    }
}