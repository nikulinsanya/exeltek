<form action="" method="post" class="margin-10">
    <input type="hidden" id="location" name="location" value="" />
    <table class="col-container job-submit">
        <tr>
            <td>
                <label>Job ID:</label>
                <div>
                    <?=$job['_id']?>
                </div>
            </td>
            <td class="extra"></td>
        </tr>
        <?php $index = 0;foreach (Form::$static_title as $column):?>
            <?php if (0 == $index++ % 2) :?>
                <tr>
                <?php $tdIndex=0;?>
            <?php endif; ?>
                <td>
                    <label class="control-label"><?=Columns::get_name($column)?>:</label>
                    <div>
                        <?=Columns::output(Arr::get($job['data'], $column), Columns::get_type($column))?>
                    </div>
                </td>

            <?php if ($tdIndex++==0 && sizeof(Form::$static_title) == $index): ?>
                <td class="extra"></td>
            <?php endif; ?>

            <?php if (0 == $index % 2) :?>
                </tr>
            <?php endif; ?>
        <?php endforeach;?>
    </table>


    <div class="upload-buttons">
        <button type="button" class="btn btn-primary upload" data-target="<?=URL::base()?>search/" data-id="<?=$job['_id']?>">Upload</button>
    </div>


    <table class="col-container job-submit">
        <tr>
            <td>
                <label>Is job completed? (please select)</label>
                <div>
                    <select name="completed" id="job-completed" class="form-control">
                        <option value=""></option>
                        <?php foreach ($columns as $key => $values):?>
                            <option value="<?=crc32($key)?>"><?=HTML::chars($key)?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </td>
            <td class="extra"></td>
        </tr>
        <?php  foreach ($columns as $key => $values):?>
            <tr id="fields-<?=crc32($key)?>" class="custom-jobs-container hidden">
                <?php $tdIndex=0;?>
                <td colspan="2">
                    <label><?=$key?>:</label>
                    <div class="width100">
                        <table class="flatten-sub-table width100">
                            <?php foreach ($values as $id => $value): $index = 0; $status = Arr::path($job_values, 'data' . intval($id) . '.status'); $old = Arr::path($job_values, 'data' . intval($id) . '.value'); ?>
                                <?php if (0 == $index++ % 2) :?>
                                    <tr>
                                <?php endif; ?>
                                <td>
                                    <div class="status-cell <?=$status === -1 ? 'bg-success has-success' : ($status === 1 ? 'bg-warning has-warning' : '')?>">
                                        <label class=""><?=$value?>:</label>
                                        <?php if ($old):?>
                                            <br/><label class="old_value control-label <?=strlen($old) > 100 ? 'shorten' : ''?> " >Last submitted value: <span class=""><?=$old?></span></label>

                                        <?php endif;?>
                                        <p class="column-value"><?=Columns::input('data-' . crc32($key) . '[' . $id . ']', NULL, Columns::get_type(intval($id)), $id == 242 ? Arr::path($job, 'data.242') : '')?></p>
                                    </div>
                                </td>
                                <?php if (0 == $index % 2) :?>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach;?>
                        </table>
                    </div>
                </td>


            <?php if ($tdIndex++==0 && sizeof($columns) == $index): ?>
                <td class="extra"></td>
            <?php endif; ?>
            </tr>
        <?php endforeach;?>


    </table>

    <div class="upload-buttons">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="signed" id="signature-checked">
                I have verified that all information to be submitted on this page are true and correct. Correction at later stage may not be possible.
            </label>
        </div>
        <div id="signature-warning">Please sign below:</div>
        <input id="signature" name="signature" type="hidden" />
        <canvas class="panel panel-default" width="400" height="260"></canvas><br/>
        <button type="button" class="btn btn-warning clear-signature">Clear signature</button>
        <button type="submit" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-danger back-button">Back</button>
    </div>
</form>

<?=View::factory('Jobs/UploadFile')?>
<script src="<?=URL::base()?>js/lib/signature_pad.min.js"></script>
<script src="<?=URL::base()?>js/lib/signature.js"></script>