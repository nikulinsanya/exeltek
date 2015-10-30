<input type="button" class="btn btn-success" value="Generate report" id="generate-report">
<?php if(sizeof($reports) > 0):?>
<h2>Table list</h2>
<ul id="table-list">
    <?php foreach ($reports as $id => $name):?>
    <li class="edit-table-item" href="#" data-id="<?=$id?>"><?=$name?></li>
    <?php endforeach;?>
</ul>
<?php endif;?>


<div class="modal fade"  id="configTable" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add report</h4>
            </div>
            <div class="modal-body" id="add-table-container">
                    <div class="row">
                    <div class="col-md-12">
                        <span class="form-label">Table name:</span>
                        <input type="text" id="table-name" placeholder="Table name"/>
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-12">
                        <span class="form-label">New cell:</span>
                        <input type="text" id="table-cell" placeholder="Header name"/>
                    </div>
                    </div>
                <div class="row">
                    <div class="col-md-12">
                        <input type="button" class="btn btn-info" value="Add cell" id="add-cell">
                    </div>
                </div>
                <table class="table table-responsive table-bordered" id="table-header">
                </table>
            </div>

            <div class="modal-footer" class="tableRowButtons">
                <input type="hidden" id="table-id" />
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success" id="save-form">Save form</button>
            </div>
        </div>
    </div>
</div>

<link href="<?= URL::base() ?>css/forms/formbuilder.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/forms/security.js"></script>