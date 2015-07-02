<?php if ($submissions):?>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-hover table-striped">
    <tr>
        <th>Date</th>
        <th>User</th>
        <th>Status</th>
        <th>Processed</th>
        <th>Company</th>
        <th>Location</th>
        <th>Column name</th>
        <th>Value</th>
    </tr>
    <?php foreach ($submissions as $submission): 
        $name = Columns::get_name(substr($submission['key'], 5));
        $type = Columns::get_type(substr($submission['key'], 5));
        ?>
        <tr>
            <td><?=date('d-m-Y H:i:s', $submission['update_time'])?></td>
            <td><?=User::get($submission['user_id'], 'login')?></td>
            <td><span class="glyphicon glyphicon-<?=Arr::get($submission, 'active') ? ($submission['active'] < 0 ? 'ok text-success' : 'edit text-info') : 'remove text-danger'?>"></span></td>
            <td><?=Arr::get($submission, 'process_time') ? date('d-m-Y H:i:s', $submission['process_time']) . ' by ' . User::get(Arr::get($submission, 'admin_id'), 'login') : ''?></td>
            <td><?=Arr::get($companies, User::get($submission['user_id'], 'company_id'))?></td>
            <td><?=Arr::get($submission, 'location') ? '<a target="_blank" href="https://www.google.com/maps/@' . $submission['location'] . ',19z">Location</a>' : ''?></td>
            <td><?=HTML::chars($name)?></td>
            <td class="<?=strlen(Columns::output($submission['value'], $type)) > 100 ? 'shorten' : ''?>"><?=Columns::output($submission['value'], $type)?></td>
        </tr>
    <?php endforeach;?>
</table>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<?php else:?>
<h4>No submissions made</h4>
<button class="btn btn-danger back-button" type="button">Back</button>
<?php endif;?>