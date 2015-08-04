<?php if ($submissions):?>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-hover">
    <tr>
        <th>Date</th>
        <th>User</th>
        <th>Source</th>
        <th>Status</th>
        <th>Processed</th>
        <th>Company</th>
        <th>Location</th>
        <th>Column name</th>
        <th>Value</th>
        <th>Current value</th>
        <?php if (Group::current('allow_assign')):?>
        <th>&nbsp;</th>
        <?php endif;?>
    </tr>
    <?php foreach ($submissions as $submission): 
        $name = Columns::get_name(substr($submission['key'], 5));
        $type = Columns::get_type(substr($submission['key'], 5));
        $value = Columns::output($submission['value'], $type);
        $current = Columns::output(Arr::path($job, 'data.' . substr($submission['key'], 5)), $type);
        ?>
        <tr class="<?=Arr::get($submission, 'active') ? ($submission['active'] > 0 ? 'bg-warning' : 'bg-success') : 'bg-danger'?>">
            <td><?=date('d-m-Y H:i:s', $submission['update_time'])?></td>
            <td><?=User::get($submission['user_id'], 'login')?></td>
            <td><?=isset($submission['version']) ? 'Mobile app (' . ($submission['version'] ? : 'Unknown') . ')' : 'Web-app'?></td>
            <td><span class="glyphicon glyphicon-<?=Arr::get($submission, 'active') ? ($submission['active'] < 0 ? 'ok text-success' : 'edit text-info') : 'remove text-danger'?>"></span></td>
            <td><?=Arr::get($submission, 'process_time') ? date('d-m-Y H:i:s', $submission['process_time']) . ' by ' . User::get(Arr::get($submission, 'admin_id'), 'login') : ''?></td>
            <td><?=Arr::get($companies, User::get($submission['user_id'], 'company_id'))?></td>
            <td><?=Arr::get($submission, 'location') ? '<a target="_blank" href="https://www.google.com/maps/@' . $submission['location'] . ',19z">Location</a>' : ''?></td>
            <td><?=HTML::chars($name)?></td>
            <td class="<?=strlen($value) > 100 ? 'shorten' : ''?>"><?=$value?></td>
            <td class="<?=strlen($current) > 100 ? 'shorten' : ''?>"><?=$current?></td>
            <?php if (Group::current('allow_assign')):?>
            <td>
                <?php if (!Arr::get($submission, 'active')):?>
                    <button class="btn btn-warning approve-submission" type="button" data-id="<?=$submission['_id']?>">Approve</button>
                <?php else:?>
                    &nbsp;
                <?php endif;?>
            </td>
            <?php endif;?>
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