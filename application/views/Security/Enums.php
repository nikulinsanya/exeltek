<table class="table">
    <tr>
        <th><button class="btn btn-success edit-enum" data-id="0">Add</button></th>
        <th>Name</th>
        <th>Multi-values</th>
    </tr>
    <?php foreach ($items as $item):?>
    <tr data-id="<?=$item['id']?>">
        <td><button class="btn btn-warning edit-enum" data-id="<?=$item['id']?>">Edit</button></td>
        <td><?=$item['name']?></td>
        <td><span class="glyphicon glyphicon-<?=$item['allow_multi'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    </tr>
    <?php endforeach;?>
</table>