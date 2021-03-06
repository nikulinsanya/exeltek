<div>
    <div class="filter-info-container">
        <label  class="filter_value">Filters:</label>
        <div class="text-info-filters">
            <div>
            </div>
        </div>

        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterModal">
            <span class="glyphicon glyphicon-filter"></span>
            Modify filters
        </button>
        <button type="button" class="btn btn-warning hidden clear-filters"><span class="glyphicon glyphicon-remove"></span> Clear</button>
        <button type="button" class="btn btn-success hidden" id="export-lifd">Export to Excel</button>
        <label class="filter_value no-filters">None</label>
    </div>

    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="width:800px;">
            <div class="modal-content">
                <form id="lifd-report-form" class="" action="<?=URL::base()?>dashboard/lifd/export" method="post">
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
                                <?=Form::select('company[]', $companies, isset($_GET['company']) ? explode(',',$_GET['company']) : [], array('class' => 'multiselect form-control company-filter', 'multiple'=>'multiple'))?>
                            </div>
                            <div class="clearfix">&nbsp;</div>
                        <?php endif;?>

                        <div class="col-xs-2">
                            <label class="control-label">FSA: </label>
                        </div>
                        <div class="col-xs-3">
                            <?=Form::select('fsa[]', $fsa, NULL, array('class' => 'fsa-filter multiselect', 'multiple' => 'multiple'))?>
                        </div>
                        <div class="clearfix">&nbsp;</div>

                        <div class="col-xs-2">
                            <label class="control-label">FSAM: </label>
                        </div>
                        <div class="col-xs-3">
                            <?=Form::select('fsam[]', $fsam, NULL, array('class' => 'fsam-filter multiselect', 'multiple' => 'multiple'))?>
                        </div>
                        <div class="clearfix">&nbsp;</div>
                    </div>

                    <div class="modal-footer">
                        <a href="javascript:;" class="btn btn-warning clear-filters"><span class="glyphicon glyphicon-remove"></span> Clear</a>
                        <button type="submit" class="btn btn-success" id="hideModalFilters">Apply filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="lifd-report">

</div>