<?php $week = strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time());?>
<form action="" method="get" class="auto-submit">
<div class="col-xs-4">
<?=Form::input('start', Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month'))), array('class' => 'form-control datepicker', 'placeholder' => 'Start date', 'id' => 'start'))?>
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
<div class="col-xs-4">
<?=Form::input('fin-start', Arr::get($_GET, 'fin-start'), array('class' => 'form-control datepicker', 'placeholder' => 'Start date (Financial)', 'id' => 'fin-start'))?>
</div>
<div class="col-xs-4">
<?=Form::input('fin-end', Arr::get($_GET, 'fin-end'), array('class' => 'form-control datepicker', 'placeholder' => 'End date (Financial)', 'id' => 'fin-end'))?>
</div>
<div class="col-xs-4">
    <button type="button" class="btn btn-info date-range" data-suffix="fin" data-start="<?=date('d-m-Y', strtotime('first day of this month'))?>" data-end="<?=date('d-m-Y')?>">This month</button>
    <button type="button" class="btn btn-info date-range" data-suffix="fin" data-start="<?=date('d-m-Y', strtotime('first day of last month'))?>" data-end="<?=date('d-m-Y', strtotime('last day of last month'))?>">Last month</button>
    <button type="button" class="btn btn-info date-range" data-suffix="fin" data-start="<?=date('d-m-Y', $week)?>" data-end="<?=date('d-m-Y')?>">This week</button>
    <button type="button" class="btn btn-info date-range" data-suffix="fin" data-start="<?=date('d-m-Y', strtotime('-1 week', $week))?>" data-end="<?=date('d-m-Y', strtotime('-1 day', $week))?>">Last week</button>
    <button type="button" class="btn btn-danger date-range" data-suffix="fin" data-start="" data-end="">Clear</button>
</div>
<div class="clearfix">&nbsp;</div>
<?php if (Group::current('allow_assign')):?>
<div class="col-xs-8">
<select name="company" class="form-control">
    <option value="">All contractors</option>
    <?php foreach ($companies as $key => $value):?>
    <option value="<?=$key?>" <?=$key == Arr::get($_GET, 'company') ? 'selected' : ''?>><?=$value?></option>
    <?php endforeach;?>
</select>
</div>
<div class="col-xs-4">
    <label>
        <input type="checkbox" class="discrepancy" />
        Discrepancies only
    </label>
</div>
<div class="clearfix">&nbsp;</div>
<?php endif;?>
</form>
<div>
<h3 class="pull-left">Total found: <?=count($submissions)?> ticket(s)</h3>
<?php if ($approve_all):?>
    <a href="<?=URL::base() . Request::current()->uri() . URL::query(array('approve' => 1))?>" class="pull-right btn btn-success">Approve all</a>
<?php endif;?>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-hover" <?=Group::current('allow_assign') ? 'data-url="' . URL::base() . 'reports/financial/approve"' : ''?>>
    <?php foreach ($submissions as $job => $list):?>
    <tr class="<?=isset($discrepancies[$job])? 'discrepancy' : ''?>">
        <th colspan="<?=Group::current('allow_assign') ? 12 : 9?>"><a href="<?=URL::base()?>search/view/<?=$job?>"><?=$job?></a></th>
    </tr>
    <tr class="<?=isset($discrepancies[$job])? 'discrepancy' : ''?>">
        <th>Submission date</th>
        <th>Approval date</th>
        <th>Financial date</th>
        <th>User</th>
        <?php if (Group::current('allow_assign')):?><th>Company</th><?php endif;?>
        <th>Column</th>
        <th>Value</th>
        <?php if (Group::current('allow_assign')):?><th>Current value</th><?php endif;?>
        <th>Paid value</th>
        <th>Max value</th>
        <th>Rate</th>
        <?php if (Group::current('allow_assign')):?><th>&nbsp;</th><?php endif;?>
    </tr>
    <?php foreach ($list as $submission): $key = substr($submission['key'], 5);?>
    <tr class="submission <?=Group::current('allow_assign') && Arr::path($jobs, $job . '.' . $submission['key']) != $submission['value'] ? 'bg-danger' : (Arr::get($submission, 'financial_time') ? 'bg-success' : 'bg-warning')?>" data-id="<?=$job?>">
        <td><?=date('d-m-Y H:i', $submission['update_time'])?></td>
        <td><?=Arr::get($submission, 'process_time') ? date('d-m-Y H:i', $submission['process_time']) : ''?></td>
        <td class="time"><?=Arr::get($submission, 'financial_time') ? date('d-m-Y H:i', $submission['financial_time']) : ''?></td>
        <td><?=User::get($submission['user_id'], 'login')?></td>
        <?php if (Group::current('allow_assign')):?><td><?=Arr::get($companies, User::get($submission['user_id'], 'company_id'), 'Unknown')?></td><?php endif;?>
        <td><?=Columns::get_name($key)?></td>
        <td><?=$submission['value']?></td>
        <?php if (Group::current('allow_assign')):?><td><?=Arr::path($jobs, $job . '.' . $submission['key'])?></td><?php endif;?>
        <td class="paid"><?=Arr::get($submission, 'paid')?></td>
        <td><?=floatval(Arr::get($columns, $key))?></td>
        <td class="rate"><?=Arr::get($submission, 'rate') ? number_format($submission['rate'], 2) : Arr::path($rates, array(User::get($submission['user_id'], 'company_id'), $key), '')?></td>
        <?php if (Group::current('allow_assign')):?>
        <td>
            <?php if (!$submission['financial_time'] && Arr::path($rates, array(User::get($submission['user_id'], 'company_id'), $key), '')):?>
            <a href="javascript:;" data-id="<?=$submission['_id']?>" data-value="<?=min(floatval(Arr::get($columns, $key)), floatval($submission['value']) ? : 1)?>" data-max="<?=floatval(Arr::get($columns, $key))?>" class="btn btn-success approve-financial">Approve</a>
            <?php else: echo '&nbsp;'; endif;?>
        </td>
        <?php endif;?>
    </tr>
    <?php endforeach;?>
    <?php endforeach;?>
</table>
<a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month')))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="pull-right btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
<a href="<?=URL::query($_GET + array('excel' => ''))?>" class="pull-right btn btn-success"><span class="glyphicon glyphicon-export"></span> Export to Excel</a>