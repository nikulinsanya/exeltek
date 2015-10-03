<form method="post" id="payment-form">
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
    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2 glyphicon glyphicon-minus text-muted payment-info"
        <?php
            foreach ($info['c'] as $company => $value) echo ' data-company-' . $company . '="' . $value . '"';
            foreach ($info['p'] as $company) echo ' data-paid-' . $company . '="1"';
        ?>
        >
        <?=$job?> - <span>0</span>
        <input type="hidden" name="job[<?=$job?>]" value="" />
    </div>
    <?php endforeach;?>
    <div class="col-xs-12">
        <label>
            Payment amount:</label>
            <input id="payment-amount" name="amount" type="text" class="form-control" value="0" />

    </div>
    <div class="col-xs-12">
        <button class="btn btn-success">Create payment</button>
    </div>
</form>