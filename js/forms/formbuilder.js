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
            this._editable = editable;
            this._formContainer = $(container);

            if(json){
                this.loadJson(json);
            }
            this.initSortable();
            this.setHandlers();
        },
        fillCellForm: function(cell){
            var type = cell.attr('data-type'),
                $parent = $('#addField');
            switch (type) {
                case 'label':
                case 'text':
                case 'date':
                    $('#placeholder-type').val(cell.attr('data-placeholder'));
                    break;
                case 'ticket':
                    var html = [],
                        ticketId = cell.attr('data-ticket-id');
                    $('.ticket-type-config').show();
                    $('#field-type').html('');
                    getColumns().then(function(data){
                        data.sort(function(a,b){
                            return a.name<b.name ? -1 : 1;
                        });
                        for(i in data){
                            html.push(
                                '<option value="',
                                data[i].id,
                                '"',
                                data[i].id == ticketId ? ' selected="selected" ' : '',
                                '>',
                                data[i].name,
                                '</option>');
                        }
                        $('#field-type').html(html.join(''));
                    });
                    break;
                case 'options':
                    $('#options-preview').html(cell.find('select').html());
                    break;
                case 'signature':
                    var origCanvas = cell.find('canvas')[0],
                        $canvas = $('#signature-canvas'),
                        ctxOrign = origCanvas.getContext('2d');
                    ctxOrign.drawImage($canvas[0],0,0);
                    break;
            }
        },
        refreshFieldForm: function(cell){
            var type = cell && cell.attr('data-type') || $('#fieldType').val(),
                $parent = $('#addField');
            if(cell){
                $('#fieldType').val(type);
            }
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
                    $('.ticket-type-config').show();
                    if(!$('#field-type').find('option').length){
                        var html = [];
                        $('.ticket-type-config').show();
                        $('#field-type').html('');
                        getColumns().then(function(data){
                            data.sort(function(a,b){
                                return a.name<b.name ? -1 : 1;
                            });
                            for(i in data){
                                html.push(
                                    '<option value="',
                                    data[i].id,
                                    '" ',
                                    cell && cell.attr('data-value') == data[i].id ? 'selected="selected"' : '',
                                    '>',
                                    data[i].name,
                                    '</option>');
                            }
                            $('#field-type').html(html.join(''));
                        });
                    }
                    break;
                case 'options':
                    $('.options-type-config').show();
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
                        ticket = $('#field-type').find('option:selected').text();
                    $selectedCell.attr('data-type','ticket');
                    $selectedCell.attr('data-value',value);
                    $selectedCell.html('<span>'+ticket+'</span>');
                    $('#field-type').val('');
                    break;
                case 'options':
                    var value = $('#options-preview').val(),
                        select = $('#options-preview').clone();
                    select.removeAttr('id');
                    $selectedCell.attr('data-type','options');
                    $selectedCell.attr('data-value',value);
                    $selectedCell.html(select);
                    $('#options-preview').val('');
                    break;
                case 'signature':
                    var $canvas  = $('#signature-canvas').clone(),
                        value    = $canvas[0].toDataURL(),
                        ctxOrign = $canvas[0].getContext('2d');
                    $selectedCell.attr('data-value',value);
                    $selectedCell.html($canvas);
                    $selectedCell.attr('data-type','signature');
                    $canvas.removeAttr('id');
                    ctxOrign.drawImage($('#signature-canvas')[0],0,0);
                    break;
            }

            $('#addField').modal('hide');
            $('.selected-cell').removeClass('selected-cell');
        },
        guid: function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        },

        sendForm: function(){
            var self = this,
                json = self.serializeForm(),
                id = $('#form-builder').attr('data-id');
            return $.ajax({
                url : utils.baseUrl() + 'form/save?id=' + id + '&type=' + $('#form-type').val() + '&name=' + encodeURIComponent($('#form-name').val()),
                type: 'POST',
                data: JSON.stringify(json),
                success: function(){
                    window.location.reload(true);
                }
            });
        },

        serializeForm: function(){
            var i, j,
                table,
                value,
                json = [],
                self = this,
                container = this._formContainer,
                tables = container.find('table'),
                obj, trs,tds, input;

            tables.each(function(){
                obj = {
                    type:'table',
                    items:[]
                };
                $(this).find('tr').each(function(){
                    trs = {
                        type:'tr',
                        items:[]
                    };
                    $(this).find('td').each(function(){
                        tds = {
                            type:'td',
                            items:[]
                        };
                        value = $(this).attr('data-value');

                        input = {
                            type : $(this).attr('data-type'),
                            placeholder: $(this).attr('data-placeholder'),
                            name: $(this).attr('data-name') || self.guid(),
                            value:value
                        };
                        if ($(this).attr('data-type') == 'options'){
                            input.options = $(this).find('option').map(function(){return $(this).val()}).toArray().join(',')
                        }

                        tds.items.push(input);

                        trs.items.push(tds);
                    });
                    obj.items.push(trs);
                });
                json.push(obj);
            });

            return(json);
        },

        loadJson: function(data){
            var i, j,
                html = [],
                self = this,

                container = this._formContainer;

            for(i=0;i<data.length;i++){
                html.push(self.loadElement(data[i]));
            }
            container.append(html.join());
            this.updateCanvases();
            if(!this._editable){
                this.updateTicketLabels();
            }
        },

        loadElement: function(element){
            var i,
                self = this,
                html = [];

            switch (element.type){
                case 'table':
                    html.push('<div class="table-container ',this._editable ? 'user-edit' : '','"><i class="glyphicon glyphicon-move"></i><button class="btn btn-danger remove-table btn-xs">Remove</button><table class="table-responsive table table-bordered editable-table"><tbody class="ui-sortable">');
                    for(i=0;i<element.items.length;i++){
                        html.push(self.loadElement(element.items[i]));
                    }
                    html.push('</tbody></table></div>');
                    break;
                case 'tr':
                    html.push('<tr>');
                    for(i=0;i<element.items.length;i++){
                        html.push(self.loadElement(element.items[i]));
                    }
                    html.push('</tr>');
                    break;
                case 'td':
                    if(element.items[0]){
                        html.push(self.loadElement(element.items[0]));
                    }else{
                        html.push('<td class="editable-cell" data-type="label"></td>');
                    }
                    break;
                case 'label':
                    html.push('<td class="editable-cell" data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'">',
                        '<span>',element.placeholder,'</span>',
                        '<input name="',element.name,'" type="hidden" value="',element.placeholder,'"></input>',
                        '</td>'
                    );
                    break;
                case 'text':
                    html.push('<td class="editable-cell" data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'">',
                        '<input name="',element.name,'" type="text" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'date':
                    html.push('<td class="editable-cell" data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'">',
                        '<input name="',element.name,'" type="text" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'ticket':
                    html.push('<td class="editable-cell" data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'">',
                        '<span>TICKETFIELD',element.placeholder,'</span>',
                        '<input name="',element.name,'" type="hidden" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'signature':
                    html.push('<td class="editable-cell" data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'">',
                        '<canvas width="200" height="100"></canvas>',
                        '<input name="',element.name,'" type="hidden" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'options':
                    var options = [], i,
                        available = element.options && element.options.split(',') || [];
                    for(i=0;i<available.length;i++){
                        options.push( '<option value="',
                            available[i],
                            '">',
                            available[i],
                            '</option>');
                    }
                    html.push('<td class="editable-cell" data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'">',
                        '<select name="',element.name,'">',
                       options.join(''),
                        '</select>',
                        '</td>'
                    );
                    break;
            }

            return html.join('');
        },

        updateCanvases: function(){
            this._formContainer.find('td[data-type="signature"]').each(function(){
                var canvas = $(this).find('canvas').get(0);
                var context = canvas.getContext('2d');
                var img = new Image();

                img.onload = function() {
                    context.drawImage(this, 0, 0, canvas.width, canvas.height);
                }

                img.src = $(this).attr('data-value');
            })

        },

        updateTicketLabels: function(){
            var self = this;
            getColumns().then(function(data){
                self._formContainer.find('td[data-type="ticket"]').each(function(){
                    var val = $(this).attr('data-value');
                    var result = $.grep(data, function(e){ return e.id == val});
                    $(this).find('span').text(result && result[0].name);
                })
            });
        },

        initSortable: function(){
            if(this._editable){
                return false;
            }
            var self = this;
            $('tbody').sortable({
                placeholder: "ui-state-highlight"
            });
            $('tbody').disableSelection();

            self._formContainer.sortable({
                placeholder: "ui-state-highlight"
            });
            self._formContainer.disableSelection();
        },

        setHandlers: function(){
            if(this._editable){
                this._formContainer.find('canvas').each(function(){
                    new SignaturePad(this);
                });

                this._formContainer.find('td[data-type="date"] input').datepicker({
                    dateFormat: 'dd-mm-yy'
                });

                return false;
            }

            var self = this;
            $('.add-table').on('click',function(){
                $('#addTable').modal('show');
            });
            this._formContainer.on('click','.editable-cell',function(e){
                $('.selected-cell').removeClass('selected-cell');
                $(this).addClass('selected-cell');
                $('#addField').modal('show');
                self.fillCellForm($(this));
                self.refreshFieldForm($(this));
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
                html.push('<div class="table-container"><i class="glyphicon glyphicon-move"></i><button class="btn btn-danger remove-table btn-xs">Remove</button><table class="table-responsive table table-bordered editable-table"><tbody>');
                for (i = 0;i<rows;i++){
                    html.push('<tr>');
                    for (j = 0;j<cols;j++){
                        html.push('<td class="editable-cell">');

                        html.push('</td>');
                    }
                    html.push('</tr>');
                }
                html.push('</tbody></table></div>')
                self._formContainer.append(html.join(''));
                $('#addTable').modal('hide');
                self.initSortable();
            });

            $('#form-save').on('click', function(){
                if(confirm('Save form and close the editor?')){
                    self.sendForm();
                }
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
            $('#form-builder').removeClass('hidden');
        } else {
            $.get(utils.baseUrl() + 'form/load?id=' + id, function(data) {
                $('#form-builder').attr('data-id', id);
                $('#form-name').val(data.name);
                $('#form-type').val(data.type);
                formbuilder.initForm('#form-builder-container',data.data);
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