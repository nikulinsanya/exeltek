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
    <div class="col-xs-12">
        <label>Form type:</label>
        <select id="form-type" class="form-control">
            <option value="">Please, select form type</option>
            <?php foreach (Form::$form_types as $type => $name):?>
                <option value="<?=$type?>"><?=$name?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div class="col-xs-12">
        <label>Form name:</label>
        <input id="form-name" type="text" class="form-control" placeholder="Please, enter form name here" />
    </div>
    <div class="container" id="form-builder-container"></div>
</div>

<link href="<?= URL::base() ?>css/forms/form.css" rel="stylesheet">
<script src="<?= URL::base() ?>js/lib/signature_pad.min.js"></script>

<script src="<?= URL::base() ?>js/forms/form.js"></script>
