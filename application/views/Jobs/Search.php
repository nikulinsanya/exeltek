<form class="filters-form" action="" method="get">
<h4>Filters:</h4>
<div class="col-xs-4 col-sm-4 col-md-4">
    <input type="text" class="form-control" id="ticket-id" placeholder="Ticket ID" name="ticket" value="<?=Arr::get($_GET, 'ticket')?>" />
</div>
<div class="col-xs-4 col-sm-4 col-md-4">
    <?=Form::select('region', array('' => 'All regions') + $regions, Arr::get($_GET, 'region'), array('class' => 'form-control'))?>
</div>
<div class="col-xs-4 col-sm-4 col-md-4">
    <?=Form::select('type', array('' => 'All jobs') + $types, Arr::get($_GET, 'type'), array('class' => 'form-control'))?>
</div>
<div class="clearfix">&nbsp;</div>
<input name="start" id="updated-start" type="hidden" value="<?=Arr::get($_GET, 'start')?>" />
<input name="end" id="updated-end" type="hidden" value="<?=Arr::get($_GET, 'end')?>" />
<input name="submit-start" id="submit-start" type="hidden" value="<?=Arr::get($_GET, 'submit-start')?>" />
<input name="submit-end" id="submit-end" type="hidden" value="<?=Arr::get($_GET, 'submit-end')?>" />
<?php if (Group::current('show_all_jobs')):?>
<div class="col-xs-2 col-sm-2 col-md-1">
    <label class="control-label">
        Assigned:
    </label>
</div>
<div class="col-xs-4 col-sm-4 col-md-2">
        <?=Form::select('company', array('' => 'Any company') + $companies, Arr::get($_GET, 'company'), array('class' => 'form-control'))?>
</div>
<div class="col-xs-2 col-sm-2 col-md-1">
    <label class="control-label">
        Previous:
    </label>
</div>
<div class="col-xs-4 col-sm-4 col-md-2">
    <?=Form::select('ex', array('' => 'Any company') + $companies, Arr::get($_GET, 'ex'), array('class' => 'form-control'))?>
</div>
<?php endif;?>
<div class="col-xs-6 col-sm-6 col-md-3">
<button type="submit" class="btn btn-success">Search</button>
<a href="<?=URL::base()?>search?clear" class="btn btn-warning">Clear</a>
</div>
<div class="clearfix">&nbsp;</div>
<?php if (Group::current('allow_assign')) foreach (Columns::$settings as $id => $name):?>
<div class="col-xs-4 col-sm-4 col-md-3">
    <label>
        <input name="settings[<?=$id?>]" class="checkbox-x" type="checkbox" checked value="<?=Arr::path($_GET, 'settings.' . $id) ? '1' : (Arr::path($_GET, 'settings.' . $id) === '0' ? '0' : '')?>" />
        <?=HTML::chars($name)?>
    </label>
</div>
<?php endforeach;?>
<div class="clearfix">&nbsp;</div>
<?php $cols = Arr::get($_GET, 'columns', array()); foreach ($cols as $id => $column):?>
<div class="filter-row col-xs-12">
    <div class="col-xs-12 col-sm-12 col-md-6">
    <?=Form::select('columns[]', array('' => 'Please select') + $columns, $column, array('class' => 'form-control field-select'))?>
    </div>
    <div class="col-xs-3 col-sm-3 col-md-1">
    <?=Form::select('actions[]', $actions, Arr::path($_GET, 'actions.' . $id), array('class' => 'form-control action-select'))?>
    </div>
    <div class="col-xs-7 col-sm-7 col-md-2">
    <input type="text" class="form-control" placeholder="Value" name="values[]" value="<?=Arr::path($_GET, 'values.' . $id)?>"/>
    </div>
    <div class="col-xs-2 col-sm-2"> 
    <button type="button" class="btn btn-danger remove-filter"><span class="glyphicon glyphicon-minus"></span> Remove</button>
    </div>
    <div class="clearfix">&nbsp;</div>
</div>
<?php endforeach; ?>
<div class="filter-row col-xs-12">
    <div class="col-xs-12 col-sm-12 col-md-6">
        <?=Form::select('columns[]', array('' => 'Please select') + $columns, false, array('class' => 'form-control field-select'))?>
    </div>
    <div class="hidden visible-sm visible-xs clearfix">&nbsp;</div>
    <div class="col-xs-3 col-sm-3 col-md-1"> 
    <?=Form::select('actions[]', $actions, false, array('class' => 'form-control action-select'))?>
    </div>
    <div class="col-xs-7 col-sm-7 col-md-2"> 
    <input type="text" class="form-control" placeholder="Value" name="values[]" />
    </div>
    <div class="col-xs-2 col-sm-2"> 
    <button type="button" class="btn btn-info add-filter"><span class="glyphicon glyphicon-plus"></span> Add</button>
    </div>
    <div class="clearfix">&nbsp;</div>
</div>
<div class="clearfix">&nbsp;</div>
<div id="sorting">
    <?php $sorting = Arr::get($_GET, 'sort', array()); foreach ($sorting as $sort):?>
    <input type="hidden" name="sort[]" value="<?=$sort?>" />
    <?php endforeach; $sorting = array_flip($sorting);?>
</div>
<div class="clearfix">&nbsp;</div>
<input type="hidden" id="status-filter" name="status" value="<?=Arr::get($_GET, 'status')?>" />
</form>
<div class="col-xs-12">
<?=$pager = View::factory('Pager');?>
</div>
<div class="clearfix">&nbsp;</div>
<ul class="nav nav-tabs status-filter">
    <li role="presentation" <?=$_GET['status'] === -1 ? 'class="active"' : ''?>><a href="javascript:;" data-id="-1">All tickets of work</a></li>
    <?php foreach (Enums::$statuses as $key => $status):?>
    <li role="presentation" <?=$_GET['status'] === $key ? 'class="active"' : ''?>><a href="javascript:;" data-id="<?=$key?>"><?=$status?></a></li>
    <?php endforeach;?>
</ul>
<form action="<?=URL::base()?>search/assign" method="post">
<?php $columns = array_flip(explode(',', Group::current('columns')));?>
<table class="table small">
    <tr>
        <?php if (Group::current('allow_assign')):?>
        <th><input type="checkbox" class="checkbox check-all" /></th>
        <?php endif;?>
        
        <th class="sortable" data-id="id">Ticket ID</th>
        <?php if (isset($columns['last_update'])):?>
        <th class="hidden-sm hidden-xs dropdown sortable <?=Arr::get($_GET, 'start') || Arr::get($_GET, 'end') ? 'bg-warning' : ''?>" data-id="update">
            <a href="javascript:;" class="dropdown-toggle" data-toggle="collapse" data-target="#filter-update">
                Last update
            </a>
            <ul class="collapse dropdown-menu" id="filter-update" data-id="update">
                <li class="dropdown-header">Add filter:</li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'start'), array('data-target' => '#updated-start','class' => 'form-control datepicker', 'placeholder' => 'Start date'))?></li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'end'), array('data-target' => '#updated-end', 'class' => 'form-control datepicker', 'placeholder' => 'End date'))?></li>
                <li class="dropdown-header">
                    <button class="btn btn-success date-filter" type="button">Apply</button>
                    <button class="btn btn-warning filter-clear" type="button">Clear</button>
                    <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse" data-target="#filter-update">Cancel</button>
                </li>
            </ul>
        </th>
        <?php endif;?>

        <?php if (isset($columns['last_submit'])):?>
        <th class="hidden-sm hidden-xs dropdown sortable <?=Arr::get($_GET, 'submit-start') || Arr::get($_GET, 'submit-end') ? 'bg-warning' : ''?>" data-id="submit">
            <a href="javascript:;" class="dropdown-toggle" data-toggle="collapse" data-target="#filter-submit">
                Last submit
            </a>
            <ul class="collapse dropdown-menu" id="filter-submit">
                <li class="dropdown-header">Add filter:</li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'submit-start'), array('data-target' => '#submit-start','class' => 'form-control datepicker', 'placeholder' => 'Start date'))?></li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'submit-end'), array('data-target' => '#submit-end', 'class' => 'form-control datepicker', 'placeholder' => 'End date'))?></li>
                <li class="dropdown-header">
                    <button class="btn btn-success date-filter" type="button">Apply</button>
                    <button class="btn btn-warning filter-clear" type="button">Clear</button>
                    <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse" data-target="#filter-submit">Cancel</button>
                </li>
            </ul>
        </th>
        <?php endif;?>

        <?php if (isset($columns['status']) && Group::current('show_all_jobs') && $_GET['status'] == -1):?>
        <th class="hidden-sm hidden-xs">Job status</th>
        <?php endif;?>
        
        <?php if (isset($columns['types'])):?>
        <th class="hidden-xs">Assigned works</th>
        <?php endif;?>

        <?php if (isset($columns['companies'])):?>
        <th class="hidden-sm hidden-xs">Assigned companies</th>
        <?php endif;?>

        <?php if (isset($columns['settings'])):?>
        <th class="hidden-sm hidden-xs">Settings</th>
        <?php endif;?>

        <?php if (isset($columns['pending'])):?>
        <th class="hidden-sm hidden-xs">Pending submissions</th>
        <?php endif;?>
        
        <?php if (isset($columns['attachments'])):?>
        <th class="hidden-xs">Attachments</th>
        <?php endif;?>

        <?php foreach (Columns::get_search() as $id => $type):?>
        <th class="dropdown sortable" data-id="data-<?=$id?>">
            <a href="javascript:;" class="dropdown-toggle" data-toggle="collapse" data-target="#filter-<?=$id?>">
                <?=Columns::get_name($id)?>
            </a>
                <ul class="collapse dropdown-menu"  id="filter-<?=$id?>" data-id="<?=$id?>">
                    <li class="dropdown-header">Add filter:</li>
                    <li><?=Form::select(NULL, $actions, false, array('class' => 'form-control'))?></li>
                    <li><?=Form::input(NULL, NULL, array('class' => 'form-control' . (Columns::get_type($id) == 'date' ? ' datepicker' : ''), 'placeholder' => 'Filtering value'))?></li>
                    <li class="dropdown-header">
                        <button class="btn btn-success table-filter" type="button">Apply</button>
                        <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse" data-target="#filter-<?=$id?>">Cancel</button>
                    </li>
                    <?php if (isset($list_values[$id])):?>
                    <li class="dropdown-header">
                        <ul class="list-unstyled" style="max-height: 400px; overflow: auto;">
                            <?php foreach ($list_values[$id] as $value):?>
                                <li><label><input type="checkbox" data-value="<?=Columns::output($value, Columns::get_type($id))?>" /><?=Columns::output($value, Columns::get_type($id))?></label></li>
                            <?php endforeach;?>
                        </ul>
                    </li>
                    <?php endif;?>
                </ul>                                     
        </th>
        <?php endforeach;?>

        <th>&nbsp;</th>
    </tr>
    <?php foreach ($tickets as $ticket):
        $status = preg_replace('/[^a-z]/', '', strtolower(Arr::path($ticket, JOB_STATUS_COLUMN, '')));
        switch ($status . '') {
            case 'deferred':
            case 'demanddrops':
            case 'heldnbn':
            case 'dirty':
                $status = '#fcc';
                break;
            case "inprogress":
                $status = '#ffc';
                break;
            case 'completed':
            case 'tested':
                $status = '#8c8';
                break;
            case 'built':
                $status = '#cfc';
                break;
            default:
                $status = 'lightcyan';
        }
    ?>
    <tr bgcolor="<?=$status?>">
        <?php if (Group::current('allow_assign')):?>
        <td><input type="checkbox" class="checkbox" name="job[<?=$ticket['_id']?>]" /></td>
        <?php endif;?>
        
        <td><?=HTML::chars($ticket['_id'])?></td>

        <?php if (isset($columns['last_update'])):?>
        <td class="hidden-sm hidden-xs"><?=date('d-m-Y H:i', Arr::get($ticket, 'last_update', $ticket['created']))?></td>
        <?php endif;?>

        <?php if (isset($columns['last_submit'])):?>
        <td class="hidden-sm hidden-xs"><?=Arr::get($ticket, 'last_submit') ? date('d-m-Y H:i', $ticket['last_submit']) : ''?></td>
        <?php endif;?>

        <?php if (isset($columns['status']) && Group::current('show_all_jobs') && $_GET['status'] == -1):?>
        <td class="hidden-sm hidden-xs"><?=Arr::get(Enums::$statuses, Arr::get($ticket, 'status', 0), 'Unknown')?></td>
        <?php endif;?>
        
        <?php if (isset($columns['types'])):?>
        <?php if (Group::current('allow_assign')):?>
        <td class="hidden-xs"><?=HTML::chars(implode(', ', array_intersect_key($types, Arr::get($ticket, 'assigned', array()))))?></td>
        <?php else:?>
        <td class="hidden-xs"><?=HTML::chars(implode(', ', array_intersect_key($types, array_filter(Arr::get($ticket, 'assigned', array()), function($x) { return $x == User::current('company_id');}))))?></td>
        <?php endif;?>
        <?php endif;?>

        <?php if (isset($columns['companies'])):?>
        <td class="hidden-sm hidden-xs"><?=HTML::chars(implode(', ', array_intersect_key($companies, array_flip(Arr::get($ticket, 'assigned', array())))))?></td>
        <?php endif;?>

        <?php if (isset($columns['settings'])):?>
        <td class="hidden-sm hidden-xs text-info">
            <?php foreach (Columns::$settings_img as $key => $value) if (Arr::get($ticket, $key)):?>
            <span class="glyphicon glyphicon-<?=$value?>" title="<?=HTML::chars(Arr::get(Columns::$settings, $key))?>"></span>
            <?php endif;?>
        </td>
        <?php endif;?>

        <?php if (isset($columns['pending'])):?>
        <td class="hidden-sm hidden-xs"><?=Arr::get($submissions, $ticket['_id'])?></td>
        <?php endif;?>
        
        <?php if (isset($columns['attachments'])):?>
        <td class="hidden-xs"><?=Arr::get($attachments, $ticket['_id'])?></td>
        <?php endif;?>

        <?php foreach (Columns::get_search() as $id => $name):?>
        <td><?=Columns::output(Arr::path($ticket, 'data.'.$id), Columns::get_type($id))?></td>
        <?php endforeach;?>

        <td>
            <?php if (Group::current('allow_forms') && !Arr::get($ticket, 'locked') && in_array(User::current('company_id'), Arr::get($ticket, 'assigned', array(), true))):?>
            <a href="<?=URL::base()?>search/form/<?=$ticket['_id']?>" class="btn btn-success"><span class="glyphicon glyphicon-list-alt"></span> Submit information</a>
            <?php endif;?>
            <a href="<?=URL::base()?>search/view/<?=$ticket['_id']?>" class="btn btn-info"><span class="glyphicon glyphicon-search"></span> View</a>
            <?php if (Group::current('allow_reports')):?>
            <a href="<?=URL::base()?>imex/reports?ticket=<?=$ticket['_id']?>" class="btn btn-warning"><span class="glyphicon glyphicon-list"></span> Reports</a>
            <a href="<?=URL::base()?>assign?ticket=<?=$ticket['_id']?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> Assign logs</a>
            <?php endif;?>
            <?php if (Group::current('allow_submissions')):?>
            <a href="<?=URL::base()?>submissions?ticket=<?=$ticket['_id']?>" class="btn btn-danger"><span class="glyphicon glyphicon-check"></span> Submissions</a>
            <?php endif;?>
        </td>
    </tr>
    <?php endforeach;?>
</table>
    <?php if (!$tickets):?>
        <div class="clearfix">&nbsp;</div>
        <div class="text-center text-danger">
        <h3>No jobs found!</h3>
        </div>
    <?php endif;?>    
<div class="col-xs-12">
<?=$pager;?>
</div>
<?php if (Group::current('allow_assign')):?>
<div class="col-sm-6 col-xs-12">
    <?=Form::select('type', array('' => 'Please, select works type...') + $types, false, array('class' => 'form-control'))?>
</div>
<div class="hidden visible-xs clearfix">&nbsp;</div>
<div class="col-sm-6 col-xs-12">
    <?=Form::select('company', array('' => 'Please, select company...', -1 => 'Unassign jobs') + $companies, false, array('class' => 'form-control'))?>
</div>
<div class="clearfix">&nbsp;</div>
<div class="col-xs-12">
    <button type="submit" class="btn btn-warning assign-jobs">Assign jobs</button>
    <button type="submit" class="btn btn-danger archive-jobs">Archive jobs</button>
    <button type="submit" class="btn btn-success complete-jobs">Complete jobs</button>
    <button type="submit" class="btn btn-primary reset-jobs">Reset jobs</button>
    <?php if (Group::current('allow_reports')):?>
    <button type="submit" class="btn btn-info export-jobs"><span class="glyphicon glyphicon-export"></span>Export jobs</button>
    <?php endif;?>
</div>
<?php endif;?>
<div class="clearfix">&nbsp;</div>
</form>