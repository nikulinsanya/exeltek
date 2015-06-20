<form action="" method="post">
<input type="hidden" name="dummy" />
<?php foreach ($columns as $id => $column):?>
<div class="checkbox">
<label>
    <input type="checkbox" name="<?=$id?>" <?=Arr::get($hidden, $id) ? 'checked' : ''?> />
    <?=$column['name']?>
</label>
</div>
<?php endforeach;?>
<input type="submit" value="Save" class="btn btn-success">
</form>