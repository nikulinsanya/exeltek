<form class="auto-submit dashboard-form">
    <?php if (Group::current('allow_assign')):?>
        <div class="col-xs-2">
            <label class="control-label">Company: </label>
        </div>
        <div class="col-xs-3">
            <?=Form::select('company', array('' => 'All') + $companies, Arr::get($_GET, 'company'), array('class' => 'form-control'))?>

        </div>
        <div class="clearfix">&nbsp;</div>
    <?php endif;?>
    <div class="col-xs-2">
        <label class="control-label">Start date: </label>
    </div>
    <div class="col-xs-3">
        <?=Form::input('start', Arr::get($_GET, 'start'), array('class' => 'form-control datepicker'))?>

    </div>
    <div class="clearfix">&nbsp;</div>
    <div class="col-xs-2">
        <label class="control-label">End date: </label>
    </div>
    <div class="col-xs-3">
        <?=Form::input('end', Arr::get($_GET, 'end'), array('class' => 'form-control datepicker'))?>

    </div>
    <div class="clearfix">&nbsp;</div>
</form>
<?php
$values = array(
    'total' => array_sum(array_column($fsa, 'total')),
    'tested' => array_sum(array_column($fsa, 'tested')),
    'built' => array_sum(array_column($fsa, 'built')),
);
?>
<div class="col-xs-12">
    <span class="label label-success">&nbsp;&nbsp;&nbsp;</span> - Built and tested<br/>
    <span class="label label-warning">&nbsp;&nbsp;&nbsp;</span> - Built<br/>
</div>
<div class="clearfix">&nbsp;</div>

<div class="col-xs-12 progress-row">
    <div class="control-label">
        <?php $tested = $values['tested'] ? round($values['tested'] / $values['total'] * 100): 0;?>
        <?php $built = $values['built'] ? round($values['built'] / $values['total'] * 100) : 0;?>

        <strong>
            Overall progress: <?=$values['total']?> <?=$values['total']>1 ? 'tickets' : 'ticket'?>
        </strong>
        <div class="progress-label">
            Tested: <span class="progress-bar-success"><?=$values['tested']?></span>, Built: <span class="progress-bar-warning"><?=$values['built']?></span>
        </div>
    </div>
    <div class="progress">
        <?php if ($values['tested']):?>
            <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$values['tested']?> (<?=$tested?>%) Built and tested">
                <?=$tested?>%
            </div>
        <?php endif;?>
        <?php if ($values['built']):?>
            <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$values['built']?> (<?=$built?>%) Built">
                <?=$built?>%
            </div>
        <?php endif;?>
    </div>
</div>


<?php foreach ($fsa as $key => $values) if ($values['total']):?>
    <div class="col-xs-12 progress-row">
        <?php $tested = $values['tested'] ? round($values['tested'] / $values['total'] * 100): 0;?>
        <?php $built = $values['built'] ? round($values['built'] / $values['total'] * 100) : 0;?>

        <div class="control-label">
            <strong>
                <a href="<?=URL::base()?>dashboard/fsam<?=URL::query(array('fsa' => $key))?>"><?=$key?>: <?=$values['total']?> <?=$values['total']>1 ? 'tickets' : 'ticket'?></a>
            </strong>
            <div class="progress-label">
                <?php if ($values['tested']):?>
                    Tested: <span class="progress-bar-success"><?=$values['tested']?></span>
                <?php endif;?>
                <?php if ($values['built']):?>
                   , Built: <span class="progress-bar-warning"><?=$values['built']?></span>
                <?php endif;?>
            </div>
        </div>
        <div class="progress">
                <?php if ($values['tested']):?>
                    <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 0?>%" title="<?=$values['tested']?> (<?=$tested?>%) Built and tested">
                        <?=$tested?>%
                    </div>
                <?php endif;?>
                <?php if ($values['built']):?>
                    <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 0?>%" title="<?=$values['built']?> (<?=$built?>%) Built">
                        <?=$built?>%
                    </div>
                <?php endif;?>
        </div>
    </div>
<?php endif;?>