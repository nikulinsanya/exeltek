<div class="modal fade" id="upload-dialog" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">File upload dialog</h4>
            </div>
            <div class="">
                <input type="hidden" id="location" />
                <div class="">
                    <table class="col-container">
                        <tr>
                            <td>
                                <label>
                                    File group:
                                </label>
                                <div class="">
                                <select class="form-control" id="file-type">
                                    <option data-capture="" data-accept="" value="other" selected="selected">Other</option>
                                    <option data-capture="" data-accept="image/*" value="photo-before">Dilapidation photos - before</option>
                                    <option data-capture="" data-accept="image/*" value="photo-after">Dilapidation photos - after</option>
                                    <option data-capture="" data-accept="image/*,application/pdf,application/x-pdf" value="jsa">JSA Form</option>
                                    <option data-capture="" data-accept="" value="otdr">OTDR Traces</option>
                                    <option data-capture="" data-accept="" value="waiver">Waiver Form</option>
                                </select>
                                </div>
                            </td>
                            <td>
                                <label>
                                    Title:
                                </label>
                                <div class="">
                                <input type="text" class="form-control" id="file-title" />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input type="file" id="file-content" />
                            </td>
                        </tr>
                    </table>




                </div>



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
