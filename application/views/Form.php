
<div class="page-header" style="margin: 30px 0px 0px;">
    <h1 style="margin-left: 15px;">Form builder</h1>
</div>

<div class="container" id="formBuildeContainer">

</div>
<div id="newFormContainer"></div>

<link href="<?=URL::base()?>css/forms/form.css" rel="stylesheet">
<script src="<?=URL::base()?>js/lib/signature_pad.min.js"></script>

<script src="<?=URL::base()?>js/forms/form.js"></script>

<script type="application/javascript">
    $(document).on('ready',function(){
        var json = $.parseJSON('<?=$json?>');
        form.init($('#formBuildeContainer'),json);
    });
</script>