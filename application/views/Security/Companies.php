<table class="table table-striped">
<tr>
    <th>Company name</th>
    <th>Company type</th>
    <th>Users</th>
    <th><a href="<?=URL::base()?>security/companies/edit" class="btn btn-success">Add</a></th>
</tr>
<?php foreach ($list as $item):?>
<tr>
    <td><?=HTML::chars($item['name'])?></td>
    <td><?=HTML::chars($item['company_type'])?></td>
    <td><?=intval($item['cnt'])?></td>
    <td>
        <a href="<?=URL::base()?>security/companies/edit/<?=$item['id']?>" class="btn btn-warning">Edit</a>
        <?php if (!$item['cnt']):?>
        <a href="<?=URL::base()?>security/companies/delete/<?=$item['id']?>" confirm="Do you really want to remove this user? This action can't be undone!" class="btn btn-danger">Delete</a>
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?>
</table>