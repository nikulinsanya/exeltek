<div id="tabEditor">
    <h3>Tabs:</h3>
    <table class="table" id="tabList">
        <tr >
            <th width="1%"><button class="btn btn-xs btn-success tab-item edit-tab">Add new tab</button></th>
            <th>Name</th>
        </tr>
        <?php foreach ($tabs as $key => $tab):?>
        <tr>
            <td>
                <button class="btn btn-xs btn-warning tab-item edit-tab" data-id="<?=$key?>"><span class="glyphicon glyphicon-pencil"></span></button>
                <button class="btn btn-xs btn-danger tab-item remove-tab" data-id="<?=$key?>"><span class="glyphicon glyphicon-trash"></span></button></td>
            <td> <span data-target="<?=$key?>"><?=$tab?></span> </td>
        </tr>
        <?php endforeach;?>
    </table>
    <h3>Columns:</h3>
    <table class="table" id="tabsTable">
        <?php $i = 0; foreach ($columns as $column):?>
        <?php if ($i % 20 == 0):?>
        <tr>
            <th width="1%"><button class="btn btn-xs btn-success column-item edit-column">Add new column</button></th>
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
            <td>
                <button class="btn btn-xs btn btn-warning column-item edit-column" data-id="<?=$column['id']?>"><span class="glyphicon glyphicon-pencil"></span></button>
                <button class="btn btn-xs btn btn-danger column-item remove-column" data-id="<?=$column['id']?>"><span class="glyphicon glyphicon-trash"></span></button>
            </td>
            <td class="tab-name"><?=Arr::get($tabs, $column['tab_id'], 'Unknown')?></td>
            <td class="column-name"><?=$column['name']?></td>
            <td class="column-type"
                data-type="<?php echo(strpos($column['type'], 'enum') === 0 ? 'enum' : $column['type'])?>">
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
                            echo '<span title="' . (Enums::is_multi($id) ? 'MULTIPLE VALUES:' : 'SINGLE VALUE:') . "\n\n" . implode("\n", Enums::get_values($id)) . '">Enum (' . Arr::get($enums, $id, 'Unknown') . ')</span>';
                        } else echo 'Default (string)';
                    endswitch;
                ?>
            </td>
            <td class="column-financial"><?=$column['financial'] ? : ''?></td>
            <td class="column-export" data-val="<?=$column['csv'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['csv'])?></td>
            <td class="column-report" data-val="<?=$column['show_reports'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['show_reports'])?></td>
            <td class="column-direct" data-val="<?=$column['direct'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['direct'])?></td>
            <td class="column-track" data-val="<?=$column['track'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['track'])?></td>
            <td class="column-persistent" data-val="<?=$column['persistent'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['persistent'])?></td>
            <td class="column-editable" data-val="<?=$column['editable'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['editable'])?></td>
            <td class="column-readonly" data-val="<?=$column['read_only'] ? 'checked' : ''?>"><?=Utils::bool_icon($column['read_only'])?></td>
        </tr>
        <?php endforeach;?>
    </table>

</div>


<div class="modal fade" id="editColumn" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document"  style="width:600px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="enum-title">Edit column </h4>
            </div>
            <div class="modal-body" id="column-form">
                <form id="column-form-data">
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Column name</span>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="column-field" name="name" id="column-name"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Tab name</span>
                    </div>
                    <div class="col-md-8">
                        <div class="in-row">
                            <select  class="column-field" name="tab_id" id="column-tab">

                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Type</span>
                    </div>
                    <div class="col-md-8">
                        <div class="in-row">
                            <select class="column-field" name="type" id="column-type">
                                <option value="">String</option>
                                <option value="text">Text</option>
                                <option value="date">Date</option>
                                <option value="datetime">DateTime</option>
                                <option value="float">Floating-point</option>
                                <option value="integer">Integer</option>
                                <option value="enum">Enum</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row enum-field-visible">
                    <div class="col-md-4">
                        <span class="form-label">Enum</span>
                    </div>
                    <div class="col-md-8">
                        <div class="in-row">
                            <select class="column-field" id="enum-type">
                                <?php foreach ($enums as $key => $enum):?>
                                    <option value="<?=$key?>"><?=$enum?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Financial value limit</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="number" step="0.01" name="financial" id="column-financial"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Export to CSV</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-export" name="csv"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Show in reports</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-report"  name="show_reports"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Direct update</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-direct" name="direct"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Track column</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-track" name="track"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Persistent column</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-persistent" name="persistent"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Is editable</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-editable" name="editable"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Is readonly</span>
                    </div>
                    <div class="col-md-8">
                        <input class="column-field" type="checkbox" id="column-readonly" name="read_only"/>
                    </div>
                </div>
                <input type="hidden" id="columnId"/>
            </form>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success" id="updateColumn">Update</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editTab" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document"  style="width:600px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="tab-title">Edit tab </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Tab name</span>
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="tabName"/>
                    </div>
                </div>
                <input type="hidden" id="tabId"/>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success" id="updateTab">Update</button>
            </div>
        </div>
    </div>
</div>


<script src="<?= URL::base() ?>js/security/tabs.js"></script>