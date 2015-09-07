
<div class="page-header" style="margin: 60px 0px 0px;">
    <h1 style="margin-left: 15px;">Form builder</h1>
</div>

<div class="container">
    <!--<div class='fb-main'></div>-->
    <!--templates-->
    <div class="form-row" data-template-row style="display: none;">

        <button class="add-value tmp-gen">+</button>
    </div>

    <span data-template-value style="display: none;">
        <div class="form-block">
            <div class="value"><span class="tmp-label"> Label</span></div>
            <button class="remove-field tmp-gen"> - </button>
        </div>
    </span>
    <!---->

    <div class="form-container"></div>


    <div class="form-configuration-container">
        <div>
            <button class="add-row btn btn-success">Add row</button>
            <button class="save-form btn btn-info">Save form</button>
            <div class="fields-config">
                <div class="config-row">
                    <div class="config-label"> Type </div>
                    <div class="config-val">
                        <select class="field-type-select">
                            <option value="label" >Label</option>
                            <option value="text">Text input</option>
                            <option value="date">Date input</option>
                            <option value="canvas">Signature</option>
                            <option value="select">Select</option>
                            <option value="ticket">Ticket field</option>
                        </select>
                    </div>
                </div>
                <div class="config-row ticket-type-select">
                    <div class="config-label"> Ticket field </div>
                    <div class="config-val ticket-input-select">
                        <select></select>
                    </div>
                </div>
                <div class="config-row  placeholder-container">
                    <div class="config-label"> Placeholder </div>
                    <div class="config-val"><input type="text" class="field-placeholder"/></div>
                </div>
                <div class="config-row config-value-container  value-container">
                    <div class="config-label"> Value </div>
                    <div class="config-val"><input type="text"/></div>
                </div>

                <div class="config-row config-select-container">
                    <div class="config-label"> Available options </div>
                    <div class="config-val">
                        <select class="available-options-select"></select>
                        <br>
                        <input type="text" class="select-option">
                        <button class="add-option">Add option</button>
                        <br>
                        <label><input type="checkbox" class="multiselect-option"> Multiple choises</label>
                        <br>
                        <button class="apply-option">Apply</button>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="form-btns-container">
        <button class="send-form">Send form</button>
        <button class="reset-form">Reset form</button>
    </div>



</div>

<link href="<?=URL::base()?>css/forms/form.css" rel="stylesheet">
<script src="<?=URL::base()?>js/lib/signature_pad.min.js"></script>

<script src="<?=URL::base()?>js/forms/form.js"></script>