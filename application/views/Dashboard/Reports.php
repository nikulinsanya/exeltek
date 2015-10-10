    <script type="application/javascript">
        var REPORTDATA = {
            isAdmin: <?=Group::current('show_all_jobs') ?>
        };

    </script>

    <?php if(Group::current('show_all_jobs')):?>
        <nav class="black-navbar navbar navbar-inverse sidebar" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="bs-sidebar-navbar-collapse-1">
                    <ul class="nav navbar-nav"  id="report-container">
                        <li data-id="main" class="active"><a class="switcher selected_switcher" href="#main">Overview<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-home"></span></a></li>
                        <li data-id="company"><a class="switcher" href="#company">Company progress<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-signal"></span></a></li>
                        <li data-id="time"><a class="switcher" href="#time">Time progress<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-time"></span></a></li>
                        <li data-id="stacked"><a class="switcher" href="#stacked">Stacked<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-stats"></span></a></li>
                        <li data-id="fsa-fsam"><a class="switcher" href="#fsa-fsam">Fsa/Fsam<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-tasks"></span></a></li>
                        <li data-id="built-type-mix"><a class="switcher" href="#built-type-mix">Built type mix<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-th-large"></span></a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="main tab-content">
            <div class="tab-pane" data-id="main">
                <form id="overview-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-overview" data-end="end-overview" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                    <?=Form::hidden('start-overview', Arr::get($_GET, 'start'), array('id'=>'start-overview'))?>
                    <?=Form::hidden('end-overview', Arr::get($_GET, 'end'), array('id'=>'end-overview'))?>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
                        <span class="glyphicon glyphicon-filter"></span>
                        Modify filters
                    </button>
                </form>
                <label  class="filter_value" style="float: left;">Filters:</label>
                <div class="text-info-filters">
                    <div>
                        <span class="filter-item"> <label class="filter_value">Empty</label></span>
                    </div>
                </div>
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
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
                        <span class="glyphicon glyphicon-filter"></span>
                        Modify filters
                    </button>
                </form>
                <label  class="filter_value" style="float: left;">Filters:</label>
                <div class="text-info-filters">
                    <div>
                        <span class="filter-item"> <label class="filter_value">Empty</label></span>
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
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
                        <span class="glyphicon glyphicon-filter"></span>
                        Modify filters
                    </button>
                </form>
                <label  class="filter_value" style="float: left;">Filters:</label>
                <div class="text-info-filters">
                    <div>
                        <span class="filter-item"> <label class="filter_value">Empty</label></span>
                    </div>
                </div>
                <div class="report-block history-container" style="padding-top: 0px;">
                    <div class="btn-group" role="group">
                        <button type="button" data-attr="d" data-format="YYYY-MM-DD" class="active btn btn-default">Daily</button>
                        <button type="button" data-attr="w" data-format="YYYY-WW" class="btn btn-default">Weekly</button>
                        <button type="button" data-attr="m" data-format="YYYY-MM" class=" btn btn-default">Monthly</button>
                    </div>
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#startDayModal">
                        <span class="glyphicon glyphicon-cog"></span>
                    </button>
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
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
                        <span class="glyphicon glyphicon-filter"></span>
                        Modify filters
                    </button>
                </form>
                <label  class="filter_value" style="float: left;">Filters:</label>
                <div class="text-info-filters">
                    <div>
                        <span class="filter-item"> <label class="filter_value">Empty</label></span>
                    </div>
                </div>
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
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
                        <span class="glyphicon glyphicon-filter"></span>
                        Modify filters
                    </button>
                </form>
                <label  class="filter_value" style="float: left;">Filters:</label>
                <div class="text-info-filters">
                    <div>
                        <span class="filter-item"> <label class="filter_value">Empty</label></span>
                    </div>
                </div>
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
            <div class="tab-pane" data-id="built-type-mix">
                <form id="built-type-mix-report">
                            <span class="date-range-container">
                                <div class="daterange" class="pull-right" data-start="start-built-type-mix" data-end="end-built-type-mix" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                                    <span></span> <b class="caret"></b>
                                </div>
                            </span>
                    <?=Form::hidden('start-built-type-mix', Arr::get($_GET, 'start'), array('id'=>'start-built-type-mix'))?>
                    <?=Form::hidden('end-built-type-mix', Arr::get($_GET, 'end'), array('id'=>'end-built-type-mix'))?>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
                        <span class="glyphicon glyphicon-filter"></span>
                        Modify filters
                    </button>
                </form>
                <label  class="filter_value" style="float: left;">Filters:</label>
                <div class="text-info-filters">
                    <div>
                        <span class="filter-item"> <label class="filter_value">Empty</label></span>
                    </div>
                </div>
                <div class="report-block">
                    <div class="chart-container full-width" id="tickets-built-type-mix"></div>
                </div>
            </div>
        </div>


<!--       -->

<?php endif;?>
    <div class="modal fade" id="startDayModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="width:800px;">
            <div class="modal-content">
                <form id="dashboard-week-start-report-form" class="" action="" method="get">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Calendar configuration</h4>
                    </div>

                    <div class="modal-body" id="filter-form" style="min-height: 300px;">
                        <div class="col-xs-3">
                            <label class="control-label">Start day of week: </label>
                        </div>
                        <div class="col-xs-3">
                            <?=Form::select('weekStart',
                                array('1' => 'Monday','2'=>'Tuesday','3'=>'Wednesday','4'=>'Thursday','5'=>'Friday','6'=>'Saturday','7'=>'Sunday'),
                                '', array('class' => 'form-control multiselect','id'=>'weekStart'))?>

                        </div>
                        <div class="clearfix">&nbsp;</div>

                        <div class="col-xs-3">
                            <label class="control-label">Start day of month: </label>
                        </div>
                        <div class="col-xs-3">
                            <?=Form::select('monthStart',
                                array_combine(range(1,30),range(1,30)),'', array('class' => 'form-control multiselect', 'id'=>'monthStart'))?>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" data-dismiss="modal"  class="btn btn-success" id="saveDateConfig">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="width:800px;">
            <div class="modal-content">
                <form id="dashboard-report-form" class="" action="" method="get">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Modify filters</h4>
                    </div>

                    <div class="modal-body" id="filter-form" style="min-height: 300px;">
                        <div class="col-xs-2">
                            <label class="control-label">Region: </label>
                        </div>
                        <div class="col-xs-3">
                            <?=Form::select('region', array('' => 'All regions') + $regions, Arr::get($_GET, 'region'), array('class' => 'form-control region-filter multiselect'))?>

                        </div>
                        <div class="clearfix">&nbsp;</div>

                        <?php if (Group::current('show_all_jobs')):?>
                            <div class="col-xs-2">
                                <label class="control-label">Contractor: </label>
                            </div>
                            <div class="col-xs-3">
                                <?=Form::select('company[]', $companies, isset($_GET['company']) ? $_GET['company'] : [], array('class' => 'multiselect form-control company-filter', 'multiple'=>'multiple'))?>
                            </div>
                            <div class="clearfix">&nbsp;</div>
                        <?php endif;?>

                        <div class="col-xs-2 fsa-fsam-hidden">
                            <label class="control-label">FSA: </label>
                        </div>
                        <div class="col-xs-3  fsa-fsam-hidden">
                            <?=Form::select('fsa[]', $fsa, NULL, array('class' => 'fsa-filter multiselect', 'multiple' => 'multiple'))?>
                        </div>
                        <div class="clearfix"  fsa-fsam-hidden>&nbsp;</div>

                        <div class="col-xs-2  fsa-fsam-hidden">
                            <label class="control-label">FSAM: </label>
                        </div>
                        <div class="col-xs-3  fsa-fsam-hidden">
                            <?=Form::select('fsam[]', $fsam, NULL, array('class' => 'fsam-filter multiselect', 'multiple' => 'multiple'))?>
                        </div>
                        <div class="clearfix fsa-fsam-hidden">&nbsp;</div>

                        <div class="col-xs-2  fda-hidden">
                            <label class="control-label">FDA: </label>
                        </div>
                        <div class="col-xs-3  fda-hidden">
                            <?=Form::select('fda[]', [], NULL, array('class' => 'fda-filter multiselect', 'multiple' => 'multiple'))?>
                        </div>
                        <div class="clearfix  fda-hidden">&nbsp;</div>
                    </div>

                    <div class="modal-footer">
                        <a href="javascript:;" class="btn btn-warning clear-filters"><span class="glyphicon glyphicon-remove"></span> Clear</a>
                        <button type="submit" class="btn btn-success" id="hideModalFilters">Apply filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="ticketData" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="width:800px;">
            <div class="modal-content">
                <form id="dashboard-report-form" class="" action="" method="get">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="ticketsTitle">Total tickets for selected date</h4>
                    </div>

                    <div class="modal-body" id="filter-form" style="min-height: 300px;max-height: 500px;">
                        <table class="table small" id="ticketTable">
                            <tr class="text-center tr-header">
                                <th>Ticket ID</th>
                                <th></th>
                            </tr>
                        </table>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success"  data-dismiss="modal" id="exportTickets"><span class="glyphicon glyphicon-download"></span> Export</button>
                        <button class="btn btn-warning"  data-dismiss="modal" id="hideModalTickets">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

