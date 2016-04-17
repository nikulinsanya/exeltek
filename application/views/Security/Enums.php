<table class="table" id="enumsTable">
    <tr>
        <th><button class="btn btn-success edit-enum" data-id="0">Add</button></th>
        <th>Name</th>
        <th>Multi-values</th>
    </tr>
    <?php foreach ($items as $item):?>
    <tr data-id="<?=$item['id']?>">
        <td><button class="btn btn-warning edit-enum" data-id="<?=$item['id']?>"><span title="Edit Enum" class="glyphicon glyphicon-pencil"></span></button>
            <button class="btn btn-danger remove-enum" data-id="<?=$item['id']?>"><span title="Remove Enum" class="glyphicon glyphicon-trash"></span></button></td>
        <td class="enumName"><?=$item['name']?></td>
        <td class="isMulti"><span class="glyphicon glyphicon-<?=$item['allow_multi'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
    </tr>
    <?php endforeach;?>
</table>

<div class="modal fade" id="editEnum" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document"  style="width:600px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="enum-title">Edit enum </h4>
            </div>
            <div class="modal-body text-center">
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Enum name</span>
                    </div>
                    <div class="col-md-8">
                        <input type="text" id="enumName"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Is multiselect</span>
                    </div>
                    <div class="col-md-8">
                        <input type="checkbox" id="isMulti" checked/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Add Option</span>
                    </div>
                    <div class="col-md-8">
                        <div class="in-row">
                            <input type="text" id="option-type-value">
                            <a class="btn btn-success btn-xs" id="add-option">Add option</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span class="form-label">Preview</span>
                    </div>
                    <div class="col-md-8">
                        <div class="in-row">
                            <select id="optionsPreview" style="width:150px;"></select>
                            <a class="btn btn-danger btn-xs" id="remove-option">Remove option</a>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="enumId"/>
            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success" id="updateEnum">Update</button>
            </div>
        </div>
    </div>
</div>


<script src="<?= URL::base() ?>js/security/enums.js"></script>