<div class="col-xs-12">
    <label class="control-label"></label>
    <select id="form-reports" class="form-control">
        <option value="">Please, select report type</option>
        <?php foreach ($reports as $id => $name):?>
            <option value="<?=$id?>"><?=HTML::chars($name)?></option>
        <?php endforeach;?>
    </select>
</div>
<table class="table">

</table>