<?php $week = strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time());?>
<form action="" method="get" class="report-form-container">

    <div class="submission-filter-container">
        <div class="col-xs-12 col-md-6">
        <label class="date-range-label">Date range: </label>
        <span class="date-range-container">
            <div class="daterange" class="pull-right" data-start="start" data-end="end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                <span></span> <b class="caret"></b>
            </div>
        </span>
        </div>
        <div class="col-xs-12 col-md-6">
            <label class="date-range-label">Ticket ID:</label>
        <span class="filter-select-container">
            <input type="text" class="form-control" id="ticket-id" placeholder="Ticket ID" name="ticket" value="<?=Arr::get($_GET, 'ticket')?>" />
        </span>
        </div>

        <div class="col-xs-12 col-md-6">
        <label class="date-range-label">Date range(Approved) :</label>
         <span class="date-range-container">
            <div class="daterange" class="pull-right"  data-start="app-start" data-end="app-end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                <span></span> <b class="caret"></b>
            </div>
        </span>

        </div>
        <div class="col-xs-12 col-md-6">
            <label class="date-range-label filter-select-label">FSA: </label>

            <span class="filter-select-container" data-fsa-selected="<?=isset($_GET['fsa']) ? implode(',',Arr::get($_GET, 'fsa')):''?>">
            <?=Form::select('fsa[]', [], NULL, array('class' => 'fsa-filter multiselect', 'multiple' => 'multiple'))?>
        </span>
        </div>

        <div class="col-xs-12 col-md-6">
        <label class="date-range-label filter-select-label">Is job finished? </label>
        <span class="filter-select-container">
            <select name="finished" class="multiselect">
                <option value="">Select</option>
                <option value="Yes" <?=Arr::get($_GET, 'finished')=='Yes' ? 'selected': ''?>>Yes</option>
                <option value="No"  <?=Arr::get($_GET, 'finished')=='No' ? 'selected': ''?>>No</option>
            </select>
        </span>
        </div>

        <div class="col-xs-12 col-md-6">
            <label class="date-range-label filter-select-label">FSAM: </label>
            <span class="filter-select-container" data-fsam-selected="<?=isset($_GET['fsam']) ? implode(',',Arr::get($_GET, 'fsam')):''?>">
            <?=Form::select('fsam[]', [], NULL, array('class' => 'fsam-filter multiselect', 'multiple' => 'multiple'))?>
        </span>
        </div>


        <?php if (Group::current('allow_assign')):?>
        <div class="col-xs-12 col-md-6">
            <label class="date-range-label filter-select-label">Contractors :</label>
        <span class="filter-select-container">
            <select name="company" class="multiselect">
                <option value="">All contractors</option>
                <?php foreach ($companies as $key => $value):?>
                    <option value="<?=$key?>" <?=$key == Arr::get($_GET, 'company') ? 'selected' : ''?>><?=$value?></option>
                <?php endforeach;?>
            </select>
        </span>
        </div>
        <?php endif;?>
        <div class="col-xs-12 col-md-6">
        <label class="date-range-label filter-select-label">FDA: </label>
            <span class="filter-select-container" data-fda-selected="<?=isset($_GET['fda']) ? implode(',',Arr::get($_GET, 'fda')):''?>">
            <?=Form::select('fda[]', [], NULL, array('class' => 'fda-filter multiselect', 'multiple' => 'multiple'))?>
        </span>
        </div>
        <div class="col-xs-12 col-md-6">
            <label class="date-range-label">Address:</label>
            <span class="filter-select-container">
                <input type="text" class="form-control"placeholder="Address" name="address" value="<?=Arr::get($_GET, 'address')?>" />
            </span>
        </div>
    </div>
        <div class="clearfix">&nbsp;</div>
    <div class="col-xs-12 col-md-6">
        <button type="submit" class="btn btn-success">Apply filters</button>
    </div>

    <div class="clearfix">&nbsp;</div>

    <div id="sorting" class="hidden">
        <?php $sorting = Arr::get($_GET, 'sort', array()); foreach ($sorting as $sort):?>
            <input type="hidden" name="sort[]" value="<?=$sort?>" />
        <?php endforeach; $sorting = array_flip($sorting);?>
    </div>


<?=Form::hidden('start', Arr::get($_GET, 'start', date('d-m-Y', $week)), array('class' => 'form-control datepicker', 'placeholder' => 'Start date', 'id' => 'start'))?>
<?=Form::hidden('end', Arr::get($_GET, 'end', date('d-m-Y')), array('class' => 'form-control datepicker', 'placeholder' => 'End date', 'id' => 'end'))?>

<?=Form::hidden('app-start', Arr::get($_GET, 'app-start'), array('class' => 'form-control datepicker', 'placeholder' => 'Start date (Approved)', 'id' => 'app-start'))?>
<?=Form::hidden('app-end', Arr::get($_GET, 'app-end'), array('class' => 'form-control datepicker', 'placeholder' => 'End date (Approved)', 'id' => 'app-end'))?>
</form>
<h2>Total <?=count($submissions)?> job(s) found:</h2>
<div class="upload-buttons text-right">
<a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
<a href="?export2&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-primary"><span class="glyphicon glyphicon-export"></span> Export grouped</a>
</div>
<table class="table table-hover">
    <tr class="text-center">
        <th class="sortable" data-id="submission">Submission date</th>
        <th class="sortable" data-id="approval">Approval date</th>
        <th>User</th>
        <?php if (Group::current('allow_assign')):?><th>Company</th><?php endif;?>
        <th>Column</th>
        <th>Value</th>
    </tr>
    <?php foreach ($submissions as $job => $list):?>
        <tr class="text-center">
            <th colspan="<?=(Group::current('allow_assign')) ? 6 : 5?>"><a href="<?=URL::base()?>search/view/<?=$job?>"><?=$job?></a></th>
        </tr>

        <?php foreach ($list as $submission): $key = substr($submission['key'], 5); $status = Arr::get($submission, 'active', 0);?>
        <tr class="text-center <?=$status > 0 ? 'yellow' : ($status < 0 ? 'lgreen' : 'rose')?>">
            <td><?=date('d-m-Y H:i', $submission['update_time'])?></td>
            <td><?=isset($submission['process_time']) ? date('d-m-Y H:i', $submission['process_time']) : ''?></td>
            <td><?=User::get($submission['user_id'], 'login')?></td>
            <?php if (Group::current('allow_assign')):?><td><?=Arr::get($companies, User::get($submission['user_id'], 'company_id'), 'Unknown')?></td><?php endif;?>
            <td><?=Columns::get_name($key)?></td>
            <td class="<?=strlen(Columns::output($submission['value'], Columns::get_type($key))) > 2 ? 'shorten' : ''?>"><?=Columns::output($submission['value'], Columns::get_type($key))?></td>
        </tr>
    <?php endforeach;?>
    <?php endforeach;?>
</table>
<div class="upload-buttons text-right">
    <a href="?export&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-info"><span class="glyphicon glyphicon-export"></span> Export</a>
    <a href="?export2&company=<?=Arr::get($_GET, 'company')?>&start=<?=Arr::get($_GET, 'start', date('d-m-Y', $week))?>&end=<?=Arr::get($_GET, 'end', date('d-m-Y'))?>" class="btn btn-primary"><span class="glyphicon glyphicon-export"></span> Export grouped</a>
</div>