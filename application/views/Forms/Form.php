<div class="page-header" style="margin: 30px 0px 0px;">
    <h1 style="margin-left: 15px;"><?=$name?></h1>
</div>

<form id="form-data" data-id="<?=$id?>" onkeypress="return event.keyCode != 13;">
    <?php if ($allow_geo):?>
        <input type="hidden" name="geo" class="geolocation" value="" />
    <?php endif;?>
    <div class="container" id="form-builder-container"></div>
    <?php if ($allow_attachment):?>
        <div id="attachments">
            <div class="clearfix"></div>
            <ul id="file-queue" class="list-unstyled">

            </ul>
        </div>
    <?php endif;?>
    <div class="col-xs-12">
        <input type="hidden" name="hidden">
        <?php if ($allow_attachment):?>
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>Add files...</span>
                <!-- The file input field used as target for the file upload widget -->
                <input id="form-upload" accept="image/*" type="file" name="files" multiple>
            </span>
        <?php endif;?>
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
            formbuilder.initForm('#form-builder-container',data.form, true);
            <?php if ($allow_attachment):?>
            var st = '';
            for (var i in data.attachments) {
                st +=
                    '<div class="col-xs-4 col-sm-3 col-md-2 col-lg-1" data-id="' + data.attachments[i] + '">' +
                        '<a href="javascript:;" class="remove-attachment"><span class="glyphicon glyphicon-remove text-danger"></span></a>' +
                        '<a href="<?=URL::base()?>download/attachment/' + data.attachments[i] + '"><img src="<?=URL::base()?>download/thumb/' + data.attachments[i] + '" /></a>' +
                    '</div>';
            }
            $('#attachments').prepend(st);
            <?php endif;?>
        });
    });
</script>