<?php $week = strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time());?>
<form action="" method="get" class="">
    <div class="financial-filter-container">
            <div class="col-xs-12 col-md-6">
            <label class="date-range-label">Date range: </label>
            <span class="date-range-container">
                <div class="daterange" class="pull-right" data-start="start" data-end="end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    <span></span> <b class="caret"></b>
                </div>
            </span>
            </div>
            <div class="col-xs-12 col-md-6">

            <label class="date-range-label">Date range(Approved) :</label>
             <span class="date-range-container">
                <div class="daterange" class="pull-right"  data-start="app-start" data-end="app-end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    <span></span> <b class="caret"></b>
                </div>
            </span>
            </div>
            <div class="col-xs-12 col-md-6">

            <label class="date-range-label">Date range(Financial) :</label>
             <span class="date-range-container">
                <div class="daterange" class="pull-right"  data-start="fin-start" data-end="fin-end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    <span></span> <b class="caret"></b>
                </div>
            </span>
            </div>

            <div class="col-xs-12 col-md-6">
                <label class="date-range-label filter-select-label">FSA: </label>
                    <span class="filter-select-container" data-fsa-selected="<?=isset($_GET['fsa']) ? implode(',',Arr::get($_GET, 'fsa')):''?>">
                    <?=Form::select('fsa[]', [], NULL, array('class' => 'fsa-filter multiselect', 'multiple' => 'multiple'))?>
                </span>
            </div>

            <div class="col-xs-12 col-md-6">
                <label class="date-range-label">Discrepancies only: </label>
                <input name="discrepancy" type="checkbox" class="discrepancy no-submit" <?=Arr::get($_GET, 'discrepancy') ? ' checked' : ''?>/>
            </div>
            <div class="col-xs-12 col-md-6">
                <label class="date-range-label filter-select-label">FSAM: </label>
                        <span class="filter-select-container" data-fsam-selected="<?=isset($_GET['fsam']) ? implode(',',Arr::get($_GET, 'fsam')):''?>">
                        <?=Form::select('fsam[]', [], NULL, array('class' => 'fsam-filter multiselect', 'multiple' => 'multiple'))?>
                    </span>
            </div>
            <?php if (Group::current('allow_assign')):?>
                <div class="col-xs-12 col-md-6">
                    <label class="date-range-label">Contractors :</label>
                    <span class="filter-select-container">
                    <select name="company" class="form-control multiselect">
                        <option value="">All contractors</option>
                        <?php foreach ($companies as $key => $value):?>
                            <option value="<?=$key?>" <?=$key == Arr::get($_GET, 'company') ? 'selected' : ''?>><?=$value?></option>
                        <?php endforeach;?>
                    </select>
                </span>
                </div>
            <?php endif;?>




            <div class="col-xs-12 col-md-6">
                <label class="date-range-label filter-select-label">FDA: </label>
                <span class="filter-select-container" data-fda-selected="<?=isset($_GET['fda']) ? implode(',',Arr::get($_GET, 'fda')):''?>">
                <?=Form::select('fda[]', [], NULL, array('class' => 'fda-filter multiselect', 'multiple' => 'multiple'))?>
            </span>

            </div>
            <div class="col-xs-12 col-md-6">
                <label class="date-range-label">Address:</label>
                <span class="filter-select-container">
                    <input type="text" class="form-control"placeholder="Address" name="address" value="<?=Arr::get($_GET, 'address')?>" />
                </span>
            </div>
        <div class="clearfix">&nbsp;</div>
        <div class="col-xs-12 col-md-6">
            <button type="submit" class="btn btn-success">Apply filters</button>
        </div>
    </div>

    <div id="sorting" class="hidden">
        <?php $sorting = Arr::get($_GET, 'sort', array()); foreach ($sorting as $sort):?>
            <input type="hidden" name="sort[]" value="<?=$sort?>" />
        <?php endforeach; $sorting = array_flip($sorting);?>
    </div>

<?=Form::hidden('start', Arr::get($_GET, 'start', date('d-m-Y', strtotime('first day of this month'))), array('class' => 'form-control datepicker', 'placeholder' => 'Start date', 'id' => 'start'))?>
<?=Form::hidden('end', Arr::get($_GET, 'end', date('d-m-Y')), array('class' => 'form-control datepicker', 'placeholder' => 'End date', 'id' => 'end'))?>
<?=Form::hidden('app-start', Arr::get($_GET, 'app-start'), array('class' => 'form-control datepicker', 'placeholder' => 'Start date (Approved)', 'id' => 'app-start'))?>
<?=Form::hidden('app-end', Arr::get($_GET, 'app-end'), array('class' => 'form-control datepicker', 'placeholder' => 'End date (Approved)', 'id' => 'app-end'))?>
<?=Form::hidden('fin-start', Arr::get($_GET, 'fin-start'), array('class' => 'form-control datepicker', 'placeholder' => 'Start date (Financial)', 'id' => 'fin-start'))?>
<?=Form::hidden('fin-end', Arr::get($_GET, 'fin-end'), array('class' => 'form-control datepicker', 'placeholder' => 'End date (Financial)', 'id' => 'fin-end'))?>

</form>
<div>
<h3 class="pull-left">Total found: <span id="submissions_count"><?=Arr::get($_GET, 'discrepancy') ? count($submissions) - count($discrepancies) : count($submissions)?></span> ticket(s)</h3>
<?php if ($approve_all):?>
    <a href="<?=URL::base() . Request::current()->uri() . URL::query(array('approve' => 1))?>" class="pull-right btn btn-success">Approve all</a>
<?php endif;?>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-hover" <?=Group::current('allow_assign') ? 'data-url="' . URL::base() . 'reports/financial/approve"' : ''?>>
    <tr class="text-center">
        <th class="sortable" data-id="submission">Submission date</th>
        <th class="sortable" data-id="approval">Approval date</th>
        <th class="sortable" data-id="financial">Financial date</th>
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
    <?php foreach ($submissions as $job => $list): $region = Arr::path($jobs, array($job, 'region'), 0);?>
        <tr class="ticket-id <?=isset($discrepancies[$job])? 'discrepancy text-center'  . (Arr::get($_GET, 'discrepancy') ? ' hidden' : '') : 'text-center'?>">
            <th colspan="<?=Group::current('allow_assign') ? 12 : 9?>"><a href="<?=URL::base()?>search/view/<?=$job?>"><?=$job?></a></th>
        </tr>

        <?php foreach ($list as $submission):
            $key = substr($submission['key'], 5);
            $type = Columns::get_type($key);
            $discr = Group::current('allow_assign') && Arr::path($jobs, $job . '.' . $submission['key']) != $submission['value'];
            $rate = Arr::path($rates, array(User::get($submission['user_id'], 'company_id'), $region, $key), Arr::path($rates, array(User::get($submission['user_id'], 'company_id'), 0, $key), 0));?>
        <tr class="submission text-center <?=$discr ? 'rose' : (Arr::get($_GET, 'discrepancy') ? 'hidden ' : '') .  (Arr::get($submission, 'financial_time') ? 'lgreen' : 'yellow')?>" data-id="<?=$job?>">
            <td><?=date('d-m-Y H:i', $submission['update_time'])?></td>
            <td><?=Arr::get($submission, 'process_time') ? date('d-m-Y H:i', $submission['process_time']) : ''?></td>
            <td class="time"><?=Arr::get($submission, 'financial_time') ? date('d-m-Y H:i', $submission['financial_time']) : ''?></td>
            <td><?=User::get($submission['user_id'], 'login')?></td>
            <?php if (Group::current('allow_assign')):?><td><?=Arr::get($companies, User::get($submission['user_id'], 'company_id'), 'Unknown')?></td><?php endif;?>
            <td><?=Columns::get_name($key)?></td>
            <td <?=strlen(Columns::output($submission['value'], $type)) > 100 ? 'class="shorten"' : ''?>><?=Columns::output($submission['value'], $type);?></td>
            <?php if (Group::current('allow_assign')):?><td><?=Arr::path($jobs, $job . '.' . $submission['key']) ? Columns::output($jobs[$job]['data'][$key], $type) : ''?></td><?php endif;?>
            <td class="paid"><?=Arr::get($submission, 'paid')?></td>
            <td><?=floatval(Arr::get($columns, $key))?></td>
            <td class="rate"><?=Arr::get($submission, 'rate') ? number_format($submission['rate'], 2) : $rate?></td>
            <?php if (Group::current('allow_assign')):?>
            <td>
                <?php if (!$submission['financial_time'] && $rate):?>
                <a href="javascript:;" data-id="<?=$submission['_id']?>" data-value="<?=min(floatval(Arr::get($columns, $key)), floatval($submission['value']) ? : 1)?>" data-max="<?=floatval(Arr::get($columns, $key))?>" class="btn btn-success approve-financial">Approve</a>
                <?php else: echo '&nbsp;'; endif;?>
            </td>
            <?php endif;?>
        </tr>
        <?php endforeach;?>

    <?php endforeach;?>
</table>
<a href="javascript:;" data-id="export" class="export-button pull-right btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
<a href="javascript:;" data-id="excel" class="export-button pull-right btn btn-success"><span class="glyphicon glyphicon-export"></span> Export to Excel</a>