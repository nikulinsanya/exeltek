<div class="btn-group">
    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Add new <span class="caret"></span></button>
    <ul class="dropdown-menu">
        <?php foreach ($forms as $key => $name):?>
            <li><a href="<?=URL::base()?>form/fill?form=<?=$key?>"><?=$name?></a></li>
        <?php endforeach;?>
    </ul>
</div>
<ul class="nav nav-tabs status-filter topsideup view-tab-header">
    <li role="presentation" data-id="forms" class="active"><a class="refreshClick" href="#forms">Pending reports</a></li>
    <li role="presentation" data-id="files"><a class="refreshClick" href="#files">Finished reports</a></li>
</ul>
<div class="panel panel-default">
    <div data-id="forms" class="panel-body active">
        <h4>Pending forms:</h4>
        <ul class="list-unstyled">
            <?php foreach($list as $form):?>
                <li><a href="<?=URL::base()?>form/fill?id=<?=$form['_id']?>"><?=date('d-m-Y', $form['created'])?>. <?=Arr::get($form, 'name', 'Unknown Form')?> by <?=User::get($form['user_id'], 'login')?>. Last update: <?=date('d-m-Y H:i', $form['last_update'])?> (Rev. <?=$form['revision']?>)</a></li>
            <?php endforeach;?>
        </ul>
    </div>
    <div data-id="files" class="panel-body hidden">
        <?php $fl = false; foreach ($files as $attachment):?>
        <div class="col-xs-12 col-md-6 col-lg-4 <?=($fl = !$fl) ? 'bg-warning' : 'yellow'?>" style="overflow-x: hidden;">
            <table><tr>
                    <?php $is_image = preg_match('/^image\/.*$/i', $attachment['mime']);?>
                    <td><div class="td-image-center">
                            <?php if ($is_image):?>
                                <img  src="<?=URL::base()?>download/thumb/<?=$attachment['id']?>" alt="Thumbnail" />
                            <?php else:?>
                                <img  src="http://stdicon.com/<?=$attachment['mime']?>?size=96&default=http://stdicon.com/text" />
                            <?php endif;?>
                        </div></td><td>
                        <a target="_blank" data-id="<?=$attachment['id']?>" class="<?=$is_image && $attachment['folder'] != 'Signatures' ? 'image-attachments' : ''?>" href="<?=URL::base()?>download/attachment/<?=$attachment['id']?>">
                            <?=HTML::chars($attachment['folder'])?><br/><?=HTML::chars($attachment['filename'])?>
                        </a><br/>
                        Uploaded <?=date('d-m-Y H:i', $attachment['uploaded'])?> by <?=User::get($attachment['user_id'], 'login')?>
                        <?php if ($attachment['location']):?>
                            <a target="_blank" href="https://www.google.com/maps/@<?=$attachment['location']?>,19z">(Location)</a>
                        <?php endif;?>
                    </td>
                </tr></table>
        </div>
        <?php endforeach;?>
    </div>
</div>