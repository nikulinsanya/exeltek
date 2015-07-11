<form action="" method="post">
<?php if (isset($_POST['job'])):?>
    <?php foreach ($_POST['job'] as $key => $value):?>
        <input type="hidden" name="job[<?=$key?>]" value="1" />
    <?php endforeach;?>
    <div class="col-xs-12">
        <h3 class="text-warning"><?=count($_POST['job'])?> jobs selected.</h3>
    </div>
<?php else:?>
    <div class="col-xs-12">
        <?=Form::select('region', array('' => 'Please, select region') + $regions, NULL, array('class' => 'form-control', 'id' => 'region-input'))?>
    </div>
<?php endif;?>
<div class="btn-group col-xs-12" id="csv-selector" data-toggle="buttons">
    <label class="btn btn-info">
        <input type="radio" class="export-group" name="csv" autocomplete="off" value="all" /> All
    </label>
    <label class="btn btn-info">
        <input type="radio" class="export-group" name="csv" autocomplete="off" value="csv" /> CSV
    </label>
    <label class="btn btn-info">
        <input type="radio" class="export-group" name="csv" autocomplete="off" value="non-csv" /> Non-CSV
    </label>
    <label class="btn btn-info">
        <input type="radio" class="export-group" name="csv" autocomplete="off" value="none" /> None (IDs only)
    </label>
</div>
<div class="btn-group col-xs-12" id="group-selector" data-toggle="buttons">
    <?php foreach ($groups as $id => $name):?>
        <label class="btn btn-primary">
            <input type="radio" class="export-group" name="group" autocomplete="off" value="<?=$id?>" /> <?=$name?>
        </label>
    <?php endforeach;?>
    <label class="btn btn-primary">
        <input type="radio" class="export-group" name="group" autocomplete="off" value="0" /> Custom
    </label>
</div>
<div class="clearfix">&nbsp;</div>
<div class="col-xs-12">
    <input type="hidden" name="group" id="group-input" value=""/>
    <input type="hidden" name="csv" id="csv-input" value="" />
    <button class="btn btn-success disabled" id="export-button" type="submit">Export</button>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-striped hidden" id="columns">
    <?php foreach (Columns::get_all() as $key => $name):?>
    <tr><td>
        <label class="control-label">
            <input type="checkbox" data-csv="<?=Columns::get_csv($key) ? 1 : 0?>" name="columns[<?=$key?>]" />
            <?=$name?>
        </label>
    </td></tr>
    <?php endforeach;?>
</table>
</form>
