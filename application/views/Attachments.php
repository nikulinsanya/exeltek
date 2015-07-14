<div class="col-xs-4">
    <input type="text" class="form-control" placeholder="Filter" id="attachments-filter" />
</div>
<div class="clearfix"></div>
<div class="col-xs-6 tree" data-url="<?=URL::base()?>attachments/">
<ul>
<?php foreach ($folders as $root => $fdas):?>
    <li data-folder="<?=$root?>" data-fda="" data-address=""><?=HTML::chars($root)?>
        <ul>
        <?php foreach ($fdas as $fda => $addresses):?>
            <li data-folder="<?=$root?>" data-fda="<?=$fda?>" data-address=""><?=HTML::chars($fda)?>
                <ul>
                <?php foreach ($addresses as $address):?>
                    <li data-folder="<?=$root?>" data-fda="<?=$fda?>" data-address="<?=$address?>"><?=HTML::chars($address)?></li>
                <?php endforeach;?>
                </ul>
            </li>
        <?php endforeach;?>
        </ul>
    </li>
<?php endforeach;?>
</ul>
</div>
<div class="col-xs-6">
    <ul id="files" class="list-unstyled">
    </ul>
</div>
<div class="clearfix"></div>
<button id="download-folder" type="button" class="btn btn-success">Download folder</button>