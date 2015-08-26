<form class="auto-submit" method="get" action="">
    <?=Form::select('company', array('' => 'Please, select company') + $companies, Arr::get($_GET, 'company'), array('class' => 'form-control'))?>
</form>
<div class="clearfix">&nbsp;</div>
<?php $company = Arr::get($_GET, 'company'); if ($company):?>
<table class="table table-striped" data-url="<?=URL::base()?>security/rates/save">
    <?php foreach ($columns as $id => $column):?>
    <tr class="text-center">
        <td class="col-xs-4"><?=HTML::chars($column)?></td>
        <td class="col-xs-8"><input data-column="<?=$id?>" data-company="<?=$company?>" type="text" class="form-control rate-change" value="<?=Arr::get($rates, $id)?>" /></td>
    </tr>
    <?php endforeach;?>
</table>
<?php endif;?>