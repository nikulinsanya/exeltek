<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">File upload dialog</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="location" />
                <div class="modal-body form-group">
                    <label>
                        File group:
                        <select class="form-control" id="file-type">
                            <option data-capture="" data-accept="" value="other" selected="selected">Other</option>
                            <option data-capture="camera" data-accept="image/*" value="photo-before">Dilapidation photos - before</option>
                            <option data-capture="camera" data-accept="image/*" value="photo-after">Dilapidation photos - after</option>
                            <option data-capture="camera" data-accept="image/*,application/pdf,application/x-pdf" value="jsa">JSA Form</option>
                            <option data-capture="" data-accept="" value="otdr">OTDR Traces</option>
                            <option data-capture="" data-accept="" value="waiver">Waiver Form</option>
                        </select>
                    </label>
                </div>
                <div class="modal-body form-group">
                    <label>
                        Title:
                        <input type="text" class="form-control" id="file-title" />
                    </label>
                </div>
                        <input type="file" id="file-content" />
                <div class="progress hidden">
                    <div id="upload-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        0%
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success hidden" id="start-upload">Upload</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>