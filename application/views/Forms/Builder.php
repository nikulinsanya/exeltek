<ul id="forms-list" class="">
    <?php foreach ($forms as $type => $list):?>
        <li><?=Arr::get(Form::$form_types, $type, 'Unknown')?>
            <ul class="">
                <?php foreach ($list as $id => $name):?>
                    <li><a href="javascript:;" class="form-edit-link" data-id="<?=$id?>"><?=HTML::chars($name)?></a></li>
                <?php endforeach;?>
            </ul>
        </li>
    <?php endforeach;?>
    <li>
        <a href="javascript:;" class="form-edit-link">Create new form</a>
    </li>
</ul>

<div id="form-builder" class="hidden">
    <div>
        <label>Form type:</label>
        <select id="form-type" class="form-control">
            <option value="">Please, select form type</option>
            <?php foreach (Form::$form_types as $type => $name):?>
                <option value="<?=$type?>"><?=$name?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div>
        <label>Form name:</label>
        <input id="form-name" type="text" class="form-control" placeholder="Please, enter form name here" />
    </div>

    <div class="builderactions">
        <button class="btn btn-info add-table">Insert table</button>
        <button class="btn btn-success right" id="form-save">Save form</button>
    </div>
    <div class="container" id="form-builder-container"></div>
</div>


<div class="modal fade"  id="addTable" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:300px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Insert table</h4>
            </div>
            <div class="modal-body text-center" id="new-table-details">
                <div class="row">
                    <div class="col-md-12">
                        <span class="form-label">Cols:</span> <input type="number" id="cols-number" value="2">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span class="form-label">Rows:</span> <input type="number" id="rows-number" value="2">
                    </div>
                </div>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success confirm-insert-table">Insert table</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade"  id="configTable" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:300px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Table settings</h4>
            </div>
            <div class="modal-body text-center" id="table-settings">
                <div class="row">
                    <div class="col-md-12">
                        <span class="form-label">Border:</span>
                        <select id="table-border">
                            <option value="" selected="selected">Select border</option>
                            <option value="0">None</option>
                            <option value="1">1px</option>
                            <option value="2">2px</option>
                            <option value="3">3px</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <span class="form-label">Border color:</span>
                        <select id="table-color">
                            <option value="" selected="selected">Select color</option>
                            <option value="black">Black</option>
                            <option value="gray">Gray</option>
                            <option value="red">Red</option>
                            <option value="blue">Blue</option>
                            <option value="green">Green</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success confirm-table-settings">Apply</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade"  id="addField" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:600px;">
        <form id="form-insert-field">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add field</h4>
            </div>
            <div class="modal-body text-center" id="new-cell-details">
                <div class="row">
                    <div class="row" class="placeholder-type-config">
                        <div class="col-md-4" >
                            <span class="form-label">Type</span>
                        </div>
                        <div class="col-md-8" >
                            <select id="fieldType">
                                <option value="label">Label</option>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="float">Float</option>
                                <option value="date">Date</option>
                                <option value="ticket">Ticket</option>
                                <option value="options">Options</option>
                                <option value="signature">Signature</option>
                            </select>
                        </div>
                    </div>

                    <div class="row type-config placeholder-type-config">
                        <div class="col-md-4" >
                            <span class="form-label">Placeholder</span>
                        </div>
                        <div class="col-md-8" >
                            <input type="text" id="placeholder-type">
                        </div>
                    </div>
                    <div class="row type-config ticket-type-config">
                        <div class="col-md-4">
                            <span class="form-label">Ticket field</span>
                        </div>
                        <div class="col-md-8">
                            <select id="field-type">
                            </select>
                        </div>
                    </div>
                    <div class="row type-config signature-type-config">
                        <div class="col-md-4">
                            <span class="form-label">Signature</span>
                        </div>
                        <div class="col-md-8">
                            <canvas width="200" height="100" id="signature-canvas"></canvas>
                        </div>
                    </div>
                    <div class="row type-config options-type-config">
                        <div class="col-md-4">
                            <span class="form-label">Add option</span>
                        </div>
                        <div class="col-md-8">
                            <input type="text" id="option-type-value">
                            <button class="btn btn-success btn-xs" id="add-option">Add option</button>
                        </div>
                    </div>
                    <div class="row type-config options-type-config">
                        <div class="col-md-4">
                            <span class="form-label">Preview</span>
                        </div>
                        <div class="col-md-8">
                            <select id="options-preview">
                            </select><button class="btn btn-danger btn-xs" id="remove-option">Remove option</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success" id="confirm-insert-field">Save</button>
            </div>
        </div>
        </form>
    </div>
</div>

<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/lib/signature_pad.min.js"></script>

<script src="<?= URL::base() ?>js/forms/formbuilder.js"></script>
