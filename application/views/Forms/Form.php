<div class="page-header" style="margin: 30px 0px 0px;">
    <h1 style="margin-left: 15px;"><?=$name?></h1>
</div>

<form id="form-data">
    <?php if ($allow_geo):?>
        <input type="hidden" name="geo" class="geolocation" value="" />
    <?php endif;?>
    <div class="container" id="form-builder-container"></div>
    <div class="col-xs-12">
        <input type="hidden" name="hidden">
        <button type="button" class="btn btn-success form-save">Save</button>
        <button type="button" class="btn btn-info form-save">Save & Print</button>
    </div>
</form>

<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/lib/signature_pad.min.js"></script>
<script src="<?= URL::base() ?>js/lib/jquery/colResizable-1.5.min.js"></script>
<script src="<?= URL::base() ?>js/forms/formbuilder.js"></script>




<script type="application/javascript">
    $(document).on('ready', function () {
        $.get(utils.baseUrl() + 'form/fill?load&<?=$id ? 'id=' . $id : 'form=' . $_GET['form']?>', function(data) {
            formbuilder.initForm('#form-builder-container',data, true);
        });
    });
</script>