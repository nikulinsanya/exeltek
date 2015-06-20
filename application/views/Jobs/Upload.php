<div id="error" class="alert hidden alert-danger">
</div>
<div id="file-type">
    <h2>Please, select file type:</h2>
    <label class="control-label"><input type="radio" name="file-type" id="file-type-partial"/> Partial import</label><br/>
    <label class="control-label"><input type="radio" name="file-type" id="file-type-full"/> Full import</label>
</div>
<div id="upload" class="hidden">
    <h2>Please, select file</h2>
    <input id="fileupload" type="file" name="files[]" data-url="<?=URL::base()?>imex/upload"> <br/>
</div>
<div id="div-progress" class="hidden">
    <h2>Upload progress: <span id="progress">0%</span></h2>
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
