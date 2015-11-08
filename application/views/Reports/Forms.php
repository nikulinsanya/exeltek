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
    <div id="filter-list"></div>
</form>
<?php if ($filters):?>
<div class="col-xs-12">
    <?=View::factory('Pager')?>
    <div id="reports" style="margin-top: 10px;">
        <table class="table">
            <tr>
                <th>File name</th>
                <?php if ($geo):?>
                    <th>Geolocation</th>
                <?php endif;?>
                <?php foreach ($columns as $column):?>
                <th><?=$column['name']?></th>
                <?php endforeach;?>
            </tr>
            <?php if ($reports): foreach ($reports as $report):?>
            <tr>
                <td><a href="<?=URL::base()?>download/attachment/<?=$report['attachment_id']?>"><?=Arr::get($report, 'attachment', 'Unknown file')?></a></td>
                <?php if ($geo):?>
                    <td></td>
                <?php endif;?>
                <?php foreach ($columns as $column):?>
                    <td><?=Arr::get($report, $column['id']) ? Columns::output($report[$column['id']], $column['type']) : '&nbsp;'?></td>
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
<?php endif;?>

<div id="templates" style="display: none;">
    <div id="datefilter">
        <ul class="dropdown-menu collapse">
            <li>
                <input type="text" class="from form-control datepicker" placeholder="Start date"/>
            </li>
            <li>
                <input type="text" class="to form-control datepicker" placeholder="End date"/>
            </li>
            <li class="dropdown-header buttons-row">
                <button class="btn btn-success apply-filter dropdown-toggle" type="button">Apply</button>
                <button class="btn btn-warning filter-clear dropdown-toggle" type="button">Clear</button>
                <button class="btn btn-danger dropdown-toggle" type="button" >Cancel</button>
            </li>
        </ul>
    </div>
    <div id="textfilter">
        <ul class="dropdown-menu collapse">
            <li>
                <input type="text" class="text form-control multiline" data-separator="|" placeholder="Contain text"/>
            </li>
            <li class="dropdown-header buttons-row">
                <button class="btn btn-success apply-filter dropdown-toggle" type="button">Apply</button>
                <button class="btn btn-warning filter-clear dropdown-toggle" type="button">Clear</button>
                <button class="btn btn-danger dropdown-toggle" type="button">Cancel</button>
            </li>
        </ul>
    </div>
    <div id="numberfilter">
        <ul class="dropdown-menu collapse">
            <li>
                <input type="text" class="from form-control" placeholder="> than"/>
            </li>
            <li>
                <input type="text" class="to form-control" placeholder="< than"/>
            </li>
            <li class="dropdown-header buttons-row">
                <button class="btn btn-success apply-filter dropdown-toggle" type="button">Apply</button>
                <button class="btn btn-warning filter-clear dropdown-toggle" type="button">Clear</button>
                <button class="btn btn-danger dropdown-toggle" type="button">Cancel</button>
            </li>
        </ul>
    </div>
</div>



<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/forms/reports.js"></script>