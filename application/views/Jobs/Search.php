<div>
    <div class="filter-info-container">
        <label  class="filter_value">Filters:</label>
        <div class="text-info-filters">
            <div>
                <?php $hasFilters = false; if(Arr::get($_GET, 'ticket')){ $hasFilters = true;?>
                    <span class="filter-item">
                            Ticket:
                            <label class="filter_value">
                                <?=Arr::get($_GET, 'ticket')?>
                            </label>
                    </span>
                <?php } ?>
                <?php if(Arr::get($_GET, 'region')){
                    $hasFilters = true;
                ?>
                    <span class="filter-item">
                        Region:
                        <label class="filter_value">
                            <?=$regions[Arr::get($_GET, 'region')]?>
                        </label>
                    </span>
                <?php } ?>
                <?php if(Arr::get($_GET, 'type')){
                    $hasFilters = true;
                ?>
                    <span class="filter-item">
                        Job:
                        <label class="filter_value">
                            <?=$types[Arr::get($_GET, 'type')]?>
                        </label>
                    </span>
                <?php } ?>
                <?php if(Arr::get($_GET, 'company')):
                    $hasFilters = true;
                ?>
                    <span class="filter-item">
                        Assigned:
                        <label class="filter_value">
                            <?php $comps = explode(',',$_GET['company']);$compa = array();?>
                            <?php foreach ($comps as $id => $c):?>
                                <?php $compa[]= Arr::get($companies, $c)?>
                            <?php endforeach; ?>
                            <?=implode(', ',$compa)?>
                        </label>
                    </span>
                    <br/>
                <?php endif; ?>
                <?php if(Arr::get($_GET, 'ex')):
                    $hasFilters = true;
                ?>
                    <span class="filter-item">
                        Previous:
                        <label class="filter_value">
                            <?php $comps = explode(',',$_GET['ex']);$compa = array();?>
                            <?php foreach ($comps as $id => $c):?>
                                <?php $compa[]= Arr::get($companies, $c)?>
                            <?php endforeach; ?>
                            <?=implode(', ',$compa)?>
                        </label>
                    </span>
                <?php endif; ?>
                <?php $cols = Arr::get($_GET, 'columns', array()); foreach ($cols as $id => $column):?>
                    <span class="filter-item">
                        <?php if($cols[$id]){
                            $hasFilters = true;
                        ?>
                            <?=Columns::get_name($column)?>:

                            <label class="filter_value">
                                <?=$actions[Arr::path($_GET, 'actions.' . $id)]?>
                                <?=Arr::path($_GET, 'values.' . $id)?>
                            </label>
                        <?php }?>
                    </span>
                <?php endforeach; ?>
            </div>

            <div>
                <?php if (Group::current('allow_assign')) foreach (Columns::$settings as $id => $name):?>
                    <?php if(Arr::path($_GET, 'settings.' . $id)){
                        $hasFilters = true;
                    ?>
                        <span class="filter-item">
                            <label class="filter_value">
                                <input disabled="disabled" class="checkbox-x" type="checkbox" checked value="<?=Arr::path($_GET, 'settings.' . $id) ? '1' : (Arr::path($_GET, 'settings.' . $id) === '0' ? '0' : '')?>" />
                                <?=HTML::chars($name)?>
                            </label>
                        </span>
                    <?php }?>
                <?php endforeach;?>
                <?php if (Group::current('allow_assign')) foreach (Columns::$settings as $id => $name):?>
                    <?php if(Arr::path($_GET, 'settings.' . $id) === '0'){
                        $hasFilters = true;
                    ?>
                        <span class="filter-item">
                            <label class="filter_value">
                                <input disabled="disabled" class="checkbox-x" type="checkbox" checked value="<?=Arr::path($_GET, 'settings.' . $id) ? '1' : (Arr::path($_GET, 'settings.' . $id) === '0' ? '0' : '')?>" />
                                <?=HTML::chars($name)?>
                            </label>
                        </span>
                    <?php }?>
                <?php endforeach;?>
            </div>

        </div>



        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
            <span class="glyphicon glyphicon-filter"></span>
            Modify filters
        </button>
        <?php if($hasFilters){?>
            <a href="<?=URL::base()?>search?clear" class="btn btn-warning">
                <span class="glyphicon glyphicon-remove"></span>
                Clear</a>
        <?php }else{?>
            <label class="filter_value no-filters">None</label>
        <?php }?>


    </div>

    <!-- Modal Filters-->
    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form class="filters-form" action="" method="get">

                    <!--template filter row-->
                    <!--@todo - move to js templates -->
                        <div id="filter_row_template" style="display: none;">
                            <div class="filter-row">
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <?=Form::select('columns[]', array('' => 'Please select') + $columns, false, array('class' => 'field-select'))?>
                                </div>
                                <div class="hidden visible-sm visible-xs clearfix">&nbsp;</div>
                                <div class="col-xs-3 col-sm-3 col-md-3">
                                    <?=Form::select('actions[]', $actions, false, array('class' => 'action-select'))?>
                                </div>
                                <div class="col-xs-7 col-sm-7 col-md-2">
                                    <input type="text" class="form-control action-value" placeholder="Value" name="values[]" />
                                </div>
                                <div class="col-xs-2 col-sm-1">
                                    <button type="button" class="btn btn-info add-filter"><span class="glyphicon glyphicon-plus"></span></button>
<!--                                    <button type="button" class="btn btn-danger remove-filter"><span class="glyphicon glyphicon-minus"></span></button>-->
                                </div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </div>




                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Modify filters</h4>
                    </div>
                    <div class="modal-body" id="filter-form">
                        <div class="col-xs-4 col-sm-4 col-md-2">
                            <input type="text" class="form-control" id="ticket-id" placeholder="Ticket ID" name="ticket" value="<?=Arr::get($_GET, 'ticket')?>" />
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-2">
                            <?=Form::select('region', array('' => 'All regions') + $regions, Arr::get($_GET, 'region'), array('class' => 'form-control'))?>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-2">
                            <?=Form::select('type', array('' => 'All jobs') + $types, Arr::get($_GET, 'type'), array('class' => 'form-control'))?>
                        </div>
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
                                <?=Form::select('company', array('' => 'Any company') + $companies, isset($_GET['company']) ? explode(',',$_GET['company']) : [], array('class' => 'multiselect form-control width-140', 'multiple'=>'multiple'))?>
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-1">
                                <label class="control-label">
                                    Previous:
                                </label>
                            </div>
                            <div class="col-xs-4 col-sm-4 col-md-2">
                                <?=Form::select('ex', array('' => 'Any company') + $companies, isset($_GET['ex']) ? explode(',',$_GET['ex']) : [], array('class' => 'form-control multiselect width-140', 'multiple'=>'multiple'))?>
                            </div>
                        <?php endif;?>
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
                        <div class="clearfix">&nbsp;</div>
                        <?php $cols = Arr::get($_GET, 'columns', array()); foreach ($cols as $id => $column):?>
                            <div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <?=Form::select('columns[]', array('' => 'Please select') + $columns, $column, array('class' => 'selectize'))?>
                                </div>
                                <div class="col-xs-3 col-sm-3 col-md-3">
                                    <?=Form::select('actions[]', $actions, Arr::path($_GET, 'actions.' . $id), array('class' => 'selectize'))?>
                                </div>
                                <div class="col-xs-7 col-sm-7 col-md-2">
                                    <input type="text" class="form-control" placeholder="Value" name="values[]" value="<?=Arr::path($_GET, 'values.' . $id)?>"/>
                                </div>
                                <div class="col-xs-2 col-sm-1">
                                    <button type="button" class="btn btn-danger remove-filter"><span class="glyphicon glyphicon-minus"></span></button>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        <?php endforeach; ?>
                        <div class="filter-row">
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <?=Form::select('columns[]', array('' => 'Please select') + $columns, false, array('class' => 'selectize'))?>
                            </div>
                            <div class="hidden visible-sm visible-xs clearfix">&nbsp;</div>
                            <div class="col-xs-3 col-sm-3 col-md-3">
                                <?=Form::select('actions[]', $actions, false, array('class' => 'selectize'))?>
                            </div>
                            <div class="col-xs-7 col-sm-7 col-md-2">
                                <input type="text" class="form-control form-value" placeholder="Value" name="values[]" />
                            </div>
                            <div class="col-xs-2 col-sm-1">
                                <button type="button" class="btn btn-info add-filter"><span class="glyphicon glyphicon-plus"></span></button>
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

                    </div>
                    <div class="modal-footer">
                        <a href="<?=URL::base()?>search?clear" class="btn btn-warning" id="clearFilters">Clear</a>
                        <button type="submit" class="btn btn-success" id="hideModalFilters">Apply filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



</div>






<div class="col-xs-12 text-center with-pager">
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
<table class="table small" id="search-table">
    <tr class="text-center table-header">
        <th class="checkbox-container"><input type="checkbox" class="checkbox check-all" /></th>

        <th class="sortable" data-id="id">Ticket ID</th>
        <?php if (isset($columns['last_update'])):?>
        <th class="hidden-sm hidden-xs dropdown sortable date-td <?=Arr::get($_GET, 'start') || Arr::get($_GET, 'end') ? 'bg-warning' : ''?>" data-id="update">
            <a href="#" class="dropdown-toggle" data-toggle="collapse" data-target="#filter-update">
                Last update
            </a>
            <ul class="collapse dropdown-menu" id="filter-update" data-id="update">
                <li class="dropdown-header">Add filter:</li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'start'), array('data-target' => '#updated-start','class' => 'form-control datepicker', 'placeholder' => 'Start date'))?></li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'end'), array('data-target' => '#updated-end', 'class' => 'form-control datepicker', 'placeholder' => 'End date'))?></li>
                <li class="dropdown-header buttons-row">
                    <button class="btn btn-success date-filter" type="button">Apply</button>
                    <button class="btn btn-warning filter-clear" type="button">Clear</button>
                    <button class="btn btn-danger dropdown-toggle" type="button" data-toggle="collapse" data-target="#filter-update">Cancel</button>
                </li>
            </ul>
        </th>
        <?php endif;?>

        <?php if (isset($columns['last_submit'])):?>
        <th class="hidden-sm hidden-xs dropdown sortable date-td <?=Arr::get($_GET, 'submit-start') || Arr::get($_GET, 'submit-end') ? 'bg-warning' : ''?>" data-id="submit">
            <a href="#" class="dropdown-toggle" data-toggle="collapse" data-target="#filter-submit">
                Last submit
            </a>
            <ul class="collapse dropdown-menu" id="filter-submit">
                <li class="dropdown-header">Add filter:</li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'submit-start'), array('data-target' => '#submit-start','class' => 'form-control datepicker', 'placeholder' => 'Start date'))?></li>
                <li><?=Form::input(NULL, Arr::get($_GET, 'submit-end'), array('data-target' => '#submit-end', 'class' => 'form-control datepicker', 'placeholder' => 'End date'))?></li>
                <li class="dropdown-header buttons-row">
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

        <?php if (isset($columns['ex'])):?>
            <th class="hidden-sm hidden-xs">Previously assigned companies</th>
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
        <th class="dropdown sortable" data-id="data-<?=$id?>" data-editable-cell>
            <a href="#" class="dropdown-toggle" data-toggle="collapse" data-target="#filter-<?=$id?>">
                <?=Columns::get_name($id)?>
            </a>
                <ul class="collapse dropdown-menu"  id="filter-<?=$id?>" data-id="<?=$id?>">
                    <li class="dropdown-header">Add filter:</li>
                    <li><?=Form::select(NULL, $actions, false, array('class' => 'selectize'))?></li>
                    <li><?=Form::input(NULL, NULL, array('class' => 'form-control' . (Columns::get_type($id) == 'date' ? ' datepicker' : ''), 'placeholder' => 'Filtering value'))?></li>
                    <li class="dropdown-header buttons-row">
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

        <th class="table-buttons"></th>
    </tr>
    <?php foreach ($tickets as $ticket):
        $status = preg_replace('/[^a-z]/', '', strtolower(Arr::path($ticket, JOB_STATUS_COLUMN, '')));
        switch ($status . '') {
            case 'deferred':
            case 'demanddrops':
            case 'heldnbn':
            case 'dirty':
                $status = 'rose';
                break;
            case "inprogress":
                $status = 'yellow';
                break;
            case 'completed':
            case 'tested':
                $status = 'green';
                break;
            case 'built':
                $status = 'lgreen';
                break;
            default:
                $status = 'lightcyan';
        }
    ?>
    <tr class="text-center <?=$status?>">
        <td class="checkbox-container"><input type="checkbox" class="checkbox" data-id=<?=$ticket['_id']?> name="job[<?=$ticket['_id']?>]" /></td>

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

        <?php if (isset($columns['ex'])):?>
            <td class="hidden-sm hidden-xs"><?=HTML::chars(implode(', ', array_intersect_key($companies, array_flip(Arr::get($ticket, 'ex', array())))))?></td>
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

        <?php foreach (Columns::get_search() as $id => $name): $value = Columns::output(Arr::path($ticket, 'data.'.$id), Columns::get_type($id));?>
        <td <?=strlen($value) > 100 ? 'class="shorten data-editable-cell"' : 'data-editable-cell'?>><?=$value?></td>
        <?php endforeach;?>



        <td  class="table-buttons">
            <?php if (Group::current('allow_forms') && !Arr::get($ticket, 'locked') && in_array(User::current('company_id'), Arr::get($ticket, 'assigned', array(), true))):?>
            <a href="<?=URL::base()?>search/form/<?=$ticket['_id']?>" class="btn btn-success col-xs-12"  data-toggle="tooltip" data-placement="top"><span class="glyphicon glyphicon-list-alt"></span> Submit data</a>
            <?php endif;?>
            <a href="<?=URL::base()?>search/view/<?=$ticket['_id']?>" class="btn btn-info col-xs-12"  data-toggle="tooltip" data-placement="top"><span class="glyphicon glyphicon-search"></span> View</a>
            <?php if (Group::current('allow_reports')):?>
            <a href="<?=URL::base()?>imex/reports?ticket=<?=$ticket['_id']?>" class="btn btn-warning col-xs-12"  data-toggle="tooltip" data-placement="top"><span class="glyphicon glyphicon-list"></span> Reports</a>
            <a href="<?=URL::base()?>assign?ticket=<?=$ticket['_id']?>" class="btn btn-primary col-xs-12"  data-toggle="tooltip" data-placement="top"><span class="glyphicon glyphicon-pencil"></span> Assign logs</a>
            <?php endif;?>
            <?php if (Group::current('allow_submissions')):?>
            <a href="<?=URL::base()?>submissions?ticket=<?=$ticket['_id']?>" class="btn btn-danger col-xs-12" data-toggle="tooltip" data-placement="top"><span class="glyphicon glyphicon-check"></span> Submissions</a>
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
<div class="col-xs-12 text-center">
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
<?php endif;?>
<div class="col-xs-12">
    <?php if (Group::current('allow_assign')):?>
    <button type="submit" class="btn btn-warning assign-jobs">Assign jobs</button>
    <button type="submit" class="btn btn-danger archive-jobs">Archive jobs</button>
    <button type="submit" class="btn btn-success complete-jobs">Complete jobs</button>
    <button type="submit" class="btn btn-primary reset-jobs">Reset jobs</button>
    <button type="button" class="btn btn-success batch-jobs"><span class="glyphicon glyphicon-edit"></span>Batch Edit</button>
    <button type="button" class="btn btn-primary export-attachments"><span class="glyphicon glyphicon-export"></span>Export Attachments</button>
    <?php endif;?>
    <?php if (Group::current('allow_reports')):?>
    <button type="submit" class="btn btn-info export-jobs"><span class="glyphicon glyphicon-export"></span>Export jobs</button>
    <?php endif;?>
    <button type="submit" class="btn btn-info export-result"><span class="glyphicon glyphicon-export"></span>Export search result</button>


</div>
<div class="clearfix">&nbsp;</div>

</form>


<div class="modal fade" id="tableRowDetails" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Ticket details</h4>
            </div>
            <div class="modal-body" id="table-row-details">
                <table class="table small">
                    <tr class="text-center tr-header">
                        <th>Ticket ID</th>
                        <?php if (isset($columns['last_update'])):?><th class="hidden-sm hidden-xs">Last update</th><?php endif;?>
                        <?php if (isset($columns['last_submit'])):?><th class="hidden-sm hidden-xs"> Last submit</th><?php endif;?>
                        <?php if (isset($columns['status']) && Group::current('show_all_jobs') && $_GET['status'] == -1):?><th class="hidden-sm hidden-xs">Job status</th><?php endif;?>
                        <?php if (isset($columns['types'])):?><th class="hidden-xs">Assigned works</th><?php endif;?>
                        <?php if (isset($columns['companies'])):?><th class="hidden-sm hidden-xs">Assigned companies</th><?php endif;?>
                        <?php if (isset($columns['settings'])):?><th class="hidden-sm hidden-xs">Settings</th><?php endif;?>
                        <?php if (isset($columns['pending'])):?><th class="hidden-sm hidden-xs">Pending submissions</th><?php endif;?>
                        <?php if (isset($columns['attachments'])):?><th class="hidden-xs">Attachments</th><?php endif;?>
                        <?php foreach (Columns::get_search() as $id => $type):?>
                        <th><?=Columns::get_name($id)?></th>
                        <?php endforeach;?>
                    </tr>
                    <tr class="text-center tr-body"></tr>
                </table>
            </div>
            <div class="modal-footer" id="tableRowButtons">
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="batchEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Ticket details</h4>
            </div>
            <div class="modal-body text-center" id="table-row-details">
                <table class="table small edit-tickets-table">

                </table>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <span class="filter-item">
                    Your Username:
                    <label class="filter_value">
                        <input type="text" id="your-username" placeholder="">
                    </label>
                </span>
                <button type="submit" class="btn btn-success batch-ticket">Update</button>
            </div>
        </div>
    </div>
</div>