<div class="col-xs-12 text-center">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-hover table-striped">
    <tr>
        <th>Action</th>
        <th>Date</th>
        <th>User</th>
        <th>Company</th>
        <th>File name</th>
    </tr>
    <?php foreach ($logs as $log):?>
        <tr>
            <td><span class="glyphicon glyphicon-<?=Arr::get($log, 'action') == 1 ? 'ok text-success' : 'remove text-danger'?>"></span></td>
            <td><?=date('d-m-Y H:i:s', $log['uploaded'])?></td>
            <td><?=User::get($log['user_id'], 'login')?></td>
            <td><?=Arr::get($companies, User::get($log['user_id'], 'company_id'))?></td>
            <td><?=HTML::chars($log['filename'])?></td>
        </tr>
    <?php endforeach;?>
</table>
<div class="col-xs-12 text-center">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
