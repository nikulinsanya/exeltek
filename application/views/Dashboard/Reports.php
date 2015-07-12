<?php
$values = array(
    'total' => array_sum(array_column($fsa, 'total')),
    'tested' => array_sum(array_column($fsa, 'tested')),
    'built' => array_sum(array_column($fsa, 'built')),
);
?>

    <script type="application/javascript">
        var REPORTDATA = {
            totalTickets:
            {
                companyName: '<?= Arr::get($_GET, 'company') ? $companies[Arr::get($_GET, 'company')] : 'All companies'?>',
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
<!--        <div>-->
<!--            --><?php //if (Group::current('allow_assign')):?>
<!--                <label class="date-range-label">Company: </label>-->
<!--                <span class="date-range-container">-->
<!--                    --><?//=Form::select('company', array('' => 'All') + $companies, Arr::get($_GET, 'company'), array('class' => 'form-control'))?>
<!--                </span>-->
<!--            --><?php //endif;?>
<!--        </div>-->
    </div>
</form>
</div>



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







<!--<div class="report-block">-->
<!--    <div class="chart_with_list">-->
<!--        <div class="chart-container" id="worker-report"></div>-->
<!--        <div class="list-container" id="worker-list">-->
<!--            <div class="list-label">Workers</div>-->
<!--            <ul>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="report-block">-->
<!--    <div class="chart_with_list">-->
<!--        <div class="chart-container" id="company-history-report"></div>-->
<!--        <div class="list-container" id="company-list">-->
<!--            <div class="list-label">Companies</div>-->
<!--            <ul>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="report-block">-->
<!--    <div class="chart_with_list">-->
<!--        <div class="chart-container" id="pie-company-report"></div>-->
<!--        <div class="list-container" id="pie-company-list">-->
<!--            <div class="list-label">Companies</div>-->
<!--            <ul>-->
<!--            </ul>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->




<!---->
<!--<div class="col-xs-12 progress-row">-->
<!--    <div class="control-label">-->
<!--        --><?php //$tested = $values['tested'] ? round($values['tested'] / $values['total'] * 100): 0;?>
<!--        --><?php //$built = $values['built'] ? round($values['built'] / $values['total'] * 100) : 0;?>
<!---->
<!--        <strong>-->
<!--            Overall progress: --><?//=$values['total']?><!-- --><?//=$values['total']>1 ? 'tickets' : 'ticket'?>
<!--        </strong>-->
<!--        <div class="progress-label">-->
<!--            Tested: <span class="progress-bar-success">--><?//=$values['tested']?><!--</span>, Built: <span class="progress-bar-warning">--><?//=$values['built']?><!--</span>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="progress">-->
<!--        --><?php //if ($values['tested']):?>
<!--            <div class="progress-bar progress-bar-success" style="width: --><?//=$tested > 1 ? $tested : 1?><!--%" title="--><?//=$values['tested']?><!-- (--><?//=$tested?><!--%) Built and tested">-->
<!--                --><?//=$tested?><!--%-->
<!--            </div>-->
<!--        --><?php //endif;?>
<!--        --><?php //if ($values['built']):?>
<!--            <div class="progress-bar progress-bar-warning" style="width: --><?//=$built > 1 ? $built: 1?><!--%" title="--><?//=$values['built']?><!-- (--><?//=$built?><!--%) Built">-->
<!--                --><?//=$built?><!--%-->
<!--            </div>-->
<!--        --><?php //endif;?>
<!--    </div>-->
<!--</div>-->
<!---->
<!---->
<?php //foreach ($fsa as $key => $values) if ($values['total']):?>
<!--    <div class="col-xs-12 progress-row">-->
<!--        --><?php //$tested = $values['tested'] ? round($values['tested'] / $values['total'] * 100): 0;?>
<!--        --><?php //$built = $values['built'] ? round($values['built'] / $values['total'] * 100) : 0;?>
<!---->
<!--        <div class="control-label">-->
<!--            <strong>-->
<!--                <a href="--><?//=URL::base()?><!--dashboard/fsam--><?//=URL::query(array('fsa' => $key))?><!--">--><?//=$key?><!--: --><?//=$values['total']?><!-- --><?//=$values['total']>1 ? 'tickets' : 'ticket'?><!--</a>-->
<!--            </strong>-->
<!--            <div class="progress-label">-->
<!--                --><?php //if ($values['tested']):?>
<!--                    Tested: <span class="progress-bar-success">--><?//=$values['tested']?><!--</span>-->
<!--                --><?php //endif;?>
<!--                --><?php //if ($values['built']):?>
<!--                    , Built: <span class="progress-bar-warning">--><?//=$values['built']?><!--</span>-->
<!--                --><?php //endif;?>
<!--            </div>-->
<!--        </div>-->
<!--        <div class="progress">-->
<!--            --><?php //if ($values['tested']):?>
<!--                <div class="progress-bar progress-bar-success" style="width: --><?//=$tested > 1 ? $tested : 0?><!--%" title="--><?//=$values['tested']?><!-- (--><?//=$tested?><!--%) Built and tested">-->
<!--                    --><?//=$tested?><!--%-->
<!--                </div>-->
<!--            --><?php //endif;?>
<!--            --><?php //if ($values['built']):?>
<!--                <div class="progress-bar progress-bar-warning" style="width: --><?//=$built > 1 ? $built: 0?><!--%" title="--><?//=$values['built']?><!-- (--><?//=$built?><!--%) Built">-->
<!--                    --><?//=$built?><!--%-->
<!--                </div>-->
<!--            --><?php //endif;?>
<!--        </div>-->
<!--    </div>-->
<?php //endif;?>