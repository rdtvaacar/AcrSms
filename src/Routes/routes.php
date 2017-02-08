<?php

Route::get('/acr_sms', function () {
    return AcrSms::index();
});
/*Route::post('acr_sms_grup_olustur', 'AcrSms@grup_olustur');
Route::post('acr_sms_grup_duzenle', 'AcrSms@sms_grup_duzenle');
Route::post('acr_sms_grup_sil', 'AcrSms@grup_sil');*/

Route::post('/acr_sms_grup_olustur', function () {
    return AcrSms::grup_olustur();
});
Route::post('/acr_sms_grup_duzenle', function () {
    return AcrSms::sms_grup_duzenle();
});
Route::post('/acr_sms_grup_sil', function () {
    return AcrSms::grup_sil();
});

/*Route::post('acr_sms_rehber_kaydet', 'AcrSms@rehber_kaydet');
Route::post('acr_sms_rehber_duzenle', 'AcrSms@sms_rehber_duzenle');
Route::post('acr_sms_rehber_sil', 'AcrSms@rehber_sil');*/
Route::post('/acr_sms_rehber_kaydet', function () {
    return AcrSms::rehber_kaydet();
});
Route::post('/acr_sms_rehber_duzenle', function () {
    return AcrSms::sms_rehber_duzenle();
});
Route::post('/acr_sms_rehber_sil', function () {
    return AcrSms::rehber_sil();
});

/*Route::post('acr_sms_form', 'AcrSms@yeni_sms_gonder');
Route::post('acr_sms_gonder', 'AcrSms@sms_form_gonder');*/

Route::post('/acr_sms_form', function () {
    return AcrSms::yeni_sms_gonder();
});
Route::post('/acr_sms_gonder', function () {
    return AcrSms::sms_form_gonder();
});
/*Route::post('acr_sms_imza_duzenle', 'AcrSms@imza_duzenle');
Route::post('acr_sms_imza_form', 'AcrSms@imza_form');
Route::post('acr_sms_imza_kaydet', 'AcrSms@imza_kaydet');
Route::post('acr_gonderilen_sms_goster', 'AcrSms@gonderilen_sms_goster');
Route::post('acr_numaralardan_grup_olustur', 'AcrSms@numaralardan_grup_olustur');*/
Route::post('/acr_sms_imza_duzenle', function () {
    return AcrSms::imza_duzenle();
});
Route::post('/acr_sms_imza_form', function () {
    return AcrSms::imza_form();
});
Route::post('/acr_sms_imza_kaydet', function () {
    return AcrSms::imza_kaydet();
});
Route::post('/acr_gonderilen_sms_goster', function () {
    return AcrSms::gonderilen_sms_goster();
});
Route::post('/acr_numaralardan_grup_olustur', function () {
    return AcrSms::numaralardan_grup_olustur();
});