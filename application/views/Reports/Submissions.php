<?php $week = strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time());?>
<form action="" method="get" class="auto-submit report-form-container">

    <div class="filter-info-container">
        <label class="date-range-label">Date range: </label>
        <span class="date-range-container">
            <div class="daterange" class="pull-right" data-start="start" data-end="end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                <span></span> <b class="caret"></b>
            </div>
        </span>

        <div class="clearfix">&nbsp;</div>


        <label class="date-range-label">Date range(Approved) :</label>
         <span class="date-range-container">
            <div class="daterange" class="pull-right"  data-start="app-start" data-end="app-end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                <span></span> <b class="caret"></b>
            </div>
        </span>


        <div class="clearfix">&nbsp;</div>
        <?php if (Group::current('allow_assign')):?>
        <label class="date-range-label filter-select-label">Contractors :</label>
        <span class="filter-select-container">
            <select name="company" class="selectize">
                <option value="">All contractors</option>
                <?php foreach ($companies as $key => $value):?>
                <option value="<?=$key?>" <?=$key == Arr::get($_GET, 'company') ? 'selected' : ''?>><?=$value?></option>
                <?php endforeach;?>
            </select>
        </span>
        <?php endif;?>
    </div>



<?=Form::hidden('start', Arr::get($_GET, 'start', date('d-m-Y', $week)), array('class' => 'form-control datepicker', 'placeholder' => 'Start date', 'id' => 'start'))?>
<?=Form::hidden('end', Arr::get($_GET, 'end', date('d-m-Y')), array('class' => 'form-control datepicker', 'placeholder' => 'End date', 'id' => 'end'))?>

<?=Form::hidden('app-start', Arr::get($_GET, 'app-start'), array('class' => 'form-control datepicker', 'placeholder' => 'Start date (Approved)', 'id' => 'app-start'))?>
<?=Form::hidden('app-end', Arr::get($_GET, 'app-end'), array('class' => 'form-control datepicker', 'placeholder' => 'End date (Approved)', 'id' => 'app-end'))?>
</form>
<h2>Total <?=count($submissions)?> job(s) found:</h2>
<div class="upload-buttons text-right">
<a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
<a href="?export2&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-primary"><span class="glyphicon glyphicon-export"></span> Export grouped</a>
</div>
<table class="table table-hover">
    <?php foreach ($submissions as $job => $list):?>
    <tr>
        <th>Ticket ID</th>
        <th>Submission date</th>
        <th>Approval date</th>
        <th>User</th>
        <?php if (Group::current('allow_assign')):?><th>Company</th><?php endif;?>
        <th>Column</th>
        <th>Value</th>
    </tr>
    <?php foreach ($list as $submission): $key = substr($submission['key'], 5); $status = Arr::get($submission, 'active', 0);?>
    <tr class="<?=$status > 0 ? 'bg-warning' : ($status < 0 ? 'bg-success' : 'bg-danger')?>">
        <td><a href="<?=URL::base()?>search/view/<?=$job?>"><?=$job?></a></td>
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
<div class="upload-buttons text-right">
    <a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
    <a href="?export2&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-primary"><span class="glyphicon glyphicon-export"></span> Export grouped</a>
</div>