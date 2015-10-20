window.formbuilder = (function() {
    function getColumns(){
        return $.ajax({
            url:utils.baseUrl() + 'json/columns',
            type: 'get',
            dataType: 'JSON'
        })
    }

    return {
        _formContainer: null,
        initForm: function(container, json, editable){
            if(!$(container)){
                alert('Wrong form container');
                return;
            }
            if (editable == undefined) editable = true;

            this._formContainer = $(container);

            this.setHandlers();

        },
        refreshFieldForm: function(){
            var type = $('#fieldType').val(),
                $parent = $('#addField');
            $parent.find('.type-config').hide();

            switch (type) {
                case 'label':
                    $('.placeholder-type-config').show();
                    break;
                case 'text':
                    $('.placeholder-type-config').show();
                    break;
                case 'date':
                    $('.placeholder-type-config').show();
                    break;
                case 'ticket':
                    var html = [];
                    $('.ticket-type-config').show();
                    $('#field-type').html('');
                    getColumns().then(function(data){
                        data.sort(function(a,b){
                            return a.name<b.name ? -1 : 1;
                        })
                        for(i in data){
                            html.push(
                                '<option value="',
                                data[i].id,
                                '">',
                                data[i].name,
                                '</option>');
                        }
                        $('#field-type').html(html.join(''));
                    });

                    break;
                case 'options':
                    $('.options-type-config').show();
                    $('#options-preview').html('');
                    break;
                case 'signature':
                    $('.signature-type-config').show();
                    new SignaturePad($('.signature-type-config').find('canvas').get(0));
                    break;
            }
        },
        confirmAddField: function(){
            var type = $('#fieldType').val(),
                $selectedCell = $('.selected-cell');


            switch (type) {
                case 'label':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type','label');
                    $selectedCell.attr('data-placeholder',value);
                    $selectedCell.html('<span>'+value+'</span>');
                    $('#placeholder-type').val('');
                    break;
                case 'text':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type','text');
                    $selectedCell.attr('data-placeholder',value);
                    $selectedCell.html('<input type="text" placeholder="'+value+'">');
                    $('#placeholder-type').val('');
                    break;
                case 'date':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type','date');
                    $selectedCell.attr('data-placeholder',value);
                    $selectedCell.html('<input type="text" placeholder="'+value+'">');
                    $('#placeholder-type').val('');
                    break;
                case 'ticket':
                    var value = $('#field-type').val(),
                        ticket = $('#field-type').find('option:selected').text()
                    $selectedCell.attr('data-type','ticket');
                    $selectedCell.attr('data-ticket-id',value);
                    $selectedCell.html('<span>'+value+'</span>');
                    $('#field-type').val('');
                    break;
                case 'options':
                    var value = $('#options-preview').val(),
                        select = $('#options-preview').clone();
                    $selectedCell.attr('data-type','ticket');
                    $selectedCell.attr('data-options-id',value);
                    $selectedCell.html(select);
                    $('#options-preview').val('');
                    break;
                case 'signature':
                    var value = $('#options-preview').val(),
                        $canvas = $('#signature-canvas').clone();
                    $selectedCell.html($canvas);
                    break;
            }

            $('#addField').modal('hide');
            $('.selected-cell').removeClass('selected-cell');
        },

        setHandlers: function(){
            var self = this;
            $('.add-table').on('click',function(){
                $('#addTable').modal('show');
            });
            this._formContainer.on('click','.editable-cell',function(e){
                $('.selected-cell').removeClass('selected-cell');
                $(this).addClass('selected-cell');
                $('#addField').modal('show');
                self.refreshFieldForm();
            });
            this._formContainer.on('click','.remove-table',function(e){
                var self = this;
                if(confirm('Do you want to remove table?')){
                    $(self).next().remove();
                }
            });


            $('.confirm-insert-field').on('click',function(){
                $('#addField').modal('hide');
            });
            $('#fieldType').on('change',function(){
                self.refreshFieldForm();
            });
            $('#add-option').on('click',function(){
                var value = $('#option-type-value').val();
                $('#options-preview').append('<option value="'+value+'">'+value+'</option>');
                $('#option-type-value').val('');
            });

            $('#confirm-insert-field').on('click',self.confirmAddField);

            $('.confirm-insert-table').on('click',function(){
                var cols = $('#cols-number').val(),
                    rows = $('#rows-number').val(),
                    html = [],
                    i,j;
                html.push('<div class="table-container"><button class="btn btn-danger remove-table btn-xs">Remove</button><table class="table-responsive table table-bordered editable-table">');
                for (i = 0;i<rows;i++){
                    html.push('<tr>');
                    for (j = 0;j<cols;j++){
                        html.push('<td class="editable-cell">');

                        html.push('</td>');
                    }
                    html.push('</tr>');
                }
                html.push('</table></div>')
                self._formContainer.append(html.join(''));
                $('#addTable').modal('hide');
            });
        }
    }
})(window);

$(function () {

    $('.form-edit-link').click(function() {
        var id = $(this).attr('data-id');
        $('#forms-list').hide();

        if (id == undefined) {
            $('#form-builder').attr('data-id', '');
            $('#form-name').val('');
            $('#form-type').val('');
            formbuilder.initForm('#form-builder-container');
//            form.init($('#form-builder-container'), []);
            $('#form-builder').removeClass('hidden');
        } else {
            $.get(utils.baseUrl() + 'form/load?id=' + id, function(data) {
                $('#form-builder').attr('data-id', id);
                $('#form-name').val(data.name);
                $('#form-type').val(data.type);
                form.init($('#form-builder-container'), data.data);
                $('#form-builder').removeClass('hidden');
            });
        }
    });

    $('.form-save').click(function() {
        var form = $(this).parents('form').serializeArray();
        if ($(this).hasClass('btn-info'))
            if (confirm('Do you really want to convert this file to PDF? After this, form data can\'t  be edited!'))
                form.push({
                    name: 'print',
                    value: ''
                });
            else return false;

        $('form').find('canvas').each(function(){
            form.push({
                name:$(this).attr('name'),
                value: $(this).get(0).toDataURL()
            });
        });

        $.ajax({
            url     : '',
            type    : 'POST',
            data    : form,
            success : function(data){
                window.location = data.url;
            },
            error   : function(e){
                alert(e.responseText);
            }
        });
    });
});