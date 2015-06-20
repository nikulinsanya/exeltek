<div id="error" class="alert hidden alert-danger">
</div>
<div id="upload">
    <h2>Please, select file: </h2>
    <input id="fileupload" type="file" name="files[]" data-url="<?=URL::base()?>defects/upload"> <br/>
</div>
<div id="div-progress" class="hidden">
    <h2>Upload progress: <span id="progress">0%</span></h2>
</div>
<div id="process" class="hidden">
    <h3>File name: </h3><span id="import-name" data-url="<?=URL::base()?>defects/upload/process/"></span>
    <h3>Time elapsed: </h3><span id="import-time">0</span> s.
    <h3>Memory usage: </h3><span id="import-memory" value="0">0B</span>
    <h3>Total rows: </h3><span id="import-total">0</span>
    <h3>Inserted: </h3><span id="import-inserted">0</span>
    <h3>Updated: </h3><span id="import-updated">0</span>
    <h3>Deleted: </h3><span id="import-deleted">0</span>
    <h3>Skipped: </h3><span id="import-skipped">0</span>
    <h3>Progress: <span id="import-progress">0%</span></h3>
    <h2 id="import-done" class="hidden">File succesfully imported! You can view <a id="reports-link" href="<?=URL::base()?>defects/reports?file=">import report now.</a></h2>
</div>
