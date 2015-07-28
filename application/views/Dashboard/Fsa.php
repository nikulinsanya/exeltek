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
<div class="col-xs-12">
    <span class="label label-success">&nbsp;&nbsp;&nbsp;</span> - Built and tested<br/>
    <span class="label label-warning">&nbsp;&nbsp;&nbsp;</span> - Built<br/>
</div>
<div class="clearfix">&nbsp;</div>

<div class="col-xs-12 progress-row">
    <div class="control-label">
        <?php $tested = $total['tested'] ? round($total['tested'] / $total['total'] * 100): 0;?>
        <?php $built = $total['built'] ? round($total['built'] / $total['total'] * 100) : 0;?>

        <strong>
            Overall progress: <?=$total['total']?> <?=$total['total']>1 ? 'tickets' : 'ticket'?>
        </strong>
        <div class="progress-label">
            Tested: <span class="progress-bar-success"><?=$total['tested']?></span>, Built: <span class="progress-bar-warning"><?=$total['built']?></span>
        </div>
    </div>
    <div class="progress">
        <?php if ($total['tested']):?>
            <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$total['tested']?> (<?=$tested?>%) Built and tested">
                <?=$tested?>%
            </div>
        <?php endif;?>
        <?php if ($total['built']):?>
            <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$total['built']?> (<?=$built?>%) Built">
                <?=$built?>%
            </div>
        <?php endif;?>
    </div>
</div>


<?php foreach ($fsam as $key => $values) if ($key != 'total'): $value = $values['total']; $value['tested'] = Arr::get($value, 'tested'); $value['built'] = Arr::get($value, 'built');?>
    <div class="clearfix">&nbsp;</div>
    <div class="col-xs-12 progress-row"  data-toggle="collapse" data-target="#fsa-<?=$key?>">
        <?php $tested = $value['tested'] ? round($value['tested'] / $value['total'] * 100): 0;?>
        <?php $built = $value['built'] ? round($value['built'] / $value['total'] * 100) : 0;?>

        <div class="control-label">
            <strong>
                <a href="<?=URL::base()?>?columns[]=12&actions[]=2&values[]=<?=$key?>"><?=$key?>: <?=$value['total']?> <?=$value['total']>1 ? 'tickets' : 'ticket'?></a>
            </strong>
            <div class="progress-label">
                <?php if ($value['tested']):?>
                    Tested: <span class="progress-bar-success"><?=$value['tested']?></span>
                <?php endif;?>
                <?php if ($value['built']):?>
                   , Built: <span class="progress-bar-warning"><?=$value['built']?></span>
                <?php endif;?>
            </div>
        </div>
        <div class="progress">
                <?php if ($value['tested']):?>
                    <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$value['tested']?> (<?=$tested?>%) Built and tested">
                        <?=$tested?>%
                    </div>
                <?php endif;?>
                <?php if ($value['built']):?>
                    <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$value['built']?> (<?=$built?>%) Built">
                        <?=$built?>%
                    </div>
                <?php endif;?>
        </div>
    </div>
    <div class="collapse" id="fsa-<?=$key?>">
        <?php $fsa = $values; foreach ($fsa as $key => $values) if ($key != 'total'): $value = $values['total']; $value['tested'] = Arr::get($value, 'tested'); $value['built'] = Arr::get($value, 'built');?>
            <div class="col-xs-11 col-xs-offset-1 progress-row" data-toggle="collapse" data-target="#fsam-<?=$key?>">
                <?php $tested = $value['tested'] ? round($value['tested'] / $value['total'] * 100): 0;?>
                <?php $built = $value['built'] ? round($value['built'] / $value['total'] * 100) : 0;?>

                <div class="control-label">
                    <strong>
                        <a href="<?=URL::base()?>?columns[]=13&actions[]=2&values[]=<?=$key?>"><?=$key?>: <?=$value['total']?> <?=$value['total']>1 ? 'tickets' : 'ticket'?></a>
                    </strong>
                    <div class="progress-label">
                        <?php if ($value['tested']):?>
                            Tested: <span class="progress-bar-success"><?=$value['tested']?></span>
                        <?php endif;?>
                        <?php if ($value['built']):?>
                            , Built: <span class="progress-bar-warning"><?=$value['built']?></span>
                        <?php endif;?>
                    </div>
                </div>
                <div class="progress">
                    <?php if ($value['tested']):?>
                        <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$value['tested']?> (<?=$tested?>%) Built and tested">
                            <?=$tested?>%
                        </div>
                    <?php endif;?>
                    <?php if ($value['built']):?>
                        <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$value['built']?> (<?=$built?>%) Built">
                            <?=$built?>%
                        </div>
                    <?php endif;?>
                </div>
            </div>
            <div class="collapse" id="fsam-<?=$key?>">
                <?php $fsam = $values; foreach ($fsam as $key => $value) if ($key != 'total'): $value['tested'] = Arr::get($value, 'tested'); $value['built'] = Arr::get($value, 'built');?>
                    <div class="col-xs-10 col-xs-offset-2 progress-row">
                        <?php $tested = $value['tested'] ? round($value['tested'] / $value['total'] * 100): 0;?>
                        <?php $built = $value['built'] ? round($value['built'] / $value['total'] * 100) : 0;?>

                        <div class="control-label">
                            <strong>
                                <a href="<?=URL::base()?>?columns[]=14&actions[]=2&values[]=<?=$key?>"><?=$key?>: <?=$value['total']?> <?=$value['total']>1 ? 'tickets' : 'ticket'?></a>
                            </strong>
                            <div class="progress-label">
                                <?php if ($value['tested']):?>
                                    Tested: <span class="progress-bar-success"><?=$value['tested']?></span>
                                <?php endif;?>
                                <?php if ($value['built']):?>
                                    , Built: <span class="progress-bar-warning"><?=$value['built']?></span>
                                <?php endif;?>
                            </div>
                        </div>
                        <div class="progress">
                            <?php if ($value['tested']):?>
                                <div class="progress-bar progress-bar-success" style="width: <?=$tested > 1 ? $tested : 1?>%" title="<?=$value['tested']?> (<?=$tested?>%) Built and tested">
                                    <?=$tested?>%
                                </div>
                            <?php endif;?>
                            <?php if ($value['built']):?>
                                <div class="progress-bar progress-bar-warning" style="width: <?=$built > 1 ? $built: 1?>%" title="<?=$value['built']?> (<?=$built?>%) Built">
                                    <?=$built?>%
                                </div>
                            <?php endif;?>
                        </div>
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
    </div>
<?php endif;?>