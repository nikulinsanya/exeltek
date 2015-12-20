<form id="filters autosubmit">
    <div class="col-xs-12">
        <label class="control-label"></label>
        <select name="id" id="form-reports" class="form-control">
            <option value="">Please, select report type</option>
            <?php foreach ($tables as $id => $name):?>
                <option value="<?=$id?>" <?=Arr::get($filters, 'report_id') == $id ? 'selected' : ''?>><?=HTML::chars($name)?></option>
            <?php endforeach;?>
        </select>
    </div>
</form>
<div class="filter-info-container">
        <label  class="filter_value">Filters:</label>
        <div class="text-info-filters">
            <div id="filter-list"></div>
        </div>

        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#formsFilterModal">
            <span class="glyphicon glyphicon-filter"></span>
            Modify filters
        </button>
        <a class="btn btn-danger clear-all" >Clear all</a>
</div>
<?php if ($filters): ?>
<div class="col-xs-12">
    <?=View::factory('Pager')?>
    <div id="reports" style="margin-top: 10px;">
        <table class="table">
            <tr class="table-header">
                <th><input type="checkbox" class="checkbox check-all select-reports"/></th>
                <th>File name</th>
                <?php if ($geo):?>
                    <th>Location</th>
                <?php endif;?>
                <?php foreach ($columns as $column):?>
                <th class="dropdown needs-filter" data-type="<?=$column['type']?>" data-guid="<?=$column['id']?>">
                    <a href="#" class="dropdown-toggle" data-toggle="collapse" data-target="#<?=$column['id']?>">
                        <?=$column['name']?>
                    </a>
                    <ul class="dropdown-menu collapse" id="<?=$column['id']?>">
                        <?php switch ($column['type']):
                            case 'date':
                            case 'datetime':?>
                            <li>
                                <input type="text" class="from form-control <?=$column['type']?>picker" placeholder="Start date" value="<?=Columns::output(Arr::path($filters, array($column['id'], '$gte')), $column['type'])?>"/>
                            </li>
                            <li>
                                <input type="text" class="to form-control <?=$column['type']?>picker" placeholder="End date" value="<?=Columns::output(Arr::path($filters, array($column['id'], '$lte')), $column['type'])?>"/>
                            </li>
                        <?php break;
                            case 'number':
                            case 'float':?>
                            <li>
                                <input type="text" class="from form-control" placeholder="> than" value="<?=Arr::path($filters, array($column['id'], '$gte'), '')?>"/>
                            </li>
                            <li>
                                <input type="text" class="to form-control" placeholder="< than" value="<?=Arr::path($filters, array($column['id'], '$lte'), '')?>" />
                            </li>
                        <?php break;
                            default:?>
                            <li>
                                <input type="text" class="text form-control multiline" data-separator="|" placeholder="Contain text" value="<?=implode('|', array_map(function ($v) { return ($v instanceof MongoRegex) ? $v->regex : strval($v); }, Arr::path($filters, array($column['id'], '$in'), array())))?>" />
                            </li>
                        <?php endswitch;?>
                        <li class="dropdown-header buttons-row">
                            <button class="btn btn-success apply-filter dropdown-toggle" type="button">Apply</button>
                            <button class="btn btn-warning filter-clear dropdown-toggle" type="button">Clear</button>
                            <button class="btn btn-danger dropdown-toggle" type="button" >Cancel</button>
                        </li>
                    </ul>
                </th>
                <?php endforeach;?>
                <?php if ($attachments):?>
                    <th>Attachments</th>
                <?php endif;?>
            </tr>
            <?php if ($reports): foreach ($reports as $report): ?>
            <tr data-id="<?=$report['id']?>">

                <td><input type="checkbox" class="select-reports checkbox" data-id="<?=$report['attachment_id']?>"/></td>
                <td><a href="<?=URL::base()?>download/attachment/<?=$report['attachment_id']?>"><?=$report['attachment']?></a></td>
                <?php if ($geo):?>
                    <td>
                        <?php if (Arr::get($report, 'geo')):?>
                        <a target="_blank" href="https://www.google.com/maps/@<?=$report['geo']?>,19z">View on map</a>
                        <?php else:?>
                        &nbsp;
                        <?php endif;?>
                    </td>
                <?php endif;?>
                <?php foreach ($columns as $column):?>
                    <td <?=isset($report['colors'][$column['id']]) ? 'style="background-color: ' . $report['colors'][$column['id']] . ';"' : ''?>
                        <?=Group::current('edit_custom_forms') && $column['visible'] == 'write' ? 'class="editable-form-cell" data-type="' . $column['type'] . '" data-guid="' . $column['id'] . '"' : ''?>>
                        <?=Arr::get($report, $column['id']) ? Columns::output($report[$column['id']], $column['type']) : '&nbsp;'?>
                    </td>
                <?php endforeach;?>
                <?php if ($attachments):?>
                    <td>
                        <?php $i = 0; foreach (Arr::get($report, 'attachments', array()) as $attachment):
                            if ($i++ == 2):?>
                                <div class="popover-block">
                            <?php endif;?>
                            <a href="<?=URL::base()?>download/attachment/<?=$attachment?>"><img src="<?=URL::base()?>download/thumb/<?=$attachment?>" /></a>
                        <?php endforeach; if ($i > 2) echo '</div>';?>

                    </td>
                <?php endif;?>
            </tr>
            <?php endforeach; else:?>
                <tr>
                    <td colspan="<?=count($columns) + ($geo ? 2 : 1)?>"><h4 class="text-danger">No records found!</h4></td>
                </tr>
            <?php endif;?>
        </table>
    </div>

</div>
    <?php if (Group::current('is_admin')):?>
    <button type="button" class="btn btn-danger pull-right" id="reports-remove"><span class="glyphicon glyphicon-remove"></span> Remove</button>
    <?php endif;?>
    <div class="btn-group pull-right">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="glyphicon glyphicon-export"></span> Export options <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="?id=<?=$_GET['id']?>&export" class="add-items-to-request"><span class="glyphicon glyphicon-list-alt">Export to CSV</span></a></li>
            <li><a href="?id=<?=$_GET['id']?>&export=excel" class="add-items-to-request"><span class="glyphicon glyphicon-file">Export to Excel</span></a></li>
            <li><a href="?id=<?=$_GET['id']?>&export&all"><span class="glyphicon glyphicon-list-alt">Export all to CSV</span></a></li>
            <li><a href="?id=<?=$_GET['id']?>&export=excel&all"><span class="glyphicon glyphicon-file">Export all to Excel</span></a></li>
        </ul>
    </div>

<?php endif;?>



<!-- Modal Filters-->
<div class="modal fade" id="formsFilterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Modify filters</h4>
                </div>
                <div class="modal-body" id="form-filter-form">

                </div>
                <div class="modal-footer">
                    <a class="btn btn-warning" data-dismiss="modal" aria-label="Close">Close</a>
                    <button class="btn btn-success" id="applyModalFilters">Apply filters</button>
                </div>
        </div>
    </div>
</div>


<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/forms/reports.js"></script>