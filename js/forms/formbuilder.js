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
                this.getReports();
            }
            this.initSortable();
            this.setHandlers();
        },

        getReports: function(){
            var id = $('#form-report').val(),
                html = [];
            if(!id){
                return false;
            }
            $.ajax({
                type:'get',
                url: utils.baseUrl() + 'security/reports/load?id='+id,
                dataType:'json',
                success:function(data){
                    html.push('<option value="">Select destination</option>')
                    for(var i in data.data){
                        html.push(
                            '<option value="',
                            data.data[i]['id'],
                            '">',
                            data.data[i]['name'],
                            '</option>'
                        )
                    }
                    $('#destination').html(html.join(''));
                }
            });
        },

        fillCellForm: function(cell){
            var type = cell.attr('data-type'),
                $parent = $('#addField');
            switch (type) {
                case 'label':
                case 'text':
                case 'number':
                case 'float':
                case 'date':
                    $('#placeholder-type').val(cell.attr('data-placeholder'));
                    break;
                case 'ticket':
                    var html = [],
                        ticketId = cell.attr('data-ticket-id');
                    $('.ticket-type-config').show();
                    $('#field-type').html('');
                    getColumns().then(function(data){
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
            if($('#destination').find('option[value="'+cell.attr('data-destination')+'"]')){
                $('#destination').val(cell.attr('data-destination'))
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
                case 'text':
                case 'number':
                case 'float':
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
//                    new SignaturePad($('.signature-type-config').find('canvas').get(0));
                    break;
            }
            $('#placeholder-type').trigger('focus');
        },
        confirmAddField: function(e){
            e.preventDefault();
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
                case 'number':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type','number');
                    $selectedCell.attr('data-placeholder',value);
                    $selectedCell.html('<input type="number" placeholder="'+value+'">');
                    $('#placeholder-type').val('');
                    break;
                case 'float':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type','float');
                    $selectedCell.attr('data-placeholder',value);
                    $selectedCell.html('<input type="number" step="0.01" placeholder="'+value+'">');
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

            $selectedCell.attr('data-destination',$('#destination').val());

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
                url : utils.baseUrl() + 'form/save?id=' + id + '&type=' + $('#form-type').val() + '&report=' + $('#form-report').val() + '&name=' + encodeURIComponent($('#form-name').val()),
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
                obj, trs,tds, input,
                c, r,
                destination,
                widthSettings,
                width,
                data;

            tables.each(function(){
                obj = {
                    type:'table',
                    style: $(this).attr('style'),
                    'data-style': $(this).attr('data-style'),
                    class: $(this).attr('data-class'),
                    'width-settings': [],
                    data:[]
                };
                widthSettings = [];
                $(this).find('tr.tmp-cell').first().find('td').each(function(){

                    if($(this).attr('data-resized')){
                        widthSettings.push($(this).outerWidth()+'px;');
                    }else{
                        widthSettings.push('auto;');
                    }

                });
                obj['width-settings'] = widthSettings;



                $(this).find('tr').not('.tmp-cell').each(function(){
                    data = [];
                    var destinations = [];
                    $('#destination>option').each(function(i, e) {
                        destinations[$(e).attr('value')] = 1;
                    });
                    $(this).find('td').not('.tmp-cell').each(function(){
                        value = $(this).attr('data-value');
                        destination = $(this).attr('data-destination');
                        if (destinations[destination] == undefined) destination = '';
                        input = {
                            type : $(this).attr('data-type'),
                            placeholder: $(this).attr('data-placeholder'),
                            name: ['label','ticket'].indexOf($(this).attr('data-type')) == -1 ?
                                $(this).attr('data-name') || self.guid():
                                '',
                            value:value,
                            destination: destination

                        };
                        if ($(this).attr('data-type') == 'options'){
                            input.options = $(this).find('option').map(function(){return $(this).val()}).toArray().join(',')
                        }
                        data.push(input);
                    });
                    obj.data.push(data);
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
            container.append(html.join(''));
            this.updateCanvases();
            if(!this._editable){
                this.updateTicketLabels();
            }
        },

        loadElement: function(element){
            var i, j,
                self = this,
                current,
                html = [];

            switch (element.type){
                case 'table':
                    html.push('<div class="table-container ',this._editable ? 'user-edit' : '','"><i class="glyphicon glyphicon-move"></i><button class="btn btn-danger remove-table btn-xs"><i class="glyphicon glyphicon-trash"></i></button><button class="btn btn-info config-table btn-xs"><i class="glyphicon glyphicon-cog"></i></button><table data-style=\''+element['data-style']+'\' style="'+element.style+'" class="table-responsive table table-bordered editable-table '+element.class+'"><tbody class="ui-sortable">');

                    if(!this._editable){
                        html.push('<tr class="tmp-cell">');
                        html.push('<td class="tmp-cell dummy-cell"  style="width:auto;"></td>');

                        for(j=0;j<element.data[0].length;j++){
                            html.push(
                                '<td class="tmp-cell" ',
                                element['width-settings'] ? 'data-resized="true" style="width:'+element['width-settings'][j+1]+'"' : '',
                                '><button class="btn btn-danger btn-xs remove-column" data-c="',j+1,'"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-down"></span></button></td>');
                        }
                        html.push('</tr>');
                    }

                    var t = false;
                    for(i=0;i<element.data.length;i++){
                        html.push('<tr>');
                        if(!element.data[i].length && (t || !this._editable)){
                            html.push('<td class="editable-cell" data-type="label"></td>');
                        }
                        if(!this._editable){
                            html.push('<td class="tmp-cell"><button class="btn btn-danger btn-xs remove-row"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-right"></span></button></td>');
                        }
                        t = true;
                        for(j=0;j<element.data[i].length;j++){
                            current = element.data[i][j];
                            if(this._editable && element['width-settings']){
                                current['width-settings'] = element['width-settings'][j+1]
                            }
                            html.push(self.loadElement(current));
                        }
                        html.push('</tr>');
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
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        ' data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'"  data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<span>',element.placeholder,'</span>',
                        '<input type="hidden" value="',element.placeholder,'"></input>',
                        '</td>'
                    );
                    break;
                case 'text':
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<input name="',element.name,'" type="text" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'number':
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<input name="',element.name,'" type="number" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'float':
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<input name="',element.name,'" step="0.01" type="number" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'date':
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<input name="',element.name,'" type="text" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'ticket':
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<span>TICKETFIELD',element.placeholder,'</span>',
                        '<input type="hidden" value="',element.value,'"></input>',
                        '</td>'
                    );
                    break;
                case 'signature':
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
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
                            '" ',
                            element.value == available[i] ? 'selected="selected"' : '',
                            '>',
                            available[i],
                            '</option>');
                    }
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        '  data-type="',
                        element.type,
                        '" data-placeholder="',element.placeholder,'" data-name="',element.name,'" data-value="',element.value,'" data-destination="',
                        element.destination,
                        '">',
                        '<select name="',element.name,'">',
                       options.join(''),
                        '</select>',
                        '</td>'
                    );
                    break;
                default:
                    html.push('<td class="editable-cell" ',
                        element['width-settings'] ? ('style="width:'+element['width-settings']+'"') : '',
                        ' data-type="label"></td>');
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
                items: "tr:not('.tmp-cell')",
                placeholder: "ui-state-highlight"
            });
            $('tbody').disableSelection();

            self._formContainer.sortable({
                placeholder: "ui-state-highlight"
            });
            self._formContainer.disableSelection();
        },

        initResize: function(){
            var self = this;
            $("#form-builder-container table.editable-table").each(function(){
               $(this).find('tr').first().find('td:not(".dummy-cell")').resizable({
                   handles: "e",
                   stop:function(e,ui){
                       $(e.target).attr('data-resized',true);
                   }
               });
            })
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

                setTimeout(function(){
                    $('#placeholder-type').focus();
                },500);
            });
            this._formContainer.on('click','.remove-table',function(e){
                var self = this;
                if(confirm('Do you want to remove table?')){
                    $(self).next().remove();
                }
            });
            this._formContainer.on('click','.config-table',function(e){
                var self  = this,
                    dataStyle = $(this).parents('.table-container').find('table').attr('data-style'),
                    table = $(this).parent().find('table').first();

                $('.selected-table').removeClass('selected-table');
                table.addClass('selected-table');

                if(dataStyle){

                    try{
                        dataStyle = JSON.parse(dataStyle);
                        $('#table-border').val(dataStyle.border);
                        $('#table-color').val(dataStyle.borderColor);
                        if(table.hasClass('not-bordered')){
                            $('#cells-border').val('not-bordered');
                        }
                    }catch(e){

                    }

                }else{
                    $('#table-border').val($('#table-border').find('option').first().val());
                    $('#table-color').val($('#table-color').find('option').first().val());
                    if(table.hasClass('not-bordered')){
                        $('#cells-border').val('not-bordered');
                    }
                }

                $('#configTable').modal('show');
            });
            this._formContainer.on('click','.remove-column',function(e){
                var self = this,
                    index,
                    i,
                    table,
                    cols;
                if(confirm('Do you want to remove column?')){
                    table = $(this).parents('table').first();
                    index = $(this).attr('data-c');

                    table.find('tr').each(function(){
                        cols = $(this).find('td');
                        if(cols[index]){
                            cols[index].remove();
                        }
                    });
                    table.find('[data-c]').each(function(){
                        $(this).attr('data-c',$(this).attr('data-c')-1);
                    });

                }
            });

            this._formContainer.on('click','.remove-row',function(e){
                var self = this;
                if(confirm('Do you want to remove row?')){
                    $(self).parents('tr').first().remove();
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
            $('#remove-option').on('click',function(){
                var value = $('#options-preview').val();
                $("#options-preview option[value='"+value+"']").remove();
            });


            $('#confirm-insert-field').on('click',self.confirmAddField);

            $('#form-insert-field').on('submit',self.confirmAddField)

            $('.confirm-insert-table').on('click',function(){
                var cols = $('#cols-number').val(),
                    rows = $('#rows-number').val(),
                    html = [],
                    i,j;
                html.push('<div class="table-container"><i class="glyphicon glyphicon-move"></i><button class="btn btn-danger remove-table btn-xs"><i class="glyphicon glyphicon-trash"></i></button><button class="btn btn-info config-table btn-xs"><i class="glyphicon glyphicon-cog"></i></button><table class="table-responsive table table-bordered editable-table"><tbody>');
                html.push('<tr class="tmp-cell">');
                html.push('<td class="tmp-cell  dummy-cell"></td>');
                for (j = 0;j<cols;j++){
                    html.push('<td class="tmp-cell" style="width:auto;"><button class="btn btn-danger btn-xs remove-column" data-c="',j+1,'"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-down"></span></button></td>');
                }
                html.push('</tr>');
                for (i = 0;i<rows;i++){
                    html.push('<tr>');
                    html.push('<td class="tmp-cell"><button class="btn btn-danger btn-xs remove-row"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-right"></span></button></td>');
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
                self.initResize();
            });
            $('.confirm-table-settings').on('click',function(){
                var table = $('.selected-table'),
                    borderVal = $('#table-border').val()|| '0',
                    borderColor = $('#table-color').val() || '#ccc',
                    style = "border:"+borderVal+"px solid " +borderColor,
                    className = $('#cells-border').val() || '';

                table
                    .attr('style',style)
                    .attr('data-style',JSON.stringify({border:borderVal,borderColor:borderColor}))
                    .attr('data-class',className);

                table.removeClass('not-bordered').addClass(className);

                table.removeClass('selected-table');
                $('#configTable').modal('hide');
            });

            $('.add-row').on('click',function(){
                var table = $('.selected-table'),
                    row = table.find('tr').last().clone();
                row.find('td:not(".tmp-cell")').each(function(){
                    $(this).html('');
                    var attrs = this.attributes;
                    var toRemove = [];
                    var element = $(this);

                    for (attr in attrs) {
                        if (typeof attrs[attr] === 'object' &&
                            typeof attrs[attr].name === 'string' &&
                            (/^data-/).test(attrs[attr].name)) {
                            toRemove.push(attrs[attr].name);
                        }
                    }

                    for (var i = 0; i < toRemove.length; i++) {
                        element.removeAttr(toRemove[i]);
                    }
                });


                table.removeClass('selected-table').append(row);
                $('#configTable').modal('hide');
                self.initResize();
            });

            $('.add-column').on('click',function(){
                var table   = $('.selected-table'),
                    firstTd = table.find('tr').first().find('td').last().clone();

                table.find('tr').first().append(firstTd);
                table.find('tr:not(".tmp-cell")').each(function(){
                    $(this).append('<td class="editable-cell"></td>');
                });

                table.removeClass('selected-table');
                $('#configTable').modal('hide');
                self.initResize();
            });


            $('#form-save').on('click', function(){
                if(confirm('Save form and close the editor?')){
                    self.sendForm();
                }
            });

            $('#form-report').on('change', function(){
                self.getReports();
            })
        }
    }
})(window);

$(function () {
    $('#form-type').on('change',function(e){
        if($(this).val() != 1){
            $('td[data-type="ticket"]').html('').attr('data-type','label');
            $('#fieldType').find('option[value="ticket"]').attr('disabled','disabled');
        }else{
            $('#fieldType').find('option[value="ticket"]').removeAttr('disabled');
        }
    });

    $('.form-edit-link').click(function() {
        var id = $(this).attr('data-id');
        $('#forms-list').hide();

        if (id == undefined) {
            $('#form-builder').attr('data-id', '');
            $('#form-name').val('');
            $('#form-type').val('');
            $('#form-report').val('');
            formbuilder.initForm('#form-builder-container');
            $('#form-builder').removeClass('hidden');
        } else {
            $.get(utils.baseUrl() + 'form/load?id=' + id, function(data) {
                $('#form-builder').attr('data-id', id);
                $('#form-name').val(data.name);
                $('#form-type').val(data.type);
                $('#form-report').val(data.report);
                formbuilder.initForm('#form-builder-container',data.data);
                $('#form-builder').removeClass('hidden');
                formbuilder.initResize();
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
                name:$(this).parent().attr('data-name'),
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
                $('html').html(e.responseText);
            }
        });
    });
});