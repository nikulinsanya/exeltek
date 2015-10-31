<div class="col-xs-12">
    <label class="control-label"></label>
    <select id="form-reports" class="form-control">
        <option value="">Please, select report type</option>
        <?php foreach ($reports as $id => $name):?>
            <option value="<?=$id?>"><?=HTML::chars($name)?></option>
        <?php endforeach;?>
    </select>
</div>
<div class="col-xs-12">
<div class="well well-lg" id="attachments" style="margin-top: 10px;">

</div>
</div>


<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/forms/reports.js"></script>