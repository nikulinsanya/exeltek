<form class="form-horizontal view-form" action="" method="post" enctype="multipart/form-data">
    <div class="view-header row">
        <div class="col-xs-1">
            <label class="control-label">Ticket ID: </label>
        </div>
        <div class="col-xs-3" id="ticket-id">
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
            <button id="finish-job" title="Unassign all companies" type="submit" class="btn btn-primary <?=array_column($tabs, 'submissions') ? 'disabled' : ''?>">Finish job</button>
        <?php endif;?>
    </div>

<!--    tabs-->
    <ul class="nav nav-tabs status-filter topsideup view-tab-header">

    <?php $fl = true; foreach ($tabs as $id => $tab) if (isset($tab['columns'])):?>
        <li role="presentation" data-id="<?=$id?>" class="<?=$fl ? 'active':''?> <?=strtolower(str_replace(' ', '_', $tab['name']));?>">
            <a class="refreshClick" href="#<?=strtolower(str_replace(' ', '_', $tab['name']));?>"><?=HTML::chars($tab['name'])?>
            <?php if (Arr::get($tab, 'submissions')):?>
                <span class="badge"><?=count($tab['submissions'])?></span>
            <?php endif;?>
            </a>
        </li>
    <?php $fl = false; endif;?>
        <?php if (Group::current('allow_assign')):?>
        <li role="presentation" data-id="assigned"><a class="refreshClick" href="#companies">Assiged companies</a></li>
        <li role="presentation" data-id="settings"><a class="refreshClick" href="#settings">Settings</a></li>
        <?php endif;?>
        <?php if ($submissions):?>
        <li role="presentation" data-id="submissions"><a class="refreshClick" href="#submissions">Submissions</a></li>
        <?php endif; ?>
        <li role="presentation" data-id="attachments"><a class="refreshClick" href="#attachments">Attachments</a></li>
        <?php if ($job['discr']):?>
            <li role="presentation" class="rose" data-id="discrepancies"><a class="refreshClick" href="#discrepancies">Discrepancies</a></li>
        <?php endif;?>
        <?php if ($forms):?>
            <li role="presentation" data-id="forms"><a class="refreshClick" href="#forms">Forms</a></li>
        <?php endif;?>
        <?php if (Group::current('time_machine')):?>
            <li role="presentation" class="yellow" data-id="time-machine"><a class="refreshClick" href="#time">Time Machine</a></li>
        <?php endif;?>
    </ul>
    
    <div class="panel panel-default">
        <?php $fl = true; foreach ($tabs as $tab_id => $tab) if (isset($tab['columns'])):?>
    
        <div data-id="<?=$tab_id?>" class="panel-body <?=!$fl ? 'hidden' : 'active'?>">
            <table class="col-container job-details-table">
            <?php  $index = 0; foreach ($tab['columns'] as $id => $name): $value = isset($values['data' . $id]) ? $values['data' . $id]: Arr::path($job, 'data.' . $id, '');?>
                <?php
                    $relation_id = ($id > 161 && $id < 182 ? $relation_id = $id+28 : ($id > 189 && $id < 210 ? $relation_id = $id-28 : false));
                    if (0 == $index++ % 2) echo '<tr>';
                    if (Arr::get($submissions, 'data.' . $id))
                        $class =  'bg-danger';
                    elseif ($id > 161 && $id < 182 && $value != (isset($values['data' . ($id + 28)]) ? $values['data' . ($id+28)]: Arr::path($job, 'data.' . ($id+28), '')))
                        $class = 'bg-warning';
                    elseif ($id > 189 && $id < 210 && $value != (isset($values['data' . ($id - 28)]) ? $values['data' . ($id-28)]: Arr::path($job, 'data.' . ($id-28), '')))
                        $class = 'bg-warning';
                    else $class = '';
                ?>

                <td  class="<?=$class?>" <?= $id > 161 && $id < 182 ? "data-has-actual-relation='$relation_id'" : ''?> <?= $id > 189 && $id < 210 ? "data-has-variation-relation='$relation_id'" : ''?><?= $id > 242 && $id < 255 ? "data-has-additional-relation='$relation_id'" : ''?>>
                        <label  class="left-label"><?=HTML::chars($name)?><?=isset($values['data' . $id]) ? '*' : ''?>: </label>
                        <div class="">
                            <?php $type = Columns::get_type($id); if (Columns::allowed($id) == Columns::COLUMN_WRITE && !Columns::get_readonly($id)):?>
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

            <?php if ($tab_id == 6 && Arr::get($job, 'payments')):?>
            <h4>Payments:</h4>
            <?php foreach ($job['payments'] as $payment): ?>
                <div class="col-xs-12">
                    <?=date('d-m-Y H:i', $payment['payment_time'])?>: <strong><?=$payment['amount']?></strong> paid to <strong><?=Arr::get($companies, $payment['company_id'], 'Unknown')?></strong> by <strong><?=User::get($payment['admin_id'], 'login')?></strong>. Total payment amount: <strong><?=$payment['total']?></strong>
                </div>
            <?php endforeach; endif;?>

        </div>
        <?php $fl = false; endif;?>


        <?php if ($submissions):?>
        <div data-id="submissions" class="panel-body hidden">
            <table class="table">
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


                    <tr class="<?=$submission['active'] == 1 ? 'yellow' : ($submission['active']? 'lgreen' : 'rose')?>">
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
                            <?php if (!$submission['active'] && Group::current('allow_assign')):?>
                            <button type="button" class="btn btn-warning approve-submission pull-right" data-id="<?=$submission['id']?>">Approve</button>
                            <?php endif;?>
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
        <div data-id="attachments" class="panel-body hidden files-container">
            <?php $fl = false; foreach ($job['attachments'] as $attachment):?>
                <div class="col-xs-12 col-md-6 col-lg-4 <?=($fl = !$fl) ? 'bg-warning' : 'yellow'?>" style="overflow-x: hidden;">
                    <table><tr>
                    <?php if (Group::current('allow_assign')):?>
                        <td>
                        <a href="<?=URL::base()?>search/view/<?=$job['_id']?>?delete=<?=$attachment['id']?>"
                           confirm="Do you really want to delete this attachment? This action can't be undone!!!"
                           class="text-danger glyphicon glyphicon-remove remove-link"></a>
                        </td>
                    <?php endif;
                        $is_image = preg_match('/^image\/.*$/i', $attachment['mime']);
                    ?>
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
            <div class="clearfix"></div>

            <div class="upload-buttons">
                <button type="button" class="btn btn-primary upload" data-target="<?=URL::base()?>search/" data-id="<?=$job['_id']?>">Upload</button>
                <?php if (Group::current('allow_reports')):?>
                    <a href="<?=URL::base()?>imex/reports/uploads/<?=$job['_id']?>" class="btn btn-info">View attachment log</a>
                <?php endif;?>
            </div>

            <?=View::factory('Jobs/UploadFile')?>
        </div>
        <?php if ($job['discr']):?>
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
                    <th>Ignore:</th>
                </tr>
                <?php $ticket = $job['discr']; $cnt = count($ticket['data']);
                    $fl = true;
                    foreach($ticket['data'] as $key => $value):
                        if ($key == 44)
                            $equal = preg_replace('/[^a-z]/i', '', strtolower($value['old_value'])) == preg_replace('/[^a-z]/i', '', strtolower(Arr::get($job['data'], $key)));
                        else
                            $equal = $value['old_value'] == Arr::get($job['data'], $key);
                    ?>
                        <tr class="<?=$equal ? 'lgreen' : 'yellow'?> text-center">
                            <?php if ($fl):?>
                                <td class="lgreen" rowspan="<?=$cnt?>"><?=date('d-m-Y H:i', $ticket['update_time'])?></td>
                                <td class="lgreen" rowspan="<?=$cnt?>"><?=User::get(Arr::get($ticket, 'user_id'), 'login') ? : 'Unknown'?></td>
                                <td class="lgreen" rowspan="<?=$cnt?>"><a href="<?=URL::base() . 'imex/discrepancies?file=' . urlencode($ticket['filename'])?>"><?=HTML::chars($ticket['filename'])?></a></td>
                            <?php endif;?>
                            <td><?=HTML::chars(Columns::get_name($key));?></td>
                            <td><?=Columns::output($value['old_value'], Columns::get_type($key))?></td>
                            <td><?=Columns::output($value['new_value'], Columns::get_type($key))?></td>
                            <td><?=Columns::output(Arr::path($job, array('data', $key)), Columns::get_type($key))?></td>
                            <td>
                                <?php if (!$equal):?>
                                    <input type="checkbox" class="ignore-discrepancy" <?=isset($value['ignore']) ? 'checked' : ''?> name="ignore-discrepancy[<?=$key?>]"/>
                                <?php else: echo '&nbsp;'; endif;?>
                            </td>
                        </tr>
                    <?php $fl = false; endforeach;?>
            </table>
            <a href="<?=URL::base()?>imex/discrepancies?ticket=<?=$job['_id']?>" class="btn btn-info">Show all</a>
        </div>
        <?php endif;?>
        <?php if ($forms):?>
            <div data-id="forms" class="panel-body hidden">
                <table class="table">
                    <?php foreach ($forms as $form):?>
                        <tr><td><a href="<?=URL::base()?>form/fill?id=<?=$form['_id']?>"><?=date('d-m-Y', $form['created'])?>. <?=Arr::get($form, 'name', 'Unknown Form')?> by <?=User::get($form['user_id'], 'login')?>. Last update: <?=isset($form['last_update']) ? date('d-m-Y H:i', $form['last_update']) : 'Never'?> (Rev. <?=$form['revision']?>)</a></td></tr>
                    <?php endforeach;?>
                </table>
            </div>
        <?php endif;?>
        <?php if (Group::current('time_machine')):?>
        <div data-id="time-machine" class="panel-body hidden">
            <button id="time-machine-start" type="button" class="btn btn-danger pull-right disabled">Rollback</button>
            <table class="table">
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>File name</th>
                    <th colspan="3">Column</th>
                </tr>
                <?php
                $actions = array(
                    '1' => 'Created',
                    '2' => 'Updated',
                    '3' => 'Removed',
                );
                $classes = array(
                    '1' => 'lgreen',
                    '2' => 'yellow',
                    '3' => 'rose',
                );
                foreach ($archive as $history):?>
                <tr class="time-machine-item <?=Arr::get($classes, $history['update_type'])?>" data-saved="<?=Arr::get($classes, $history['update_type'])?>" data-id="<?=$history['_id']?>">
                    <td nowrap="nowrap"><?=date('d-m-Y H:i', $history['update_time'])?></td>
                    <td><?=User::get(Arr::get($history, 'user_id'), 'login') ? : 'Unknown'?></td>
                    <td><?=Arr::get($actions, $history['update_type'])?></td>
                    <td><a href="<?=URL::base() . 'imex/reports?file=' . urlencode($history['filename'])?>"><?=HTML::chars($history['filename'])?></a></td>

                    <td colspan="3">
                        <?php if ($history['update_type'] == 2):?>
                        <table class="table subtable">
                            <tr>
                                <th>Name</th>
                                <th>Old value:</th>
                                <th>New value:</th>
                                <th>Current value</th>
                            </tr>
                            <?php foreach($history['data'] as $id => $value): $type = Columns::get_type($id);?>
                                <tr class="same-yellow">
                                    <td><?=HTML::chars(Columns::get_name($id))?></td>
                                    <td <?=strlen($value['old_value']) > 100 ? 'class="shorten"' : ''?>><?=Columns::output($value['old_value'], $type)?></td>
                                    <td <?=strlen($value['new_value']) > 100 ? 'class="shorten"' : ''?>><?=Columns::output($value['new_value'], $type)?></td>
                                    <td <?=strlen(Arr::get($job['data'], $id)) > 100 ? 'class="shorten"' : ''?>><?=Columns::output(Arr::get($job['data'], $id), $type)?></td>
                                </tr>
                            <?php endforeach;?>
                        </table>
                        <?php else:?>
                            N/A
                        <?php endif;?>
                    </td>
                </tr>
            <?php endforeach;?>
            </table>
        </div>
        <?php endif;?>
    </div>
    <!--    tabs-->
    <ul class="nav nav-tabs status-filter upsidedown view-tab-header">
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
        <?php if ($job['discr']):?>
            <li role="presentation" class="rose" data-id="discrepancies"><a href="javascript:;">Discrepancies</a></li>
        <?php endif;?>
        <?php if ($forms):?>
            <li role="presentation" data-id="forms"><a class="refreshClick" href="#forms">Forms</a></li>
        <?php endif;?>
        <?php if (Group::current('time_machine')):?>
            <li role="presentation" class="yellow" data-id="time-machine"><a href="javascript:;">Time Machine</a></li>
        <?php endif;?>
    </ul>
</form>


<div class="modal fade" id="editImage" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit attachment </h4>
            </div>
            <div class="modal-body text-center">
                <div id="wpaintContainer">
                    <div id="wPaint" style="position:relative; width:800px; height:500px; margin:0px auto"></div>
                </div>

            </div>
            <div class="modal-footer" class="tableRowButtons">
                <button class="btn btn-info new-window-open" style="float: left;">Open in new window</button>
                <button class="btn btn-warning" data-dismiss="modal">Close</button>
                <button class="btn btn-success update-image">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="wpaintContainer">
    <div id="wPaintMobile" style="position:relative; margin:0px auto"></div>
    <div class="modal-footer" class="tableRowButtons" style="background-color: #fff;border-bottom: 1px solid #ccc;border-top: 0px;">
        <button class="btn btn-info new-window-open" style="float: left;">Open in new window</button>
        <button class="btn btn-warning close-wpaint" style="float: left;">Close</button>
        <button class="btn btn-success update-image" style="float: left;">Update</button>
    </div>
</div>
