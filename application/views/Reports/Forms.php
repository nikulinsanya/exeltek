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
<?php if ($filters): ?>
<div class="col-xs-12">
    <div id="filter-list"></div>
    <?=View::factory('Pager')?>
    <div id="reports" style="margin-top: 10px;">
        <table class="table">
            <tr class="table-header">
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
                            case 'date':?>
                            <li>
                                <input type="text" class="from form-control datepicker" placeholder="Start date" value="<?=Arr::path($filters, array($column['id'], '$gte')) ? date('d-m-Y', $filters[$column['id']]['$gte']) : ''?>"/>
                            </li>
                            <li>
                                <input type="text" class="to form-control datepicker" placeholder="End date" value="<?=Arr::path($filters, array($column['id'], '$lte')) ? date('d-m-Y', $filters[$column['id']]['$lte']) : ''?>"/>
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
                                <input type="text" class="text form-control multiline" data-separator="|" placeholder="Contain text" value="<?=implode('|', array_map('strval', Arr::path($filters, array($column['id'], '$in'), array())))?>" />
                            </li>
                        <?php endswitch;?>
                        <li class="dropdown-header buttons-row">
                            <button class="btn btn-success apply-filter dropdown-toggle" type="button">Apply</button>
                            <button class="btn btn-warning filter-clear dropdown-toggle" type="button">Clear</button>
                            <button class="btn btn-danger dropdown-toggle" type="button" >Cancel</button>
                        </li>
                    </ul>
<!--                    -->
                </th>
                <?php endforeach;?>
            </tr>
            <?php if ($reports): foreach ($reports as $report):?>
            <tr>
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
                    <td><?=Arr::get($report, $column['id'], '&nbsp;')?></td>
                <?php endforeach;?>
            </tr>
            <?php endforeach; else:?>
                <tr>
                    <td colspan="<?=count($columns) + ($geo ? 2 : 1)?>"><h4 class="text-danger">No records found!</h4></td>
                </tr>
            <?php endif;?>
        </table>
    </div>

</div>
    <div class="btn-group pull-right">
        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="glyphicon glyphicon-export"></span> Export options <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="?id=<?=$_GET['id']?>&export"><span class="glyphicon glyphicon-list-alt">Export to CSV</span></a></li>
            <li><a href="?id=<?=$_GET['id']?>&export=excel"><span class="glyphicon glyphicon-file">Export to Excel</span></a></li>
        </ul>
    </div>

<?php endif;?>

<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/forms/reports.js"></script>