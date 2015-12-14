<h3>Tabs:</h3>
<ul class="list-unstyled">
    <li data-id=""><button class="btn btn-xs btn-success tab-item">Add new tab</button></li>
    <?php foreach ($tabs as $key => $tab):?>
        <li data-id="<?=$key?>"><button class="btn btn-xs btn-warning tab-item">Edit</button> <?=$tab?></li>
    <?php endforeach;?>
</ul>
<h3>Columns:</h3>
<table class="table">
    <?php $i = 0; foreach ($columns as $column):?>
    <?php if ($i % 20 == 0):?>
    <tr data-id="">
        <th width="1%"><button class="btn btn-success column-item">Add</button></th>
        <th>Tab</th>
        <th>Name</th>
        <th>Type</th>
        <th>Financial value limit</th>
        <th>Export to CSV</th>
        <th>Show in reports</th>
        <th>Direct update</th>
        <th>Track column</th>
        <th>Persistent column</th>
        <th>Editable</th>
        <th>Read-only</th>
    </tr>
    <?php endif; $i++;?>
    <tr data-id="<?=$column['id']?>">
        <td><button class="btn btn btn-warning column-item">Edit</button></td>
        <td><?=Arr::get($tabs, $column['tab_id'], 'Unknown')?></td>
        <td><?=$column['name']?></td>
        <td>
            <?php switch ($column['type']):
                case 'text':
                    echo 'Text';
                    break;
                case 'int':
                    echo 'Number';
                    break;
                case 'float':
                    echo 'Floating-point';
                    break;
                case 'date':
                    echo 'Date';
                    break;
                case 'datetime':
                    echo 'Date and Time';
                    break;
                default:
                    if (strpos($column['type'], 'enum') === 0) {
                        $id = substr($column['type'], 5);
                        echo '<a href="javascript:;" title="' . (Enums::is_multi($id) ? 'MULTIPLE VALUES:' : 'SINGLE VALUE:') . "\n\n" . implode("\n", Enums::get_values($id)) . '">Enum (' . Arr::get($enums, $id, 'Unknown') . ')</a>';
                    } else echo 'Default (string)';
                endswitch;
            ?>
        </td>
        <td><?=$column['financial'] ? : ''?></td>
        <td><?=Utils::bool_icon($column['csv'])?></td>
        <td><?=Utils::bool_icon($column['show_reports'])?></td>
        <td><?=Utils::bool_icon($column['direct'])?></td>
        <td><?=Utils::bool_icon($column['track'])?></td>
        <td><?=Utils::bool_icon($column['persistent'])?></td>
        <td><?=Utils::bool_icon($column['editable'])?></td>
        <td><?=Utils::bool_icon($column['read_only'])?></td>
    </tr>
    <?php endforeach;?>
</table>