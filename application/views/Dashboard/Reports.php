<?php
$values = array(
    'total' => array_sum(array_column($fsa, 'total')),
    'tested' => array_sum(array_column($fsa, 'tested')),
    'built' => array_sum(array_column($fsa, 'built')),
);
?>

    <script type="application/javascript">
        var REPORTDATA = {
            isAdmin: <?=Group::current('show_all_jobs') ?>,
            totalTickets:
            {
                data:{
                    total: <?= $values['total']?>,
                    tested: <?= $values['tested']?>,
                    built: <?= $values['built']?>
                }
            }
        };

    </script>

<div class="report-block filter-info-container">
<form class="auto-submit">
    <div class="text-info-filters">
        <div>
            <label class="date-range-label">Date range: </label>
            <span class="date-range-container">
                <div class="daterange" class="pull-right" data-start="start" data-end="end" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    <span></span> <b class="caret"></b>
                </div>
            </span>
            <?=Form::hidden('start', Arr::get($_GET, 'start'), array('id'=>'start'))?>
            <?=Form::hidden('end', Arr::get($_GET, 'end'), array('id'=>'end'))?>
        </div>
    </div>
</form>
</div>

<?php if(Group::current('show_all_jobs')):?>

<div class="report-block">
    <div class="chart_with_list">
        <div class="chart-container full-width height-400" id="pie-total-tickets-assigned"></div>
    </div>
</div>
<div class="report-block">
    <div class="chart_with_list">
        <div class="chart-container full-width height-400" id="pie-total-tickets"></div>
    </div>
</div>
<div class="report-block">
    <div class="charts-expand">
        <button class="btn btn-simple small hidden do-collapse"><i class="glyphicon glyphicon-menu-up"></i> Collapse</button>
        <button class="btn btn-simple small  do-expand"><i class="glyphicon glyphicon-menu-down"></i> Show additional info</button>
    </div>

    <div class="chart-list-container" id="tickets-companies" style="display: none;"></div>
</div>

<div class="report-block">
    <div class="chart-container full-width" id="tickets-stacked"></div>
</div>

<?php else:?>

    <div class="report-block">
        <div class="chart_with_list">
            <div class="chart-container full-width" id="fsa-statuses"></div>
        </div>
    </div>


<?php endif;?>