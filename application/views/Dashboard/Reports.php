    <script type="application/javascript">
        var REPORTDATA = {
            isAdmin: <?=Group::current('show_all_jobs') ?>
        };

    </script>
    <?php if(Group::current('show_all_jobs')):?>
        <div class="row" id="report-container">
            <div class="col-sm-3 col-md-2 sidebar">
                <ul class="nav nav-sidebar">
                    <li data-id="main" class="active"><a class="switcher" href="#main">Overview</a></li>
                    <li data-id="company"><a class="switcher" href="#company">Company progress</a></li>
                    <li data-id="time"><a class="switcher" href="#time">Time progress</a></li>
                    <li data-id="stacked"><a class="switcher" href="#stacked">Stacked</a></li>
                    <li data-id="fsa-fsam"><a class="switcher" href="#fsa-fsam">Fsa/Fsam</a></li>
                </ul>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 dashboard tab-content">
                    <div class="tab-pane active" data-id="main">
                        <form id="overview-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-overview" data-end="end-overview" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                            <?=Form::hidden('start-overview', Arr::get($_GET, 'start'), array('id'=>'start-overview'))?>
                            <?=Form::hidden('end-overview', Arr::get($_GET, 'end'), array('id'=>'end-overview'))?>
                        </form>
                        <div class="report-block">
                            <div class="chart_with_list">
                                <div class="chart-container full-width height-400" id="pie-total-tickets-assigned"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" data-id="company">
                        <form id="company-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-company" data-end="end-company" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                            <?=Form::hidden('start-company', Arr::get($_GET, 'start'), array('id'=>'start-company'))?>
                            <?=Form::hidden('end-company', Arr::get($_GET, 'end'), array('id'=>'end-company'))?>
                        </form>
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
                    </div>
                    <div class="tab-pane" data-id="time">
                        <form id="time-report" style="float: left; margin-right: 10px;">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-time" data-end="end-time" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                            <?=Form::hidden('start-time', Arr::get($_GET, 'start'), array('id'=>'start-time'))?>
                            <?=Form::hidden('end-time', Arr::get($_GET, 'end'), array('id'=>'end-time'))?>
                        </form>
                        <div class="report-block history-container" style="padding-top: 0px;">
                            <div class="btn-group" role="group">
                                <button type="button" data-attr="d" data-format="YYYY-MM-DD" class="btn btn-default">Daily</button>
                                <button type="button" data-attr="w" data-format="YYYY-WW" class="btn btn-default">Weekly</button>
                                <button type="button" data-attr="m" data-format="YYYY-MM" class="active btn btn-default">Monthly</button>
                            </div>
                            <div class="chart-container full-width" id="history-block"></div>
                        </div>
                    </div>
                    <div class="tab-pane" data-id="stacked">
                        <form id="stacked-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-stacked" data-end="end-stacked" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                            <?=Form::hidden('start-stacked', Arr::get($_GET, 'start'), array('id'=>'start-stacked'))?>
                            <?=Form::hidden('end-stacked', Arr::get($_GET, 'end'), array('id'=>'end-stacked'))?>
                        </form>
                        <div class="report-block">
                            <div class="chart-container full-width" id="tickets-stacked"></div>
                        </div>
                    </div>
                    <div class="tab-pane" data-id="fsa-fsam">
                        <form id="fsa-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-fsa" data-end="end-fsa" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                            <?=Form::hidden('start-fsa', Arr::get($_GET, 'start'), array('id'=>'start-fsa'))?>
                            <?=Form::hidden('end-fsa', Arr::get($_GET, 'end'), array('id'=>'end-fsa'))?>
                        </form>
                        <div class="report-block">
                            <div class="chart_with_list">
                                <div class="chart-container full-width" id="fsa-statuses"></div>
                            </div>
                        </div>
                        <div class="report-block fsam-statuses" style="display:none;">
                            <div class="chart_with_list">
                                <div class="chart-container full-width" id="fsam-statuses"></div>
                            </div>
                        </div>

                    </div>
            </div>
        </div>
<?php else:?>

        <div class="row" id="report-container">
            <div class="col-sm-3 col-md-2 sidebar">
                <ul class="nav nav-sidebar">
                    <li data-id="main" class="active"><a class="switcher" href="#main">Overview</a></li>
                    <li data-id="time"><a class="switcher" href="#time">Time progress</a></li>
                    <li data-id="fsa-fsam"><a class="switcher" href="#fsa-fsam">Fsa/Fsam</a></li>
                </ul>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 dashboard tab-content">
                <div class="tab-pane active" data-id="main">
                    <form id="overview-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-overview" data-end="end-overview" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                        <?=Form::hidden('start-overview', Arr::get($_GET, 'start'), array('id'=>'start-overview'))?>
                        <?=Form::hidden('end-overview', Arr::get($_GET, 'end'), array('id'=>'end-overview'))?>
                    </form>
                    <div class="report-block">
                        <div class="chart_with_list">
                            <div class="chart-container full-width height-400" id="pie-total-tickets-assigned"></div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" data-id="time">
                    <form id="time-report" style="float: left; margin-right: 10px;">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-time" data-end="end-time" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                        <?=Form::hidden('start-time', Arr::get($_GET, 'start'), array('id'=>'start-time'))?>
                        <?=Form::hidden('end-time', Arr::get($_GET, 'end'), array('id'=>'end-time'))?>
                    </form>
                    <div class="report-block history-container" style="padding-top: 0px;">
                        <div class="btn-group" role="group">
                            <button type="button" data-attr="d" data-format="YYYY-MM-DD" class="btn btn-default">Daily</button>
                            <button type="button" data-attr="w" data-format="YYYY-WW" class="btn btn-default">Weekly</button>
                            <button type="button" data-attr="m" data-format="YYYY-MM" class="active btn btn-default">Monthly</button>
                        </div>
                        <div class="chart-container full-width" id="history-block"></div>
                    </div>
                </div>
                <div class="tab-pane" data-id="fsa-fsam">
                    <form id="fsa-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-fsa" data-end="end-fsa" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                        <?=Form::hidden('start-fsa', Arr::get($_GET, 'start'), array('id'=>'start-fsa'))?>
                        <?=Form::hidden('end-fsa', Arr::get($_GET, 'end'), array('id'=>'end-fsa'))?>
                    </form>
                    <div class="report-block">
                        <div class="chart_with_list">
                            <div class="chart-container full-width" id="fsa-statuses"></div>
                        </div>
                    </div>
                    <div class="report-block fsam-statuses" style="display:none;">
                        <div class="chart_with_list">
                            <div class="chart-container full-width" id="fsam-statuses"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


<?php endif;?>
