<form class="form-horizontal view-form" action="" method="post" enctype="multipart/form-data">
    <div class="view-header row">
        <div class="col-xs-1">
            <label class="control-label">Ticket ID: </label>
        </div>
        <div class="col-xs-3">
            <?=$job['_id']?>
        </div>
        <div class="clearfix">&nbsp;</div>

        <div class="col-xs-1">
            <label class="control-label">Region: </label>
        </div>
        <div class="col-xs-3">
            <?=$job['region']?>
        </div>
    </div>

    <div class="top-buttons">
        <button type="button" class="btn btn-danger back-button">Back</button>
        <button type="submit" class="btn btn-success">Save</button>
        <?php if (Group::current('allow_assign') && Arr::get($job, 'assigned')):?>
            <button title="Unassign all companies" type="submit" class="btn btn-primary <?=array_column($tabs, 'submissions') ? 'disabled' : ''?>">Finish job</button>
        <?php endif;?>
    </div>

<!--    tabs-->
    <ul class="nav nav-tabs status-filter topsideup">

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
        <?php if ($job['discrepancies']):?>
            <li role="presentation" class="bg-danger" data-id="discrepancies"><a href="javascript:;">Discrepancies</a></li>
        <?php endif;?>
    </ul>
    
    <div class="panel panel-default">
        <?php $fl = true; foreach ($tabs as $id => $tab) if (isset($tab['columns'])):?>
    
        <div data-id="<?=$id?>" class="panel-body <?=!$fl ? 'hidden' : 'active'?>">
            <table class="col-container">
            <?php  $index = 0; foreach ($tab['columns'] as $id => $name): $value = isset($values['data' . $id]) ? $values['data' . $id]: Arr::path($job, 'data.' . $id, '');?>
                <?php if (0 == $index++ % 2) :?>
                    <tr>
                <?php endif; ?>
                <td  class="<?=Arr::get($submissions, 'data.' . $id) ? 'bg-danger' : ''?>">

                        <label  class="left-label"><?=HTML::chars($name)?><?=isset($values['data' . $id]) ? '*' : ''?>: </label>
                        <div class="">
                            <?php $type = Columns::get_type($id); if (Columns::allowed($id) == Columns::COLUMN_WRITE):?>
                                <?php echo Columns::input('data', $id, $type, $value); if (isset($submissions['data.' . $id])):?>
                                    <ul class="list-unstyled radio-container">
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

                </td>
                <?php if (0 == $index % 2) :?>
                    </tr>
                <?php endif; ?>
            <?php endforeach;?>
            </table>

        </div>
        <?php $fl = false; endif;?>
        <?php if ($submissions):?>
        <div data-id="submissions" class="panel-body hidden">
            <table class="col-container">
                <?php $time = false; $user_id = false; foreach ($submissions['list'] as $submission):?>
                <?php if ($time != $submission['time'] || $user_id != $submission['user_id']):
                    $user = User::get($submission['user_id']);
                    $user_id = $submission['user_id'];
                    $time = $submission['time'];
                    ?>
                    <tr class="text-center">
                        <th>
                        <label>
                            <?=date('d-m-Y H:i', $submission['time']) . ' - ' .
                                Arr::get($user, 'login', 'Unknown user') . '@' . (isset($submission['version']) ? 'Mobile app (' . ($submission['version'] ? : 'Unknown') . ')' : 'Web-app') .
                                ' / ' . Arr::get($companies, Arr::get($user, 'company_id'), 'Unknown company')?>

                        </label>
                        </th>
                    </tr>
                <?php endif;?>


                    <tr>
                        <td>
                            <?php if ($submission['active'] == 1):?>
                                <span id="submission-<?=$submission['id']?>" class="pending-<?=$submission['key']?> text-info glyphicon glyphicon-edit"></span>
                            <?php elseif ($submission['active'] == -1):?>
                                <span class="text-success glyphicon glyphicon-ok"></span>
                            <?php else:?>
                                <span class="text-danger glyphicon glyphicon-remove"></span>
                            <?php endif;?>
                            <strong><?=$submission['name']?>: </strong>
                            <?=Columns::output($submission['value'], $submission['type'])?>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </table>
            <div class="clearfix">&nbsp;</div>
        </div>
        <?php endif;?>
        <?php if (Group::current('allow_assign')):?>
        <div data-id="assigned" class="panel-body hidden">
            <table class="col-container">
                    <?php $history = array_flip(Arr::get($job, 'ex', array()));$index = 0;   foreach ($job_types as $id => $type): unset($history[Arr::path($job, 'assigned.' . $id)]);?>
                    <?php if (0 == $index++ % 2) :?>
                        <tr>
                    <?php endif; ?>
                        <td>

                            <label  class="left-label"><?=HTML::chars($type)?></label>
                            <div class="">
                            <?=Form::select('assigned[' . $id . ']', array('' => 'None') + $companies, Arr::path($job, 'assigned.' . $id), array('class' => 'form-control'))?>
                            </div>
                        </td>
                        <?php if (0 == $index % 2) :?>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach;?>
                </table>


                <!--@todo stylize this block -->
                <?php if ($history):?>
                <h4>Previously assigned companies:</h4>
                <ul class="list-unstyled">
                    <?php foreach ($history as $key => $value):?>
                        <li><?=Arr::get($companies, $key, 'Unknown')?></li>
                    <?php endforeach;?>
                </ul>


            <?php endif;?>
        </div>
        <div data-id="settings" class="panel-body hidden">
            <table class="col-container">
                <?php foreach (Columns::$settings as $key => $value):?>
                    <tr>
                        <td>
                            <label class="checkbox-label">
                                <input type="checkbox" name="<?=$key?>" <?=Arr::get($job, $key) ? 'checked' : ''?> <?=in_array($key, Columns::$settings_read_only, true) ? 'disabled' : ''?> value="1" />
                                <?=HTML::chars($value)?>
                            </label>
                        </td>
                    </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php endif;?>
        <div data-id="attachments" class="panel-body hidden">
            <table class="col-container files-container">
                <?php foreach ($job['attachments'] as $attachment):?>
                    <tr>
                        <td>
                            <?php if (Group::current('allow_assign')):?>
                                <a href="<?=URL::base()?>search/view/<?=$job['_id']?>?delete=<?=$attachment['id']?>"
                                   confirm="Do you really want to delete this attachment? This action can't be undone!!!"
                                   class="text-danger glyphicon glyphicon-remove remove-link"></a>
                            <?php endif;?>
                            <a target="_blank" href="<?=URL::base()?>download/attachment/<?=$attachment['id']?>">
                                <img src="http://stdicon.com/<?=$attachment['mime']?>?size=32&default=http://stdicon.com/text" />
                                <?=HTML::chars($attachment['folder'] . ' / ' . $attachment['fda_id'] . ' / ' . $attachment['address'] . ' / ' . $attachment['filename'])?>
                            </a>
                            - Uploaded <?=date('d-m-Y H:i', $attachment['uploaded'])?> by <?=User::get($attachment['user_id'], 'login')?>
                            <?php if ($attachment['location']):?>
                                <a target="_blank" href="https://www.google.com/maps/@<?=$attachment['location']?>,19z">(Location)</a>
                            <?php endif;?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </table>


            <div class="upload-buttons">
                <button type="button" class="btn btn-primary upload" data-target="<?=URL::base()?>search/" data-id="<?=$job['_id']?>">Upload</button>
                <?php if (Group::current('allow_reports')):?>
                    <a href="<?=URL::base()?>imex/reports/uploads/<?=$job['_id']?>" class="btn btn-info">View attachment log</a>
                <?php endif;?>
            </div>

            <?=View::factory('Jobs/UploadFile')?>
        </div>
        <div data-id="discrepancies" class="panel-body hidden">
            <table class="table">
                <tr class="text-center">
                    <th class="col-xs-1">Date</th>
                    <th class="col-xs-1">User</th>
                    <th class="col-xs-1">File name</th>
                    <th>Column name:</th>
                    <th>Old value:</th>
                    <th>New value:</th>
                    <th>Current value:</th>
                </tr>
                <?php foreach ($job['discrepancies'] as $ticket): $cnt = count($ticket['data']);
                    $fl = true;
                    foreach($ticket['data'] as $key => $value): ?>
                        <tr class="lgreen text-center">
                            <?php if ($fl):?>
                                <td rowspan="<?=$cnt?>"><?=date('d-m-Y H:i', $ticket['update_time'])?></td>
                                <td rowspan="<?=$cnt?>"><?=User::get(Arr::get($ticket, 'user_id'), 'login') ? : 'Unknown'?></td>
                                <td rowspan="<?=$cnt?>"><a href="<?=URL::base() . 'imex/discrepancies?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
                            <?php endif;?>
                            <td><?=HTML::chars(Columns::get_name($key));?></td>
                            <td><?=Columns::output($value['old_value'], Columns::get_type($key))?></td>
                            <td><?=Columns::output($value['new_value'], Columns::get_type($key))?></td>
                            <td><?=Columns::output(Arr::path($job, array('data', $key)), Columns::get_type($key))?></td>
                        </tr>
                        <?php $fl = false; endforeach;?>
                <?php endforeach;?>
            </table>
        </div>
    </div>
    <!--    tabs-->
    <ul class="nav nav-tabs status-filter upsidedown">
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
        <?php if ($job['discrepancies']):?>
            <li role="presentation" class="bg-danger" data-id="discrepancies"><a href="javascript:;">Discrepancies</a></li>
        <?php endif;?>
    </ul>
</form>
