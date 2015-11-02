<div class="col-xs-12">
    <label class="control-label"></label>
    <select id="form-reports" class="form-control">
        <option value="">Please, select report type</option>
        <?php foreach ($reports as $id => $name):?>
            <option value="<?=$id?>"><?=HTML::chars($name)?></option>
        <?php endforeach;?>
    </select>
</div>
<div class="col-xs-12">
    <div id="reports" style="margin-top: 10px;"></div>
</div>

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
                <button class="btn btn-warning date-clear dropdown-toggle" type="button">Clear</button>
                <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse">Cancel</button>
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
                <button class="btn btn-warning date-clear dropdown-toggle" type="button">Clear</button>
                <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse">Cancel</button>
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
                <button class="btn btn-warning date-clear dropdown-toggle" type="button">Clear</button>
                <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse">Cancel</button>
            </li>
        </ul>
    </div>
</div>



<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/forms/reports.js"></script>