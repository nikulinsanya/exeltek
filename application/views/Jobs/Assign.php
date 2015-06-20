<?php if ($list):?>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<table class="table table-hover table-striped">
    <tr>
        <th>Date</th>
        <th>User</th>
        <th>Job ID</th>
        <th>Job type</th>
        <th>Company</th>
    </tr>
    <?php foreach ($list as $assign): ?>
        <tr>
            <td><?=date('d-m-Y H:i:s', $assign['time'])?></td>
            <td><?=User::get($assign['user_id'], 'login')?></td>
            <td><?=HTML::chars($assign['job_id'])?></td>
            <td><?=Arr::get($types, $assign['type'])?></td>
            <td><?=Arr::get($companies, $assign['company_id'], 'Unassigned')?></td>
        </tr>
    <?php endforeach;?>
</table>
<div class="col-xs-12">
<?php if (Pager::pages() > 1) echo $pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<?php else:?>
<h4>This job was not allocated yet!</h4>
<button class="btn btn-danger back-button" type="button">Back</button>
<?php endif;?>