<form class="auto-submit" method="get" action="">
    <?=Form::select('company', array('' => 'Please, select company', '0' => 'Client rates') + $companies, Arr::get($_GET, 'company'), array('class' => 'form-control'))?>
    <?=Form::select('region', array('0' => 'All') + $regions, Arr::get($_GET, 'region'), array('class' => 'form-control'))?>
</form>
<div class="clearfix">&nbsp;</div>
<?php $company = Arr::get($_GET, 'company'); $region = intval(Arr::get($_GET, 'region')); if ($company || $company === '0'):?>
<table class="table table-striped" data-url="<?=URL::base()?>security/rates/save">
    <?php foreach ($columns as $id => $column):?>
    <tr class="text-center">
        <td class="col-xs-4"><?=HTML::chars($column)?></td>
        <td class="col-xs-8"><input data-column="<?=$id?>" data-region="<?=$region?>" data-company="<?=$company?>" type="text" class="form-control rate-change" value="<?=Arr::get($rates, $id)?>" /></td>
    </tr>
    <?php endforeach;?>
</table>
<?php endif;?>