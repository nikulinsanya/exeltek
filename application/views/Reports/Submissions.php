<?php $week = strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time());?>
<form action="" method="get" class="auto-submit">
<div class="col-xs-4">
<?=Form::input('start', Arr::get($_GET, 'start', date('d-m-Y', $week)), array('class' => 'form-control datepicker', 'placeholder' => 'Start date', 'id' => 'start'))?>
</div>
<div class="col-xs-4">
<?=Form::input('end', Arr::get($_GET, 'end', date('d-m-Y')), array('class' => 'form-control datepicker', 'placeholder' => 'End date', 'id' => 'end'))?>
</div>
<div class="col-xs-4">
    <button type="button" class="btn btn-info date-range" data-suffix="" data-start="<?=date('d-m-Y', strtotime('first day of this month'))?>" data-end="<?=date('d-m-Y')?>">This month</button>
    <button type="button" class="btn btn-info date-range" data-suffix="" data-start="<?=date('d-m-Y', strtotime('first day of last month'))?>" data-end="<?=date('d-m-Y', strtotime('last day of last month'))?>">Last month</button>
    <button type="button" class="btn btn-info date-range" data-suffix="" data-start="<?=date('d-m-Y', $week)?>" data-end="<?=date('d-m-Y')?>">This week</button>
    <button type="button" class="btn btn-info date-range" data-suffix="" data-start="<?=date('d-m-Y', strtotime('-1 week', $week))?>" data-end="<?=date('d-m-Y', strtotime('-1 day', $week))?>">Last week</button>
</div>
<div class="clearfix">&nbsp;</div>
<div class="col-xs-4">
<?=Form::input('app-start', Arr::get($_GET, 'app-start'), array('class' => 'form-control datepicker', 'placeholder' => 'Start date (Approved)', 'id' => 'app-start'))?>
</div>
<div class="col-xs-4">
<?=Form::input('app-end', Arr::get($_GET, 'app-end'), array('class' => 'form-control datepicker', 'placeholder' => 'End date (Approved)', 'id' => 'app-end'))?>
</div>
<div class="col-xs-4">
    <button type="button" class="btn btn-info date-range" data-suffix="app" data-start="<?=date('d-m-Y', strtotime('first day of this month'))?>" data-end="<?=date('d-m-Y')?>">This month</button>
    <button type="button" class="btn btn-info date-range" data-suffix="app" data-start="<?=date('d-m-Y', strtotime('first day of last month'))?>" data-end="<?=date('d-m-Y', strtotime('last day of last month'))?>">Last month</button>
    <button type="button" class="btn btn-info date-range" data-suffix="app" data-start="<?=date('d-m-Y', $week)?>" data-end="<?=date('d-m-Y')?>">This week</button>
    <button type="button" class="btn btn-info date-range" data-suffix="app" data-start="<?=date('d-m-Y', strtotime('-1 week', $week))?>" data-end="<?=date('d-m-Y', strtotime('-1 day', $week))?>">Last week</button>
    <button type="button" class="btn btn-danger date-range" data-suffix="app" data-start="" data-end="">Clear</button>
</div>
<div class="clearfix">&nbsp;</div>
<?php if (Group::current('allow_assign')):?>
<div class="col-xs-12">
<select name="company" class="form-control">
    <option value="">All contractors</option>
    <?php foreach ($companies as $key => $value):?>
    <option value="<?=$key?>" <?=$key == Arr::get($_GET, 'company') ? 'selected' : ''?>><?=$value?></option>
    <?php endforeach;?>
</select>
</div>
<div class="clearfix">&nbsp;</div>
<?php endif;?>
</form>
<h2>Total <?=count($submissions)?> job(s) found:</h2>
<a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month')))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="pull-right btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
<a href="?export2&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month')))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="pull-right btn btn-primary"><span class="glyphicon glyphicon-export"></span> Export grouped</a>
<table class="table table-hover">
    <?php foreach ($submissions as $job => $list):?>
    <tr>
        <th colspan="<?=Group::current('allow_assign') ? 6 : 5?>"><a href="<?=URL::base()?>search/view/<?=$job?>"><?=$job?></a></th>
    </tr>
    <tr>
        <th>Submission date</th>
        <th>Approval date</th>
        <th>User</th>
        <?php if (Group::current('allow_assign')):?><th>Company</th><?php endif;?>
        <th>Column</th>
        <th>Value</th>
    </tr>
    <?php foreach ($list as $submission): $key = substr($submission['key'], 5); $status = Arr::get($submission, 'active', 0);?>
    <tr class="<?=$status > 0 ? 'bg-warning' : ($status < 0 ? 'bg-success' : 'bg-danger')?>">
        <td><?=date('d-m-Y H:i', $submission['update_time'])?></td>
        <td><?=isset($submission['process_time']) ? date('d-m-Y H:i', $submission['process_time']) : ''?></td>
        <td><?=User::get($submission['user_id'], 'login')?></td>
        <?php if (Group::current('allow_assign')):?><td><?=Arr::get($companies, User::get($submission['user_id'], 'company_id'), 'Unknown')?></td><?php endif;?>
        <td><?=Columns::get_name($key)?></td>
        <td class="<?=strlen(Columns::output($submission['value'], Columns::get_type($key))) > 100 ? 'shorten' : ''?>"><?=Columns::output($submission['value'], Columns::get_type($key))?></td>
    </tr>
    <?php endforeach;?>
    <?php endforeach;?>
</table>
<a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month')))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="pull-right btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
<a href="?export2&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month')))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="pull-right btn btn-primary"><span class="glyphicon glyphicon-export"></span> Export grouped</a>