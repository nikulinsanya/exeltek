<form action="" method="get">
    <div class="filter-info-container">
        <label class="date-range-label">Ticket ID</label>
        <span class="date-range-container">
            <input type="text" class="form-control auto-complete" source="<?=URL::base()?>imex/reports/tickets/" name="ticket" value="<?=Arr::get($_GET, 'ticket')?>" />
        </span>
        <div class="clearfix">&nbsp;</div>

        <label class="date-range-label">CSV File Name</label>
        <span class="date-range-container">
            <input type="text" class="form-control auto-complete" placeholder="" source="<?=URL::base()?>imex/reports/files/" name="file" value="<?=Arr::get($_GET, 'file')?>" />
        </span>
        <div class="clearfix">&nbsp;</div>

        <label class="date-range-label">Date range: </label>
        <span class="date-range-container">
            <div class="daterange" data-time-picker="true" class="pull-right" data-start="start" data-end="end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                <span></span> <b class="caret"></b>
            </div>
        </span>
        <div class="clearfix">&nbsp;</div>

        <label class="date-range-label">Discrepancies only: </label>
        <input name="discrepancy" type="checkbox" class="discrepancy no-submit" <?=Arr::get($_GET, 'discrepancy') ? ' checked' : ''?>/>

        <input type="hidden" class="form-control datetimepicker" placeholder="Start Date" name="start" id="start" value="<?=Arr::get($_GET, 'start')?>" />
        <input type="hidden" class="form-control datetimepicker" placeholder="End Date" name="end" id="end" value="<?=Arr::get($_GET, 'end')?>" />
        <div class="clearfix">&nbsp;</div>
        <button type="submit" class="btn btn-success" value="Show report">
            <span class="glyphicon glyphicon-ok"></span>
            Show report
        </button>
    </div>

    <div class="clearfix">&nbsp;</div>
</form>
<?php if (isset($error)):
    echo View::factory('Error')->set('error', $error); 
else:?>
<div class="col-xs-12 text-center">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="col-xs-12 text-right">
    <a class="pull-right btn btn-simple export-button" data-url="<?=URL::base()?>imex/discrepancies/export/all" href="javascript:;"><span class="glyphicon glyphicon-export"></span>Export all to CSV</a>
    <a class="pull-right btn btn-simple export-button" data-url="<?=URL::base()?>imex/discrepancies/export" href="javascript:;"><span class="glyphicon glyphicon-export"></span>Export to CSV</a>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table">
    <tr class="text-center">
        <th class="col-xs-1">Ticket ID</th>
        <th class="col-xs-1">Date</th>
        <th class="col-xs-1">User</th>
        <th class="col-xs-1">File name</th>
        <?php foreach ($reports as $id => $name):?>
        <th class="col-xs-1"><?=HTML::chars($name)?></th>
        <?php endforeach;?>
        <th>Columns:</th>
    </tr>
    <?php foreach ($tickets as $ticket):?>
    <tr class="text-center super yellow <?=$ticket['discr'] ? 'discrepancy' . (isset($_GET['discrepancy']) ? ' hidden' : '') : ''?>">
        <td><a href="<?=URL::base() . 'search/view/' . $ticket['job_key']?>"><?=$ticket['job_key']?></a></td>
        <td nowrap="nowrap"><?=date('d-m-Y H:i', $ticket['update_time'])?></td>
        <td><?=User::get(Arr::get($ticket, 'user_id'), 'login') ? : 'Unknown'?></td>
        <td><a href="<?=URL::base() . 'imex/discrepancies?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
        <?php foreach ($reports as $key => $name):?>
        <td><?=Columns::output(Arr::path($ticket, 'static.'.$key), Columns::get_type($key))?></td>
        <?php endforeach;?>
        <td>
            <table class="table subtable">
                <tr class="same-yellow">
                    <th>Column name</th>
                    <th>Old value</th>
                    <th>New value</th>
                    <th>Current value</th>
                </tr>
                <?php foreach($ticket['data'] as $id => $value): $date = Columns::get_type($id) == 'date';?>
                <tr class="submission <?=$value['old_value'] != $ticket['current'][$id] ? 'rose' : 'same-yellow' . (isset($_GET['discrepancy']) ? ' hidden' : '')?>">
                    <td><?=HTML::chars(Columns::get_name($id));?></td>
                    <td><?=HTML::chars($date ? date("d-m-Y H:i", $value['old_value'] ? : 0) : $value['old_value'])?></td>
                    <td><?=HTML::chars($date ? date("d-m-Y H:i", $value['new_value'] ? : 0) : $value['new_value'])?></td>
                    <td><?=HTML::chars($date ? date("d-m-Y H:i", $ticket['current'][$id] ? : 0) : $ticket['current'][$id])?></td>
                </tr>
                <?php endforeach;?>
            </table>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<div class="col-xs-12 text-center">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="col-xs-12">
    <a class="pull-right btn btn-simple export-button" data-url="<?=URL::base()?>imex/discrepancies/export" href="javascript:;"><span class="glyphicon glyphicon-export"></span>Export to CSV</a>
</div>
<div class="clearfix">&nbsp;</div>
<?php endif;?>