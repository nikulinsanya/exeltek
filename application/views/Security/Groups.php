<table class="table table-striped">
<tr class="text-center">
    <th>Group name</th>
    <th>Is admin</th>
    <th>Show all jobs</th>
    <th>Allow assigning</th>
    <th>Allow tracking changes</th>
    <th>Allow tracking submissions</th>
    <th>Financial reports</th>
    <th>Forms submission</th>
    <th>Custom forms submission</th>
    <th>Editing custom forms reports</th>
    <th>Quality reports</th>
    <th>Time Machine</th>
    <th>Columns</th>
    <th>Users</th>
    <th><a href="<?=URL::base()?>security/groups/edit" class="btn show-content-in-popup btn-success">Add</a></th>
</tr>
<?php foreach ($list as $item):?>
<tr class="text-center">
    <td><?=HTML::chars($item['name'])?></td>
    <td><span class="glyphicon glyphicon-<?=$item['is_admin'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['show_all_jobs'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_assign'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_reports'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_submissions'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_finance'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_forms'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_custom_forms'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['edit_custom_forms'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['allow_quality'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><span class="glyphicon glyphicon-<?=$item['time_machine'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    <td><?=implode('<br/>', array_intersect_key(Columns::$fixed, array_flip(explode(',', $item['columns']))))?></td>
    <td><?=intval($item['cnt'])?></td>
    <td>
        <a href="<?=URL::base()?>security/groups/edit/<?=$item['id']?>" class="btn btn-warning show-content-in-popup">Edit</a>
        <?php if (!$item['is_admin'] && !$item['cnt']):?>
        <a href="<?=URL::base()?>security/groups/delete/<?=$item['id']?>" confirm="Do you really want to remove this user? This action can't be undone!" class="btn btn-danger">Delete</a>
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?>
</table>