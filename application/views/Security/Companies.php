<table class="table table-striped">
<tr class="text-center">
    <th>Company name</th>
    <th>Company type</th>
    <th>Users</th>
    <th><a href="<?=URL::base()?>security/companies/edit" class="btn btn-success  show-content-in-popup">Add</a></th>
</tr>
<?php foreach ($list as $item):?>
<tr class="text-center">
    <td><?=HTML::chars($item['name'])?></td>
    <td><?=HTML::chars($item['company_type'])?></td>
    <td><?=intval($item['cnt'])?></td>
    <td>
        <a href="<?=URL::base()?>security/companies/edit/<?=$item['id']?>" class="btn btn-warning  show-content-in-popup">Edit</a>
        <?php if (!$item['cnt']):?>
        <a href="<?=URL::base()?>security/companies/delete/<?=$item['id']?>" confirm="Do you really want to remove this user? This action can't be undone!" class="btn btn-danger">Delete</a>
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?>
</table>