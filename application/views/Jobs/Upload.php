<div id="error" class="alert hidden alert-danger">
</div>
<div id="upload">
    <h2>Please, select file</h2>
    <input id="fileupload" type="file" name="files[]" accept="text/csv" data-url="<?=URL::base()?>imex/upload"> <br/>
</div>
<div id="div-progress" class="hidden">
    <h2>Upload progress: <span id="progress">0%</span></h2>
</div>
<div id="prepare" class="hidden">
    <div class="col-xs-12">
        Last update: <span id="file-last-update"></span>
    </div>
    <div class="col-xs-12">
        <label class="control-label">
            <input type="checkbox" name="skip-deleted" />
            Don't create 'deleted' log entries (update & insert only)
        </label>
    </div>
    <div class="col-xs-4">
        <label class="control-label">Date:</label>
        <input type="text" id="file-date" class="form-control datepicker" />
    </div>
    <div class="col-xs-4">
        <label class="control-label">Time of day:</label>
        <select id="file-tod" class="form-control">
            <option value="">Please, select time of day...</option>
            <option value="0">Start of day (SOD)</option>
            <option value="1">End of day (EOD)</option>
        </select>
    </div>
    <div class="col-xs-4">
        <label class="control-label">Region:</label>
        <select id="file-region" class="form-control">
            <option value="">Please, select region...</option>
            <option value="-1">None (update existing tickets)</option>
            <?php foreach ($regions as $region):?>
                <option value="<?=$region['id']?>" data-date="<?=date('d-m-Y', $region['last_update'])?>"><?=$region['name']?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div class="col-xs-12" id="file-mapping">

    </div>
    <div class="col-xs-12">
        <button class="btn btn-success disabled" id="import-start">Start import</button>
    </div>
</div>
<div id="process" class="hidden">
    <h4>File name: </h4><span id="import-name" data-url="<?=URL::base()?>imex/upload/process/"></span>
    <h4>Time elapsed: </h4><span id="import-time">0</span> s.
    <h4>Memory usage: </h4><span id="import-memory" value="0">0B</span>
    <h4>Total rows: </h4><span id="import-total">0</span>
    <h4>Inserted: </h4><span id="import-inserted">0</span>
    <h4>Updated: </h4><span id="import-updated">0</span>
    <h4>Deleted: </h4><span id="import-deleted">0</span>
    <h4>Skipped: </h4><span id="import-skipped">0</span>
    <h4>Progress: <span id="import-progress">0%</span></h4>
    <h3 id="import-done" class="text-success hidden">
        File succesfully imported!
        Now you can view <a id="reports-link" href="<?=URL::base()?>imex/reports?file=">imported changes</a>
        or <a href="<?=URL::base()?>imex/upload">upload new file.</a></h3>
</div>