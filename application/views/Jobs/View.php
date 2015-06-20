<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
    
    <div class="form-group">
        <label class="col-xs-2 control-label">Ticket ID:</label>
        <div class="col-xs-10">
            <p class="form-control-static"><?=$job['_id']?></p>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-xs-2 control-label">Region:</label>
        <div class="col-xs-10">
            <p class="form-control-static"><?=$job['region']?></p>
        </div>
    </div>
    
    <ul class="nav nav-tabs">
        <li role="presentation" class="pull-right"><button type="button" class="btn btn-danger back-button">Back</button></li>
        <li role="presentation" class="pull-right"><button type="submit" class="btn btn-success">Save</button></li>
        <?php if (Group::current('allow_assign') && Arr::get($job, 'assigned')):?>
        <li role="presentation" id="finish-job" class="pull-right"><button title="Unassign all companies" type="submit" class="btn btn-primary <?=array_column($tabs, 'submissions') ? 'disabled' : ''?>">Finish job</button></li>
        <?php endif;?>

    <?php $fl = true; foreach ($tabs as $id => $tab) if (isset($tab['columns'])):?>
        <li role="presentation" data-id="<?=$id?>" class="<?=$fl ? 'active':''?>">
            <a href="javascript:;"><?=HTML::chars($tab['name'])?>
            <?php if (Arr::get($tab, 'submissions')):?>
                <span class="badge"><?=count($tab['submissions'])?></span>
            <?php endif;?>
            </a>
        </li>
    <?php $fl = false; endif;?>
        <?php if (Group::current('allow_assign')):?>
        <li role="presentation" data-id="assigned"><a href="javascript:;">Assiged companies</a></li>
        <li role="presentation" data-id="settings"><a href="javascript:;">Settings</a></li>
        <?php endif;?>
        <?php if ($submissions):?>
        <li role="presentation" data-id="submissions"><a href="javascript:;">Submissions</a></li>
        <?php endif; ?>
        <li role="presentation" data-id="attachments"><a href="javascript:;">Attachments</a></li>
    </ul>
    
    <div class="panel panel-default">
        <?php $fl = true; foreach ($tabs as $id => $tab) if (isset($tab['columns'])):?>
    
        <div data-id="<?=$id?>" class="panel-body <?=!$fl ? 'hidden' : 'active'?>">
            <?php foreach ($tab['columns'] as $id => $name): $value = isset($values['data' . $id]) ? $values['data' . $id]: Arr::path($job, 'data.' . $id, '');?>
    
            <div class="form-group <?=Arr::get($submissions, 'data.' . $id) ? 'bg-danger' : ''?>">
                <label class="col-xs-4 control-label"><?=HTML::chars($name)?><?=isset($values['data' . $id]) ? '*' : ''?>: </label>
                <div class="col-xs-8">
                <?php $type = Columns::get_type($id); if (Columns::allowed($id) == Columns::COLUMN_WRITE):?>
                <?php echo Columns::input('data', $id, $type, $value); if (isset($submissions['data.' . $id])):?>
                    <ul class="list-unstyled">
                    <li><label><input type="radio" class="submission-select" data-id="data-<?=$id?>" name="submission-data[<?=$id?>]" value="0" />Keep current</label></li>
                <?php $multi = substr($type, 0, 4) == 'enum' && Enums::is_multi(substr($type, 5));
                    if (Group::current('allow_assign')) foreach ($submissions['data.' . $id] as $submission): $user = User::get($submission['user_id']);?>
                    <li><label>
                        <input type="radio" class="submission-select" data-id="data-<?=$id?>" name="submission-data[<?=$id?>]" value="<?=$submission['id']?>" />
                        <?=$type == 'date' ? date('Y-m-d H:i:s', $submission['value']) :HTML::chars($submission['value']) ? : '<span class="glyphicon glyphicon-remove"></span>'?>
                        (<?=date('d-m-Y H:i', $submission['time']) . ' - ' . Arr::get($user, 'login', 'Unknown user') . ' / ' . Arr::get($companies, Arr::get($user, 'company_id'), 'Unknown company')?>)
                    </label></li>
                <?php endforeach;?>
                    </ul>
                <?php endif; else:?>
                    <p class="form-control-static">
                        <?=Columns::output($value, $type)?>
                    </p>
                <?php endif;?>
                </div>
            </div>
            <?php endforeach;?>
        </div>
        <?php $fl = false; endif;?>
        <?php if ($submissions):?>
        <div data-id="submissions" class="panel-body hidden">
            <?php $time = false; $user_id = false; foreach ($submissions['list'] as $submission):?>
            <?php if ($time != $submission['time'] || $user_id != $submission['user_id']): 
                if ($time) echo '</ul></div>';
                $user = User::get($submission['user_id']);
                $user_id = $submission['user_id'];
                $time = $submission['time'];
            ?>
            <div>
                <label>
                    <?=date('d-m-Y H:i', $submission['time']) . ' - ' . Arr::get($user, 'login', 'Unknown user') . ' / ' . Arr::get($companies, Arr::get($user, 'company_id'), 'Unknown company')?>
                </label>
                <ul>
            <?php endif;?>
                    <li>
                        <?php if ($submission['active'] == 1):?>
                        <span id="submission-<?=$submission['id']?>" class="pending-<?=$submission['key']?> text-info glyphicon glyphicon-edit"></span>
                        <?php elseif ($submission['active'] == -1):?>
                        <span class="text-success glyphicon glyphicon-ok"></span>
                        <?php else:?>
                        <span class="text-danger glyphicon glyphicon-remove"></span>
                        <?php endif;?>
                        <strong><?=$submission['name']?>: </strong>
                        <?=Columns::output($submission['value'], $submission['type'])?>
                    </li>
            <?php endforeach;?>
                </ul>
            </div>
            <div class="clearfix">&nbsp;</div>
        </div>
        <?php endif;?>
        <?php if (Group::current('allow_assign')):?>
        <div data-id="assigned" class="panel-body hidden">
            <?php $history = array_flip(Arr::get($job, 'ex', array())); foreach ($job_types as $id => $type): unset($history[Arr::path($job, 'assigned.' . $id)]);?>
            <div class="form-group">
                <label class="col-xs-4 control-label"><?=HTML::chars($type)?></label>
                <div class="col-xs-8">
                <?=Form::select('assigned[' . $id . ']', array('' => 'None') + $companies, Arr::path($job, 'assigned.' . $id), array('class' => 'form-control'))?>
                </div>
            </div>
            <?php endforeach; if ($history):?>
            <h4>Previously assigned companies:</h4>
            <ul class="list-unstyled">
            <?php foreach ($history as $key => $value):?>
                <li><?=Arr::get($companies, $key, 'Unknown')?></li>
            <?php endforeach;?>
            </ul>
            <?php endif;?>
        </div>
        <div data-id="settings" class="panel-body hidden">
            <div class="col-xs-12">
                <?php foreach (Columns::$settings as $key => $value):?>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="<?=$key?>" <?=Arr::get($job, $key) ? 'checked' : ''?> value="1" />
                        <?=HTML::chars($value)?>
                    </label>
                </div>
                <?php endforeach;?>
            </div>
        </div>
        <?php endif;?>
        <div data-id="attachments" class="panel-body hidden">
            <ul class="list-unstyled">
                <?php foreach ($job['attachments'] as $attachment):?>
                <li>
                    <?php if (Group::current('allow_assign')):?>
                    <a href="<?=URL::base()?>search/view/<?=$job['_id']?>?delete=<?=$attachment['id']?>"
                        confirm="Do you really want to delete this attachment? This action can't be undone!!!"
                        class="text-danger glyphicon glyphicon-remove remove-link"></a>
                    <?php endif;?>
                    <a href="<?=URL::base()?>download/attachment/<?=$attachment['id']?>">
                    <img src="http://stdicon.com/<?=$attachment['mime']?>?size=32&default=http://stdicon.com/text" />
                    <?=HTML::chars($attachment['folder'] . ' / ' . $attachment['fda_id'] . ' / ' . $attachment['address'] . ' / ' . $attachment['filename'])?>
                    </a>
                    - Uploaded <?=date('d-m-Y H:i', $attachment['uploaded'])?> by <?=User::get($attachment['user_id'], 'login')?>
                    <?php if ($attachment['location']):?>
                        <a target="_blank" href="https://www.google.com/maps/@<?=$attachment['location']?>,19z">(Location)</a>
                    <?php endif;?>
                </li>
                <?php endforeach;?>
                <li><button type="button" class="btn btn-primary upload" data-target="<?=URL::base()?>search/" data-id="<?=$job['_id']?>">Upload</button></li>
            </ul>
            <?php if (Group::current('allow_reports')):?>
            <a href="<?=URL::base()?>imex/reports/uploads/<?=$job['_id']?>" class="btn btn-info">View attachment log</a>
            <?php endif;?>
            <?=View::factory('Jobs/UploadFile')?>
        </div>
    </div>
</form>