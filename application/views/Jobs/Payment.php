<form method="post" id="payment-form">
    <div id="payment-pre" class="col-xs-12">
        <div class="col-xs-12">
            <label>Company:</label>
                <select id="payment-company" name="company" class="selectize">
                    <option value="">Please, select contractor...</option>
                    <?php foreach ($companies as $id => $name):?>
                        <option value="<?=$id?>"><?=$name?></option>
                    <?php endforeach;?>
                </select>

        </div>
        <?php foreach ($jobs as $job => $info):?>
        <div class="col-sm-3 col-md-3 glyphicon glyphicon-minus text-muted payment-info"
            <?php
                foreach ($info['c'] as $company => $value) {
                    echo ' data-company-' . $company . '="' . $value . '"';
                }
                foreach ($info['p'] as $company => $value) echo ' data-paid-' . $company . '="' . $value . '"';
            ?>
            >
           <span class="payment-job-id"><?=$job?></span> - <span class="payment-job-value">0</span>
            <input type="hidden" name="job[<?=$job?>]" value="" />
        </div>
        <?php endforeach;?>
        <div class="col-xs-12">
            <label>
                Pending amount: <span id="payment-avail"></span></label>
        </div>
        <div class="col-xs-12">
            <button id="payment-continue" type="button" class="btn btn-success hidden">Continue</button>
        </div>
    </div>
    <div class="col-xs-12 hidden" id="payment-details">
        <div class="col-xs-12">
            <label>
                Payment amount:</label>
            <input id="payment-amount" type="text" class="form-control" value="0" />

        </div>

        <div class="col-xs-12">
            <button class="btn btn-success">Create payment</button>
            <button class="btn btn-danger" id="payment-cancel">Cancel</button>
        </div>
    </div>
</form>