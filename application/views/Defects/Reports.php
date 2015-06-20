<form action="" method="get">
<div class="col-xs-2">
    <input type="text" class="form-control auto-complete" placeholder="Ticket ID" source="<?=URL::base()?>defects/reports/tickets/" name="ticket" value="<?=Arr::get($_GET, 'ticket')?>" />
</div>
<div class="col-xs-2">
    <input type="text" class="form-control auto-complete" placeholder="CSV File Name" source="<?=URL::base()?>defects/reports/files/" name="file" value="<?=Arr::get($_GET, 'file')?>" />
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
<div class="col-xs-12">
    <a class="pull-right" href="<?=URL::base()?>defects/reports/export<?=URL::query()?>"><span class="glyphicon glyphicon-export"></span>Export to CSV</a>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-striped">
    <tr>
        <th class="col-xs-1">Ticket ID</th>
        <th class="col-xs-1">Date</th>
        <th class="col-xs-1">Action</th>
        <th class="col-xs-1">File name</th>
        <th colspan="3">Additional information</th>
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
    <?php if ($ticket['update_type'] == 2): $cnt = count($ticket['diff']) + 1 + ($ticket['hidden'] ? 1 : 0);?>
    <tr class="warning">
        <td class="visible-<?=$ticket['key']?>" rowspan="<?=$cnt?>"><a href="<?=URL::base() . 'defects/reports?ticket=' . $ticket['Id']?>"><?=$ticket['Id']?></a></td>
        <td class="visible-<?=$ticket['key']?>" rowspan="<?=$cnt?>" nowrap="nowrap"><?=$ticket['update_time']?></td>
        <td class="visible-<?=$ticket['key']?>" rowspan="<?=$cnt?>"><?=Arr::get($actions, $ticket['update_type'])?></td>
        <td class="visible-<?=$ticket['key']?>" rowspan="<?=$cnt?>"><a href="<?=URL::base() . 'defects/reports?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
        <th>Column name:</th>
        <th>Old value:</th>
        <th>New value:</th>
    </tr>    
    <?php foreach($ticket['diff'] as $key => $value):?>
    <tr class="warning">
        <td><?=HTML::chars(Arr::get($columns, $key, $key))?></td>
        <td><?=HTML::chars($value)?></td>
        <td><?=HTML::chars(Arr::get($ticket, $key, ''))?></td>
    </tr>
    <?php endforeach;?>
    <?php if ($ticket['hidden']):?>
    <tr class="warning">
        <th colspan="3">
            <a href="javascript:;" data-target="<?=$ticket['id']?>" data-base="<?=$cnt?>" data-count="<?=count($ticket['hidden'])?>" class="btn btn-info btn-toggle">Toggle hidden columns</a>
        </th>
    </tr>
    <?php foreach($ticket['hidden'] as $key => $value):?>
    <tr class="warning hidden hidden-<?=$ticket['id']?>">
        <td><?=HTML::chars(Arr::get($columns, $key, $key))?></td>
        <td><?=HTML::chars($value)?></td>
        <td><?=HTML::chars(Arr::get($ticket, $key, ''))?></td>
    </tr>
    <?php endforeach;?>
    <?php endif;?>
    <?php else:?>     
    <tr class="<?=Arr::get($classes, $ticket['update_type'])?>">
        <td><a href="<?=URL::base() . 'defects/reports?ticket=' . $ticket['Id']?>"><?=$ticket['Id']?></a></td>
        <td nowrap="nowrap"><?=$ticket['update_time']?></td>
        <td><?=Arr::get($actions, $ticket['update_type'])?></td>
        <td><a href="<?=URL::base() . 'defects/reports?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
        <td colspan="3">N/A</td>
    </tr>
    <?php endif;?>
    <?php endforeach;?>
</table>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="col-xs-12">
    <a class="pull-right" href="<?=URL::base()?>defects/reports/export<?=URL::query()?>"><span class="glyphicon glyphicon-export"></span>Export to CSV</a>
</div>
<div class="clearfix">&nbsp;</div>
<?php endif;?>