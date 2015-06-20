<form action="" method="get">
<div class="col-xs-2">
    <input type="text" class="form-control auto-complete" placeholder="Ticket ID" source="<?=URL::base()?>imex/reports/tickets/" name="ticket" value="<?=Arr::get($_GET, 'ticket')?>" />
</div>
<div class="col-xs-2">
    <input type="text" class="form-control auto-complete" placeholder="CSV File Name" source="<?=URL::base()?>imex/reports/files/" name="file" value="<?=Arr::get($_GET, 'file')?>" />
</div>
<div class="col-xs-2">
    <?=Form::select('action', array('' => 'All action', '1' => 'Created', '2' => 'Updated', '3' => 'Removed'), Arr::get($_GET, 'action'), array('class' => 'form-control'))?>
</div>
<div class="col-xs-2">
    <input type="text" class="form-control datetimepicker" placeholder="Start Date" name="start" value="<?=Arr::get($_GET, 'start')?>" />
</div>
<div class="col-xs-2">
    <input type="text" class="form-control datetimepicker" placeholder="End Date" name="end" value="<?=Arr::get($_GET, 'end')?>" />
</div>
<div class="col-xs-2">
    <input type="submit" class="form-control btn-success" value="Show report"/>
</div>
<div class="clearfix">&nbsp;</div>
</form>
<?php if (isset($error)):
    echo View::factory('Error')->set('error', $error); 
else:?>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="col-xs-12 text-right">
    <a href="<?=URL::base()?>imex/reports/export/all<?=URL::query()?>"><span class="glyphicon glyphicon-export"></span>Export all to CSV</a>
    <a href="<?=URL::base()?>imex/reports/export<?=URL::query()?>"><span class="glyphicon glyphicon-export"></span>Export to CSV</a>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-striped">
    <tr>
        <th class="col-xs-1">Ticket ID</th>
        <th class="col-xs-1">Date</th>
        <th class="col-xs-1">User</th>
        <th class="col-xs-1">Action</th>
        <th class="col-xs-1">File name</th>
        <?php foreach ($reports as $id => $name):?>
        <th class="col-xs-1"><?=HTML::chars($name)?></th>
        <?php endforeach;?>
        <th>Column name:</th>
        <th>Old value:</th>
        <th>New value:</th>
    </tr>
    <?php
        $actions = array(
            '1' => 'Created',
            '2' => 'Updated',
            '3' => 'Removed',
        );
        $classes = array(
            '1' => 'success',
            '2' => 'warning',
            '3' => 'danger',
        );
        foreach ($tickets as $ticket):?>
    <?php if ($ticket['update_type'] == 2 && $ticket['data']): $cnt = count($ticket['data']);?>
    <?php $fl = true; foreach($ticket['data'] as $id => $value): $date = Columns::get_type($id) == 'date';?>
    <tr class="warning">
        <?php if ($fl):?>    
        <td rowspan="<?=$cnt?>"><a href="<?=URL::base() . 'imex/reports?ticket=' . $ticket['job_key']?>"><?=$ticket['job_key']?></a></td>
        <td rowspan="<?=$cnt?>" nowrap="nowrap"><?=date('d-m-Y H:i', $ticket['update_time'])?></td>
        <td rowspan="<?=$cnt?>"><?=User::get(Arr::get($ticket, 'user_id'), 'login') ? : 'Unknown'?></td>
        <td rowspan="<?=$cnt?>"><?=Arr::get($actions, $ticket['update_type'])?></td>
        <td rowspan="<?=$cnt?>"><a href="<?=URL::base() . 'imex/reports?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
        <?php foreach ($reports as $key => $name):?>
        <td rowspan="<?=$cnt?>"><?=Columns::output(Arr::path($ticket, 'static.'.$key), Columns::get_type($key))?></td>
        <?php endforeach; endif;?>
        <td><?=HTML::chars(Columns::get_name($id))?></td>
        <td><?=HTML::chars($date ? date("d-m-Y H:i", $value['old_value'] ? : 0) : $value['old_value'])?></td>
        <td><?=HTML::chars($date ? date("d-m-Y H:i", $value['new_value'] ? : 0) : $value['new_value'])?></td>
    </tr>
    <?php $fl = false; endforeach;?>
    <?php else:?>     
    <tr class="<?=Arr::get($classes, $ticket['update_type'])?>">
        <td><a href="<?=URL::base() . 'imex/reports?ticket=' . $ticket['job_key']?>"><?=$ticket['job_key']?></a></td>
        <td nowrap="nowrap"><?=date('d-m-Y H:i', $ticket['update_time'])?></td>
        <td><?=User::get(Arr::get($ticket, 'user_id'), 'login') ? : 'Unknown'?></td>
        <td><?=Arr::get($actions, $ticket['update_type'])?></td>
        <td><a href="<?=URL::base() . 'imex/reports?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
        <?php foreach ($reports as $id => $name):?>
        <td><?=Arr::path($ticket, 'static.'.$id)?></td>
        <?php endforeach;?>
        <td colspan="3">N/A</td>
    </tr>
    <?php endif;?>
    <?php endforeach;?>
</table>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="col-xs-12">
    <a class="pull-right" href="<?=URL::base()?>imex/reports/export<?=URL::query()?>"><span class="glyphicon glyphicon-export"></span>Export to CSV</a>
</div>
<div class="clearfix">&nbsp;</div>
<?php endif;?>