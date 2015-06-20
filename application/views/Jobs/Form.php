<form action="" method="post">
    <input type="hidden" id="location" name="location" value="" />
    <div class="form-group">
        <label class="control-label">Job ID:</label>
        <p><?=$job['_id']?></p>
    </div>
    <?php foreach (Form::$static_title as $column):?>
    <div class="form-group">
        <label class="control-label"><?=Columns::get_name($column)?>:</label>
        <p><?=Columns::output(Arr::get($job['data'], $column), Columns::get_type($column))?></p>
    </div>
    <?php endforeach;?>
    <div class="form-group">
    <button type="button" class="btn btn-primary upload" data-target="<?=URL::base()?>search/" data-id="<?=$job['_id']?>">Upload</button>
    </div>
    <div class="form-group">
        <label class="control-label">Is job completed? (please select)</label>
        <select name="completed" id="job-completed" class="form-control">
            <option value=""></option>
            <?php foreach ($columns as $key => $values):?>
                <option value="<?=crc32($key)?>"><?=HTML::chars($key)?></option>
            <?php endforeach;?>
        </select>
    </div>
    <?php foreach ($columns as $key => $values):?>
    <div id="fields-<?=crc32($key)?>" class="fields-group hidden">
        <?php foreach ($values as $id => $value): $status = Arr::path($job_values, 'data' . intval($id) . '.status'); $old = Arr::path($job_values, 'data' . intval($id) . '.value'); ?>
            <div class="form-group  <?=$status === -1 ? 'bg-success has-success' : ($status === 1 ? 'bg-warning has-warning' : '')?>">
                <label class="control-label"><?=$value?>:</label>
                <?php if ($old):?>
                <br/><label class="control-label">Last submitted value: <?=$old?></label>
                <?php endif;?>
                <p><?=Columns::input('data-' . crc32($key) . '[' . $id . ']', NULL, Columns::get_type(intval($id)), $id == 242 ? Arr::path($job, 'data.242') : '')?></p>
            </div>
        <?php endforeach;?>
    </div>
    <?php endforeach;?>
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
</form>

<?=View::factory('Jobs/UploadFile')?>
<script src="<?=URL::base()?>js/signature_pad.min.js"></script>
<script src="<?=URL::base()?>js/signature.js"></script>