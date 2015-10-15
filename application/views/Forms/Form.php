<div class="page-header" style="margin: 30px 0px 0px;">
    <h1 style="margin-left: 15px;"><?=$name?></h1>
</div>

<form id="form-data">
    <div class="container" id="form-builder-container"></div>
    <div class="col-xs-12">
        <button type="button" id="form-save" class="btn btn-success">Save</button>
    </div>
</form>

<link href="<?= URL::base() ?>css/forms/form.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/lib/signature_pad.min.js"></script>

<script src="<?= URL::base() ?>js/forms/form.js"></script>

<script type="application/javascript">
    $(document).on('ready', function () {
        $.get(utils.baseUrl() + 'form/fill?<?=$id ? 'id=' . $id : 'form=' . $_GET['form']?>', function(data) {
            form.init($('#form-builder-container'), data, false);
        });
    });
</script>