<form class="auto-submit">
    <?php if (Group::current('allow_assign')):?>
        <div class="col-xs-3">
            <label class="control-label">Company: </label>
        </div>
        <div class="col-xs-9">
            <?=Form::select('company', array('' => 'All') + $companies, Arr::get($_GET, 'company'), array('class' => 'form-control'))?>

        </div>
        <div class="clearfix">&nbsp;</div>
    <?php endif;?>
    <div class="col-xs-3">
        <label class="control-label">FSA ID: </label>
    </div>
    <div class="col-xs-9">
        <?=Form::select('fsa', array('' => 'All') + $fsas, Arr::get($_GET, 'fsa'), array('class' => 'form-control'))?>

    </div>
    <div class="clearfix">&nbsp;</div>
    <div class="col-xs-3">
        <label class="control-label">FSAM ID: </label>
    </div>
    <div class="col-xs-9">
        <?=Form::select('fsam', array('' => 'All') + $fsam, Arr::get($_GET, 'fsam'), array('class' => 'form-control'))?>

    </div>
    <div class="clearfix">&nbsp;</div>
    <div class="col-xs-2">
        <label class="control-label">Start date: </label>
    </div>
    <div class="col-xs-4">
        <?=Form::input('start', Arr::get($_GET, 'start'), array('class' => 'form-control datepicker'))?>

    </div>
    <div class="col-xs-2">
        <label class="control-label">End date: </label>
    </div>
    <div class="col-xs-4">
        <?=Form::input('end', Arr::get($_GET, 'end'), array('class' => 'form-control datepicker'))?>

    </div>
    <div class="clearfix">&nbsp;</div>
</form>
<?php
$values = array(
    'total' => array_sum(array_column($fdas, 'total')),
    'tested' => array_sum(array_column($fdas, 'tested')),
    'built' => array_sum(array_column($fdas, 'built')),
);
?>
<div class="col-xs-12">
    <span class="label label-success">&nbsp;&nbsp;&nbsp;</span> - Built and tested<br/>
    <span class="label label-warning">&nbsp;&nbsp;&nbsp;</span> - Built<br/>
</div>
<div class="clearfix">&nbsp;</div>
<div class="col-xs-12">
    <div class="control-label">
        Overall progress: <?=$values['total']?> ticket(s).
    </div>
    <div class="progress">
        <?php if ($values['tested']): $tested = round($values['tested'] / $values['total'] * 100);?>
            <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$values['tested']?> (<?=$tested?>%) Built and tested">
                <span class="sr-only"><?=$values['tested']?> (<?=$tested?>%) Built and tested</span>
            </div>
        <?php endif;?>
        <?php if ($values['built']): $built = round($values['built'] / $values['total'] * 100);?>
            <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$values['built']?> (<?=$built?>%) Built">
                <span class="sr-only"><?=$values['built']?> (<?=$built?>%) Built</span>
            </div>
        <?php endif;?>
    </div>
</div>


<?php foreach ($fdas as $key => $values) if ($values['total']):?>
    <div class="col-xs-12">
        <div class="control-label">
            <?=$key?>: <?=$values['total']?> ticket(s).
        </div>
        <div class="progress">
            <?php if ($values['tested']): $tested = round($values['tested'] / $values['total'] * 100);?>
                <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$values['tested']?> (<?=$tested?>%) Built and tested">
                    <span class="sr-only"><?=$values['tested']?> (<?=$tested?>%) Built and tested</span>
                </div>
            <?php endif;?>
            <?php if ($values['built']): $built = round($values['built'] / $values['total'] * 100);?>
                <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$values['built']?> (<?=$built?>%) Built">
                    <span class="sr-only"><?=$values['built']?> (<?=$built?>%) Built</span>
                </div>
            <?php endif;?>
        </div>
    </div>
<?php endif;?>