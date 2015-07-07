<div class="report-block filter-info-container">

    <label  class="filter_value">Filters:</label>
    <div class="text-info-filters">
        <div>
            <label class="date-range-label">Date range: </label>
            <span class="date-range-container">
                <div class="daterange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
                    <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
                    <span></span> <b class="caret"></b>
                </div>
            </span>
        </div>
        <div>
            <label class="date-range-label">Company: </label>
            <span class="date-range-container">
                <select name="company" class="form-control" id="company-selector">
                    <option value="">All</option>
                    <option value="4">Dummy VK Construction - Not Active</option>
                    <option value="5">Dummy John Contracting - Not Active</option>
                    <option value="8">Tester - Wawan Team</option>
                    <option value="9">Tester - Type A - Franky team</option>
                    <option value="11">Tester - SA - Ethan Kellaway</option>
                    <option value="12">Exel - South QLD Team - Steve W, Tim B, Tristan L</option>
                    <option value="13">Tester - QLD - Jeff Cowl</option>
                    <option value="15">Tester - QLD - Ben Corfield</option>
                    <option value="16">Exel - North QLD - Ryen &amp; Piers</option>
                    <option value="17">Tester - SA - Daniel Greene</option>
                    <option value="18">Tester - SA - Courtney Caldow</option>
                    <option value="19">Exel - SA Civil crew (Brett)</option>
                    <option value="20">Icon Fibre - QLD - Type A Crews</option>
                    <option value="21">Icon Fibre - QLD - Type C Crews</option>
                    <option value="22">Tester - QLD - Craig W</option>
                    <option value="23">Cynergy - QLD - Civil</option>
                    <option value="24">AAA - temp acc.</option>
                    <option value="25">Paltech - QLD</option>
                </select>
            </span>
        </div>
    </div>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#filterModal">
        <span class="glyphicon glyphicon-ok"></span>
        Apply filter
    </button>
</form>
</div>
<div class="report-block">
    <div class="chart_with_list">
        <div class="chart-container" id="worker-report"></div>
        <div class="list-container" id="worker-list">
            <div class="list-label">Workers</div>
            <ul>
            </ul>
        </div>
    </div>
</div>

<div class="report-block">
    <div class="chart_with_list">
        <div class="chart-container" id="company-history-report"></div>
        <div class="list-container" id="company-list">
            <div class="list-label">Companies</div>
            <ul>
            </ul>
        </div>
    </div>
</div>

<div class="report-block">
    <div class="chart_with_list">
        <div class="chart-container" id="pie-company-report"></div>
        <div class="list-container" id="pie-company-list">
            <div class="list-label">Companies</div>
            <ul>
            </ul>
        </div>
    </div>
</div>