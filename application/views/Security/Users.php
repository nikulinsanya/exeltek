<table class="table table-striped">
<tr class="text-center">
    <th>User name</th>
    <th>Email</th>
    <th>Group</th>
    <th>Company</th>
    <th>Regions</th>
    <th>Last login</th>
    <th><a href="<?=URL::base()?>security/users/edit" class="btn btn-success">Add</a></th>
</tr>
<?php foreach ($list as $item):?>
<tr class="text-center">
    <td><?=HTML::chars($item['login'])?></td>
    <td><?=HTML::chars($item['email'])?></td>
    <td><?=HTML::chars($item['group'])?></td>
    <td><?=HTML::chars($item['company'])?></td>
    <td><?=implode(',', array_intersect_key($regions, array_flip(Arr::get($regs, $item['id'], array()))))?></td>
    <td><?=date('d-m-Y H:i:s', $item['last_seen'])?></td>
    <td>
        <a href="<?=URL::base()?>security/users/edit/<?=$item['id']?>" class="btn btn-warning">Edit</a>
        <?php if (!$item['is_admin']):?>
        <a href="<?=URL::base()?>security/users/delete/<?=$item['id']?>" confirm="Do you really want to remove this user? This action can't be undone!" class="btn btn-danger">Delete</a>
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?>
</table>