<link rel="stylesheet" href="<?php echo URL::asset('/') . 'css/all.css' ?>"/>
<meta name="csrf-token" content="<?php echo csrf_token() ?>">
<?php


echo $my->msg();
?>
<div class="alert alert-info">Sistemde Tanımlı SMS miktarınız : <?php echo $sms->smsMiktar(); ?><a href="/smsPaketiTanimla" style="float: right;" class="btn btn-primary btn-sm"> Paket Tanımla</a></div>
<div class="col-md-3">
    <strong>Gönderilen SMS'ler</strong>
    <div style=" float: right;"><?php echo $sms->yeni_sms(); ?></div>
    <div style="clear:both;"></div>
    <div style="clear:both;"></div>
    <?php echo $sms->gonderilen_smsler(); ?>
</div>
<div class="col-md-3">
    <strong>Tüm Gruplar</strong>
    <div style=" float: right;"><?php echo $sms->yeni_grup(); ?></div>
    <div style="clear:both;"></div>
    <?php echo $sms->gruplar(); ?>
</div>
<div class="col-md-3">
    <strong>Tüm Numaralar</strong>
    <div style=" float: right;"><?php echo $sms->yeni_numara(); ?></div>
    <div style="clear:both;"></div>
    <?php echo $sms->numaralar(); ?>
</div>
<div class="col-md-3">
    <strong>SMS İmza</strong>
    <div style=" float: right;"><?php echo $sms->yeni_imza(); ?></div>
    <div style="clear:both;"></div>
    <div class="bax" style="padding: 4px;">
        <div style="clear:both;"></div>
        <?php echo $sms->imza(); ?>
    </div>
</div>
<div style="clear:both;"></div>
<?php echo $sms->sms_modal() ?>

<script language="JavaScript" src="https://okuloncesievrak.com/js/all.js"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    <?php echo $sms->sms_script() ?>
</script>