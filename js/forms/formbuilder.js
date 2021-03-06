window.formbuilder = (function () {
    function getColumns() {
        return $.ajax({
            url: utils.baseUrl() + 'json/columns',
            type: 'get',
            dataType: 'JSON'
        })
    }

    return {
        _formContainer: null,
        initForm: function (container, json, editable) {
            if (!$(container)) {
                alert('Wrong form container');
                return;
            }
            this._editable = editable;
            this._formContainer = $(container);

            if (json) {
                this.loadJson(json);
                this.getReports();
                this.applyColors();
                this.checkUnbinded();
            }
            if (this._editable) {
                this.setTableRelations();
            } else {
                this.initSelect();
            }
            this.initSortable();
            this.setHandlers();


        },

        checkUnbinded: function () {
            if ($('#form-type').val() == 3) {
                $('#new-cell-details').addClass('unattached-container');
                $('#form-save').attr('is_unattached', true);
            } else {
                $('#new-cell-details').removeClass('unattached-container');
                $('#form-save').removeAttr('is_unattached');
            }
        },

        getReports: function () {
            var id = $('#form-report').val(),
                html = [];
            if (!id) {
                return false;
            }
            $.ajax({
                type: 'get',
                url: utils.baseUrl() + 'security/reports/load?id=' + id,
                dataType: 'json',
                success: function (data) {
                    html.push('<option value="">Select destination</option>')
                    for (var i in data.data) {
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

        applyColors: function () {
            $('.editable-cell[data-type="options"]').each(function () {
                var select = $(this).find('select'),
                    color = select.find('option[value="' + select.val() + '"]');
                color = color.attr('data-color');
                if (color) {
                    $(this).css('background-color', color);
                }
                ;
                select.on('change', function () {
                    var color = $(this).find('option[value="' + $(this).val() + '"]').attr('data-color');
                    $(this).parent().css('background-color', color);
                });
            });
        },
        initSelect: function () {
            $('select').selectpicker({
                size: 5,
                width: false
            });
            $('select').on('loaded.bs.select', function (e) {
                if ($(e.currentTarget).find('option[value=""]').length > 1) {
                    $(e.currentTarget).find('option[value=""]').remove();
                }
            });
        },
        fillAssignFields: function (cell) {
            var html = [],
                ticketId = cell.attr('data-assign-to'),
                i;
            getColumns().then(function (data) {
                html.push('<option value=""></option>');
                for (i in data) {
                    if (data[i].id) {
                        html.push(
                            '<option value="',
                            data[i].id,
                            '"',
                            data[i].id == ticketId ? ' selected="selected" ' : '',
                            '>',
                            data[i].name,
                            '</option>');
                    }
                }
                $('#assign-to').html(html.join(''));
                $('#assign-as').val(cell.attr('data-assign-as'));
                $('select').selectpicker('refresh');
            });
        },
        fillCellForm: function (cell) {
            var type = cell.attr('data-type'),
                $parent = $('#addField');
            $('#options-preview').html('');
            $('.ticket-bind-type-config').show();
            switch (type) {
                case 'label':
                case 'text':
                case 'number':
                case 'float':
                case 'date':
                case 'timestamp':
                case 'revision':
                    $('#placeholder-type').val(cell.attr('data-placeholder'));
                    break;
                case 'ticket':
                    var html = [],
                        ticketId = cell.attr('data-ticket-id');
                    $('.ticket-type-config').show();
                    $('.ticket-bind-type-config').hide();
                    $('#field-type').html('');
                    getColumns().then(function (data) {
                        for (i in data) {
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
                    ctxOrign.drawImage($canvas[0], 0, 0);
                    break;
            }
            if ($('#destination').find('option[value="' + cell.attr('data-destination') + '"]')) {
                $('#destination').val(cell.attr('data-destination'))
            }

            $('#option-title').val(cell.attr('data-text') || '');
        },
        refreshFieldForm: function (cell) {
            var type = cell && cell.attr('data-type') || $('#fieldType').val(),
                $parent = $('#addField'),
                self = this;
            if (cell) {
                $('#fieldType').val(type);
            }
            $parent.find('.type-config').hide();
            $('.ticket-bind-type-config').show();
            switch (type) {
                case 'label':
                case 'text':
                case 'number':
                case 'float':
                case 'date':
                case 'timestamp':
                case 'revision':
                    $('.placeholder-type-config').show();
                    break;
                case 'ticket':
                    $('.ticket-type-config').show();
                    $('.ticket-bind-type-config').hide();
                    if (!$('#field-type').find('option').length) {
                        var html = [];
                        $('.ticket-type-config').show();
                        $('#field-type').html('');
                        getColumns().then(function (data) {
                            for (i in data) {
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
                            $('#bind-field-type').html(html.join(''));
                            var parent = $('#field-type').parents('div.col-md-8'),
                                select = $('#field-type').clone();
                            parent.find('.bootstrap-select').replaceWith(select);
                            $('#field-type').selectpicker({size: 5});


                            var parent = $('#bind-field-type').parents('div.col-md-8'),
                                select = $('#bind-field-type').clone();
                            parent.find('.bootstrap-select').replaceWith(select);
                            $('#bind-field-type').selectpicker({size: 5});


                        });

                    }

                    break;
                case 'options':
                    $('.options-type-config').show();
                    break;
                case 'signature':
                    $('.signature-type-config').show();
                    break;
            }
            if (!$('#bind-field-type').find('option').length) {
                getColumns().then(function (data) {
                    var html = [];
                    for (i in data) {
                        html.push(
                            '<option value="',
                            data[i].id,
                            '" ',
                            cell && cell.attr('data-value') == data[i].id ? 'selected="selected"' : '',
                            '>',
                            data[i].name,
                            '</option>');
                    }
                    $('#bind-field-type').html(html.join(''));
                    var parent = $('#bind-field-type').parents('div.col-md-8'),
                        select = $('#bind-field-type').clone();

                    if (cell && cell.attr('data-bind-value')) {
                        var dataarray = cell.attr('data-bind-value').split(",");
                        select.val(dataarray);
                    } else {
                        select.val([]);
                    }

                    parent.find('.bootstrap-select').replaceWith(select);
                    $('#bind-field-type').selectpicker({size: 5});
                });
            } else {
                var select = $('#bind-field-type');
                if (cell && cell.attr('data-bind-value')) {
                    var dataarray = cell.attr('data-bind-value').split(",");
                    select.val(dataarray);
                } else {
                    select.val([]);
                }
                select.selectpicker('refresh');

            }


            if (cell && cell.attr('data-color')) {
                $('#color').val(cell.attr('data-color')).css('background-color', cell.attr('data-color'));
            } else {
                $('#color').val('').css('background-color', '#fff');
            }
            if (cell && cell.attr('data-required')) {
                $('#required').attr('checked', 'checked');
            } else {
                $('#required').removeAttr('checked');
            }

            $('#placeholder-type').trigger('focus');
        },
        confirmAddField: function (e) {
            e.preventDefault();
            var type = $('#fieldType').val(),
                $selectedCell = $('.selected-cell');
            $selectedCell.removeAttr('data-type data-value data-color data-text name');

            switch (type) {
                case 'timestamp':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'timestamp');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<span>' + value + '</span>');
                    $('#placeholder-type').val('');
                    break;
                case 'revision':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'revision');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<span>' + value + '</span>');
                    $('#placeholder-type').val('');
                    break;
                case 'label':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'label');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<span>' + value + '</span>');
                    $('#placeholder-type').val('');
                    break;
                case 'text':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'text');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<textarea placeholder="' + value + '"></textarea>');
                    $('#placeholder-type').val('');
                    break;
                case 'number':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'number');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<input type="number" placeholder="' + value + '">');
                    $('#placeholder-type').val('');
                    break;
                case 'float':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'float');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<input type="number" step="0.01" placeholder="' + value + '">');
                    $('#placeholder-type').val('');
                    break;
                case 'date':
                    var value = $('#placeholder-type').val();
                    $selectedCell.attr('data-type', 'date');
                    $selectedCell.attr('data-placeholder', value);
                    $selectedCell.html('<input type="text" placeholder="' + value + '">');
                    $('#placeholder-type').val('');
                    break;
                case 'ticket':
                    var value = $('#field-type').val(),
                        ticket = $('#field-type').find('option:selected').text();
                    $selectedCell.attr('data-type', 'ticket');
                    $selectedCell.attr('data-value', value);
                    $selectedCell.html('<span>' + ticket + '</span>');
                    $('#field-type').val('');
                    break;
                case 'options':
                    var value = $('#options-preview').val(),
                        select = $('#options-preview').clone(),
                        color = $('#options-preview').attr('data-color'),
                        guid = $selectedCell.attr('data-name') || window.formbuilder.guid(),
                        title = $('#option-title').val();
                    select.removeAttr('id');
                    $selectedCell.attr('data-type', 'options');
                    $selectedCell.attr('data-value', value);
                    $selectedCell.attr('data-color', color);
                    $selectedCell.attr('data-name', guid);
                    $selectedCell.attr('data-text', title);
                    select.attr('data-text', title);
                    select.attr('name', guid);
                    $selectedCell.html(select);
                    $('#options-preview').val('');
                    break;
                case 'signature':
                    var $canvas = $('#signature-canvas').clone(),
                        value = $canvas[0].toDataURL(),
                        ctxOrign = $canvas[0].getContext('2d');
                    $selectedCell.attr('data-value', value);
                    $selectedCell.html($canvas);
                    $selectedCell.attr('data-type', 'signature');
                    $canvas.removeAttr('id');
                    ctxOrign.drawImage($('#signature-canvas')[0], 0, 0);
                    break;
            }

            $selectedCell.attr('data-destination', $('#destination').val());
            if ($('#color').val()) {
                $selectedCell.attr('data-color', $('#color').val());
                $selectedCell.css('background-color', $('#color').val());
            }
            if ($('#assign-to').val()) {
                var value = $('#assign-to').val();
                $selectedCell.attr('data-assign-to', value);
            }
            if ($('#assign-as').val()) {
                var value = $('#assign-as').val();
                $selectedCell.attr('data-assign-as', value);
            }
            if ($('#required').is(':checked')) {
                $selectedCell.attr('data-required', true);
            }

            $('#addField').modal('hide');
            $('.selected-cell').removeClass('selected-cell');

            if (type != 'ticket') {
                var options = $('#bind-field-type').val();
                if (!options) {
                    return;
                }
                var value = options.join(','),
                    ticket = $('#bind-field-type').find('option:selected').text();
                $selectedCell.attr('data-bind-value', value);
                $('#bind-field-type').val('');
            }
            $('select').selectpicker('refresh');
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

        checkRequiredFields: function () {
            var container = this._formContainer || $('#form-data'),
                tables = container.find('table'),
                valid = true,
                type;
            $('.error').removeClass('error');
            tables.find('td[data-required="true"]').each(function () {
                type = $(this).attr('data-type');
                switch (type) {
                    case 'text':
                        if (!$(this).find('textarea').val()) {
                            $(this).find('textarea').addClass('error');
                            valid = false;
                        }
                        break;
                    case 'number':
                    case 'float':
                    case 'date':
                        if (!$(this).find('input[type="text"]').val()) {
                            $(this).find('input[type="text"]').addClass('error');
                            valid = false;
                        }
                        break;
                    case 'options':
                        if (!$(this).find('select').val()) {
                            $(this).find('select').addClass('error');
                            valid = false;
                        }
                        break;
                    case 'signature':
                        var canvas = $(this).find('canvas').get(0);
                        if (!canvas || canvas.toDataURL() == document.getElementById('blank-canvas').toDataURL()) {
                            $(this).find('canvas').addClass('error');
                            valid = false;
                        }
                        break;
                }
            });
            return valid;
        },

        sendForm: function () {
            var self = this,
                json = self.serializeForm(),
                id = $('#form-builder').attr('data-id');

            return $.ajax({
                url: utils.baseUrl() + 'form/save?id=' + id + '&type=' + $('#form-type').val() + '&report=' + $('#form-report').val() +
                '&name=' + encodeURIComponent($('#form-name').val()) + '&geo=' + ($('#allow-geo').prop('checked') ? '1' : '') + '&attachment=' + ($('#allow-attachment').prop('checked') ? '1' : ''),
                type: 'POST',
                data: JSON.stringify(json),
                success: function () {
                    window.location.reload(true);
                }
            });
        },

        serializeForm: function () {
            var i, j,
                table,
                value,
                json = [],
                self = this,
                container = this._formContainer,
                tables = container.find('table'),
                obj, trs, tds, input,
                c, r,
                destination,
                bindValue,
                color,
                assignTo,
                assignAs,
                title,
                widthSettings,
                width,
                parentWidth,
                data;

            tables.each(function () {
                obj = {
                    type: 'table',
                    style: $(this).attr('style'),
                    'data-style': $(this).attr('data-style'),
                    'data-related-option': $(this).attr('data-related-option'),
                    'data-related-value': $(this).attr('data-related-value'),
                    class: $(this).attr('data-class'),
                    'width-settings': [],
                    data: []
                };

                widthSettings = [];
                $(this).find('tr.tmp-cell').first().find('td').each(function () {
                    //if($(this).attr('data-resized')){
                    parentWidth = 0;
                    $(this).parent().find('td.ui-resizable').each(function () {
                        parentWidth += $(this).outerWidth();
                    })
                    width = $(this).outerWidth();
                    width = width / parentWidth * 100;
                    widthSettings.push(width + '%;');
                });

                obj['width-settings'] = widthSettings;


                $(this).find('tr').not('.tmp-cell').each(function () {
                    data = [];
                    var destinations = [];
                    var isUnattached = $('#form-save').attr('is_unattached');
                    $('#destination>option').each(function (i, e) {
                        destinations[$(e).attr('value')] = 1;
                    });
                    $(this).find('td').not('.tmp-cell').each(function (i, e) {
                        value = $(this).attr('data-value') || '';
                        destination = $(this).attr('data-destination') || '';
                        color = $(this).attr('data-color') || '';
                        assignTo = $(this).attr('data-assign-to') || '';
                        assignAs = $(this).attr('data-assign-as') || '';
                        title = $(this).attr('data-text') || '';
                        bindValue = !isUnattached && $(this).attr('data-bind-value') || '';

                        if (destinations[destination] == undefined) destination = '';

                        input = {
                            type: $(this).attr('data-type'),
                            placeholder: $(this).attr('data-placeholder'),
                            name: ['label', 'ticket', 'revision', 'timestamp'].indexOf($(this).attr('data-type')) == -1 ?
                            $(e).attr('data-name') || self.guid() :
                                '',
                            value: value,
                            destination: destination,
                            color: color,
                            assignTo: assignTo,
                            assignAs: assignAs,
                            title: title,
                            bindValue: bindValue,
                            required: !!$(this).attr('data-required')
                        };

                        if ($(this).attr('data-type') == 'options') {
                            input.options = $(this).find('option').map(function () {
                                return $(this).val()
                            }).toArray().join(',');
                            input.colors = $(this).find('option').map(function () {
                                return $(this).attr('data-color')
                            }).toArray().join(',');
                        }

                        for(var i in input){
                            if (!input[i]){
                                delete(input[i]);
                            }
                        }

                        data.push(input);
                    });
                    obj.data.push(data);
                });

                json.push(obj);
            });
            return (json);
        },

        loadJson: function (data) {
            var i, j,
                html = [],
                self = this,
                container = this._formContainer;
            for (i = 0; i < data.length; i++) {
                html.push(self.loadElement(data[i]));
            }
            container.append(html.join(''));
            this.updateCanvases();
            if (!this._editable) {
                this.updateTicketLabels();
            }
            this.recalcColSizes();
        },

        recalcColSizes: function () {
            setTimeout(function () {
                $('.tmp-cell').each(function () {
                    var cells = $(this).find('td[data-resized="true"]'),
                        length = cells.length,
                        c = 0;
                    $(cells).each(function () {
                        if (++c == length) {
                            $(this).css('width', 'auto');
                            $(this).find('.reset-width').remove();
                        } else {
                            $(this).css('width', $(this).outerWidth() + 'px');
                        }
                    });
                });
            }, 200);
        },

        loadElement: function (element) {
            var i, j,
                self = this,
                current,
                style,
                dataColor,
                html = [];

            switch (element.type) {
                case 'table':
                    html.push(
                        '<div class="table-container ', this._editable ? 'user-edit' : '', '">' +
                        '<i class="glyphicon glyphicon-move"></i>' +
                        '<button class="btn btn-danger remove-table btn-xs"><i class="glyphicon glyphicon-trash"></i></button>' +
                        '<button class="btn btn-info config-table btn-xs"><i class="glyphicon glyphicon-cog"></i></button>' +
                        '<table data-style=\'' + element['data-style'] + '\' style="' + element.style + '" ' +
                        'class="table-responsive table table-bordered editable-table ' + element.class + '" ' +
                        (element.class ? ' data-class="' + element.class + '" ' : '') +
                        (element['data-related-option'] ? (' data-related-option=\'' + element['data-related-option'] + '\'') : '') +
                        (element['data-related-value'] ? (' data-related-value=\'' + element['data-related-value'] + '\'') : '') + ' ><tbody class="ui-sortable">');

                    if (!this._editable) {
                        html.push('<tr class="tmp-cell">');
                        html.push('<td class="tmp-cell dummy-cell"  style="width:auto;"></td>');

                        for (j = 0; j < element.data[0].length; j++) {
                            html.push(
                                '<td class="tmp-cell" ',
                                (element['width-settings'] && element['width-settings'][j + 1] != 'auto;' ? 'data-resized="true" style="width:' + element['width-settings'][j + 1] + '"' : ''),
                                '><button class="btn btn-danger btn-xs remove-column" data-c="', j + 1, '"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-down"></span></button>',
                                (element['width-settings'] && element['width-settings'][j + 1] != 'auto;' ? '<a class="btn btn-warning btn-xs reset-width" style="float:right;"><i class="glyphicon glyphicon-resize-full"></i></a>' : ''),
                                '</td>');
                        }
                        html.push('</tr>');
                    }

                    var t = false;
                    for (i = 0; i < element.data.length; i++) {
                        html.push('<tr>');
                        if (!element.data[i].length && (t || !this._editable)) {
                            html.push('<td class="editable-cell" data-type="label"></td>');
                        }
                        if (!this._editable) {
                            html.push('<td class="tmp-cell"><button class="btn btn-danger btn-xs remove-row"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-right"></span></button></td>');
                        }
                        t = true;
                        for (j = 0; j < element.data[i].length; j++) {
                            current = element.data[i][j];
                            if (this._editable && element['width-settings']) {
                                current['width-settings'] = element['width-settings'][j + 1]
                            }
                            html.push(self.loadElement(current));
                        }
                        html.push('</tr>');
                    }

                    html.push('</tbody></table></div>');
                    break;
                case 'tr':
                    html.push('<tr>');
                    for (i = 0; i < element.items.length; i++) {
                        html.push(self.loadElement(element.items[i]));
                    }
                    html.push('</tr>');
                    break;
                case 'td':
                    if (element.items[0]) {
                        html.push(self.loadElement(element.items[0]));
                    } else {
                        html.push('<td class="editable-cell" data-type="label"></td>');
                    }
                    break;
                case 'label':
                case 'revision':
                case 'timestamp':
                    html.push(this.getFilledTd(element),
                        '<span>', element.placeholder, '</span>',
                        '<input type="hidden" value="', element.placeholder, '"></input>',
                        '</td>'
                    );
                    break;
                case 'text':
                    html.push(this.getFilledTd(element),
                        //'<input name="',element.name,'" type="text" placeholder="',element.placeholder,'" value="',element.value,'"></input>',
                        '<textarea name="', element.name, '" placeholder="', element.placeholder, '">', element.value, '</textarea>',
                        '</td>'
                    );
                    break;
                case 'number':
                    html.push(this.getFilledTd(element),
                        '<input name="', element.name, '" type="number" placeholder="', element.placeholder, '" value="', element.value, '"></input>',
                        '</td>'
                    );
                    break;
                case 'float':
                    html.push(this.getFilledTd(element),
                        '<input name="', element.name, '" step="0.01" type="number" placeholder="', element.placeholder, '" value="', element.value, '"></input>',
                        '</td>'
                    );
                    break;
                case 'date':
                    html.push(this.getFilledTd(element),
                        '<input name="', element.name, '" type="text" placeholder="', element.placeholder, '" value="', element.value, '"></input>',
                        '</td>'
                    );
                    break;
                case 'ticket':
                    html.push(this.getFilledTd(element),
                        '<span>TICKETFIELD', element.placeholder, '</span>',
                        '<input type="hidden" value="', element.value, '"></input>',
                        '</td>'
                    );
                    break;
                case 'signature':
                    html.push(this.getFilledTd(element),
                        '<canvas width="200" height="100"></canvas>',
                        '<input name="', element.name, '" type="hidden" value="', element.value, '"></input>',
                        '</td>'
                    );
                    break;
                case 'options':
                    var options = [], i,
                        available = element.options && element.options.split(',') || [],
                        colors = element.colors && element.colors.split(',') || [],
                        title = element.title;
                    for (i = 0; i < available.length; i++) {
                        options.push('<option value="',
                            available[i],
                            '" ',
                            element.value == available[i] ? 'selected="selected"' : '',
                            ' ',
                            'data-color="',
                            colors[i],
                            '"',
                            '>',
                            available[i],
                            '</option>');
                    }
                    html.push(this.getFilledTd(element),
                        '<select name="', element.name, '" data-text="', title, '">',
                        options.join(''),
                        '</select>',
                        '</td>'
                    );
                    break;
                default:
                    style = element['width-settings'] ?
                    'width:' + element['width-settings'] + ';' :
                        '';
                    style += element.color ?
                    'background:' + element.color + ';' :
                        '';
                    dataColor = element.color ?
                    ' data-color="' + element.color + '" ' :
                        '';
                    html.push('<td class="editable-cell"',
                        style ? ('style="' + style + '"') : '',
                        ' data-type="label"></td>');
                    break;
            }

            return html.join('');
        },

        getFilledTd: function (element) {
            var html = [],
                style = (element['width-settings'] ? 'width:' + element['width-settings'] + ';' : '') +
                    (element.color ? 'background:' + element.color + ';' : '');
                dataColor = element.color ? ' data-color="' + element.color + '" ' : '',
                assignTo = element.assignTo ? ' data-assign-to="' + element.assignTo + '" ' : '',
                assignAs = element.assignAs ? ' data-assign-as="' + element.assignAs + '" ' : '',
                required = element.required ? ' data-required="true" ' : '',
                title = element.title ? ' data-text="' + element.title + '" ' : '',
                bindValue = element.bindValue ? ' data-bind-value="' + element.bindValue + '" ' : '',
                name = element.name ? ' data-name="' + element.name + '" ' : '';

            html.push('<td class="editable-cell"',
                style ? ('style="' + style + '"') : '',
                '  data-type="',
                element.type,
                '" data-placeholder="', element.placeholder, '" data-value="', element.value, '" data-destination="',
                element.destination,
                '" ',
                name,
                dataColor,
                assignTo,
                assignAs,
                required,
                bindValue,
                title,
                '>');
            return html.join('');
        },

        updateCanvases: function () {
            this._formContainer.find('td[data-type="signature"]').each(function () {
                var canvas = $(this).find('canvas').get(0);
                var context = canvas.getContext('2d');
                var img = new Image();

                img.onload = function () {
                    context.drawImage(this, 0, 0, canvas.width, canvas.height);
                }

                img.src = $(this).attr('data-value');
            })

        },

        updateTicketLabels: function () {
            var self = this;
            getColumns().then(function (data) {
                self._formContainer.find('td[data-type="ticket"]').each(function () {
                    var val = $(this).attr('data-value');
                    var result = $.grep(data, function (e) {
                        return e.id == val
                    });
                    $(this).find('span').text(result && result[0].name);
                })
            });
        },

        initSortable: function () {
            if (this._editable) {
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

        setTableRelations: function () {
            var selects = [];

            $('table[data-related-option]').each(function () {
                var relation = $(this).attr('data-related-option'),
                    select = $('select[name="' + relation + '"]');
                select = select.length ? select : $('select[data-text="' + relation + '"]');
                if (select.length) {
                    selects.push(select);
                }
                $(this).hide();
            });
            selects.forEach(function (el) {
                $(el).off().on('change', function (e) {
                    var relation = $(this).attr('name');
                    $('table[data-related-option="' + relation + '"]').hide();
                    var table = $('table[data-related-value="' + $(this).val() + '"]');
                    table.show();
                    table.addClass('showed-once');
                });
                $(el).trigger('change');
            })

            $('select').each(function () {
                var parent = $(this).parents('td.editable-cell').first(),
                    value = parent.attr('data-value');

                if (!$(this).find('option[value=""]').length) {
                    $(this).prepend('<option value=""></option>');
                    $(this).val(value || "");
                    $(this).trigger('change');
                }
            });
            setTimeout(function () {
                $('select').trigger('change')
            }, 500);
        },


        t: false,
        initResize: function () {
            var self = this;
            $("#form-builder-container table.editable-table").each(function () {
                $(this).find('tr').first().find('td:not(".dummy-cell")').resizable({
                    handles: "e",
                    stop: function (e, ui) {
                        $(e.target).attr('data-resized', true);
                        if (!$(e.target).find('a.reset-width').length) {
                            self.t = setTimeout(function () {
                                $(e.target).append('<a class="btn btn-warning btn-xs reset-width" style="display: none;float:right;"><i class="glyphicon glyphicon-resize-full"></i></a>');
                                $(e.target).stop().find('a.reset-width').show(100);
                            }, 100)
                        }
                    }
                });
            })
        },
        selectOptions: [],
        collectAvailableOptions: function () {
            var self = this;
            self.selectOptions = [];
            $('.editable-cell[data-type="options"]').each(function () {
                self.selectOptions.push($(this).find('select').html());
            });
            self.selectOptions = $.unique(self.selectOptions);
            return self.selectOptions;
        },
        setHandlers: function () {
            if (this._editable) {
                this._formContainer.find('canvas').each(function () {
                    new SignaturePad(this);
                });

                this._formContainer.find('td[data-type="date"] input').datepicker({
                    dateFormat: 'dd-mm-yy'
                });

                return false;
            }

            var self = this;
            $('.add-table').off().on('click', function () {
                $('#addTable').modal('show');
            });
            this._formContainer.off('click', '.editable-cell');
            this._formContainer.on('click', '.editable-cell', function (e) {
                $('.selected-cell').removeClass('selected-cell');
                $(this).addClass('selected-cell');
                $().colorPicker.destroy();
                $('#color').val('').removeAttr('style').colorPicker();
                $('#addField').modal('show');
                self.initSelect();
                self.fillCellForm($(this));
                self.refreshFieldForm($(this));
                self.fillAssignFields($(this));
                $('#options-settings').removeClass('visible');
                setTimeout(function () {
                    $('#placeholder-type').focus();
                    $('#options-preview').trigger('change');
                    $('select').selectpicker('refresh');
                }, 500);
            });
            this._formContainer.off('click', '.remove-table');
            this._formContainer.on('click', '.remove-table', function (e) {
                var self = this;
                if (confirm('Do you want to remove table?')) {
                    $(self).parent().remove();
                }
            });
            this._formContainer.off('click', '.config-table');
            this._formContainer.on('click', '.config-table', function (e) {
                var self = this,
                    dataStyle = $(this).parents('.table-container').find('table').attr('data-style'),
                    table = $(this).parent().find('table').first(),
                    relatedOption = table.attr('data-related-option'),
                    relatedValue = table.attr('data-related-value');

                $('.selected-table').removeClass('selected-table');
                table.addClass('selected-table');
                if (dataStyle) {
                    try {
                        dataStyle = JSON.parse(dataStyle);
                        $('#table-border').val(dataStyle.border);
                        $('#table-color').val(dataStyle.borderColor);
                        if (table.hasClass('not-bordered')) {
                            $('#cells-border').val('not-bordered');
                        }
                        else {
                            $('#cells-border').val('');
                        }
                    } catch (e) {
                        $('#table-border').val($('#table-border').find('option').first().val());
                        $('#table-color').val($('#table-color').find('option').first().val());
                        if (table.hasClass('not-bordered')) {
                            $('#cells-border').val('not-bordered');
                        }
                        else {
                            $('#cells-border').val('');
                        }
                    }

                } else {
                    $('#table-border').val($('#table-border').find('option').first().val());
                    $('#table-color').val($('#table-color').find('option').first().val());
                    if (table.hasClass('not-bordered')) {
                        $('#cells-border').val('not-bordered');
                    } else {
                        $('#cells-border').val('');
                    }
                }

                //fill related selects
                var options = {},
                    selects = ['<option value=""></option>'];
                $('table.editable-table:not(".selected-table") td.editable-cell').find('select').each(function () {

                    var value = $(this).attr('name'),
                        title = $(this).attr('data-text');

                    if (!title) return;

                    options[value] = $(this).find('option').clone();
                    selects.push('<option value="' + $(this).attr('name') + '">' + title + '</option>');

                });
                $('#related_option').html(selects.join(''));
                $('#related_option').off().on('change', function () {
                    var val = $(this).val();
                    $('#related_value').html(options[val] || '');
                    $('select').selectpicker('refresh');
                });

                $('#related_option').val(relatedOption);
                $('#related_option').trigger('change');
                $('#related_value').val(relatedValue);

                setTimeout(function () {
                    $('select').selectpicker('refresh');
                }, 500);
                $('#configTable').modal('show');
            });
            this._formContainer.off('click', '.remove-column');
            this._formContainer.on('click', '.remove-column', function (e) {
                var self = this,
                    index,
                    i,
                    table,
                    cols;
                if (confirm('Do you want to remove column?')) {
                    table = $(this).parents('table').first();
                    index = $(this).attr('data-c');

                    table.find('tr').each(function () {
                        cols = $(this).find('td');
                        if (cols[index]) {
                            cols[index].remove();
                        }
                    });
                    table.find('[data-c]').each(function () {
                        $(this).attr('data-c', $(this).attr('data-c') - 1);
                    });

                }
            });
            this._formContainer.off('click', '.remove-row');
            this._formContainer.on('click', '.remove-row', function (e) {
                var self = this;
                if (confirm('Do you want to remove row?')) {
                    $(self).parents('tr').first().remove();
                }
            });


            $('.confirm-insert-field').off().on('click', function () {
                $('#addField').modal('hide');
            });
            $('#fieldType').off().on('change', function () {
                self.refreshFieldForm();
            });
            $('#add-option').off().on('click', function () {
                var value = $('#option-type-value').val();
                $('#options-preview').append('<option value="' + value + '">' + value + '</option>');
                $('#option-type-value').val('');
                $('select').selectpicker('refresh');
            });
            $('#remove-option').off().on('click', function () {
                var value = $('#options-preview').val();
                $("#options-preview option[value='" + value + "']").remove();
                $('#option-color').val('');
                $('select').selectpicker('refresh');
            });
            $('#option-color').off().on('change', function (e) {
                $('#options-preview').find('option[value="' + $('#options-preview').val() + '"]').attr('data-color', $(this).val());
            });

            $('#options-preview').off().on('change', function () {
                var color = $(this).find('option[value="' + $(this).val() + '"]').attr('data-color');
                $('#option-color').val(color);
            });

            $('#color').colorPicker();

            $('#confirm-insert-field').off().on('click', self.confirmAddField);

            $('.confirm-insert-table').off().on('click', function () {
                var cols = $('#cols-number').val(),
                    rows = $('#rows-number').val(),
                    html = [],
                    i, j;
                html.push('<div class="table-container"><i class="glyphicon glyphicon-move"></i><button class="btn btn-danger remove-table btn-xs"><i class="glyphicon glyphicon-trash"></i></button><button class="btn btn-info config-table btn-xs"><i class="glyphicon glyphicon-cog"></i></button><table class="table-responsive table table-bordered editable-table"><tbody>');
                html.push('<tr class="tmp-cell">');
                html.push('<td class="tmp-cell  dummy-cell"></td>');
                for (j = 0; j < cols; j++) {
                    html.push('<td class="tmp-cell" style="width:auto;"><button class="btn btn-danger btn-xs remove-column" data-c="', j + 1, '"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-down"></span></button></td>');
                }
                html.push('</tr>');
                for (i = 0; i < rows; i++) {
                    html.push('<tr>');
                    html.push('<td class="tmp-cell"><button class="btn btn-danger btn-xs remove-row"><span class="glyphicon glyphicon-trash"></span><span class="glyphicon glyphicon-arrow-right"></span></button></td>');
                    for (j = 0; j < cols; j++) {
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
            $('.confirm-table-settings').off().on('click', function () {
                var table = $('.selected-table'),
                    borderVal = $('#table-border').val() || '0',
                    borderColor = $('#table-color').val() || '#ccc',
                    style = "border:" + borderVal + "px solid " + borderColor,
                    className = $('#cells-border').val() || '',
                    relatedOption = $('#related_option').val() || '',
                    relatedValue = $('#related_value').val() || '';

                table
                    .attr('style', style)
                    .attr('data-style', JSON.stringify({border: borderVal, borderColor: borderColor}))
                    .attr('data-class', className)
                    .attr('data-related-option', relatedOption)
                    .attr('data-related-value', relatedValue)
                ;

                table.removeClass('not-bordered').addClass(className);

                table.removeClass('selected-table');
                $('#configTable').modal('hide');
            });

            $('.add-row').off().on('click', function () {
                var table = $('.selected-table'),
                    row = table.find('tr').last().clone();
                row.find('td:not(".tmp-cell")').each(function () {
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

            $('.add-column').off().on('click', function () {
                var table = $('.selected-table'),
                    firstTd = table.find('tr').first().find('td').last().clone();

                table.find('tr').first().append(firstTd);
                table.find('tr:not(".tmp-cell")').each(function () {
                    $(this).append('<td class="editable-cell"></td>');
                });

                table.removeClass('selected-table');
                $('#configTable').modal('hide');
                self.initResize();
            });


            $('#form-save').off().on('click', function () {
                if (confirm('Save form and close the editor?')) {
                    self.sendForm();
                }
            });

            $('#form-report').off().on('change', function () {
                self.getReports();
            });

            $('#form-builder-container').on('click', 'a.reset-width', function () {
                $(this).parent().attr('style', 'width:auto;').removeAttr('data-resized');
                $(this).remove();
            });

            $('.close-options').off().on('click', function () {
                $('#options-settings').removeClass('visible');
            });
            $('.apply-options').off().on('click', function () {
                var selected = $('[name="option-item-list"]:checked'),
                    idx,
                    html;
                if (selected.length) {
                    idx = selected.attr('data-idx');
                    html =
                        '<select>' +
                        $('.available-select[data-idx="' + idx + '"]').html() +
                        '</select>';

                    $('#options-preview').html(html).selectpicker('refresh');
                    $('#options-settings').removeClass('visible');
                }

            });


            $('[data-toggle="tooltip"]').tooltip();
            $('[title]').tooltip();

            $('#show-options-settings').off().on('click', function () {
                var options = self.collectAvailableOptions(),
                    i,
                    idx = 0,
                    items = [];

                items.push('<h4>Global:</h4>');
                $.ajax({
                    async: false,
                    url: utils.baseUrl() + 'json/enums',
                    success: function(data) {
                        for (var i in data) {
                            items.push(
                                '<div>',
                                '<input type="radio" name="option-item-list"',
                                'data-idx="',
                                ++idx,
                                '"',
                                '/>',
                                data[i].name,
                                ': <select class="available-select"',
                                'data-idx="',
                                idx,
                                '"',
                                '>'
                            );
                            for (var j in data[i].values)
                                items.push('<option value="' + data[i].values[j] + '">' + data[i].values[j] + '</option>');

                            items.push(
                                '</select>',
                                '</div>'
                            );

                        }
                    }
                });
                items.push('<h4>Local:</h4>');
                if (options.length) {
                    for (i in options) {
                        items.push(
                            '<div>',
                            '<input type="radio" name="option-item-list"',
                            'data-idx="',
                            ++idx,
                            '"',
                            '/>',
                            '<select class="available-select"',
                            'data-idx="',
                            idx,
                            '"',
                            '>',
                            options[i],
                            '</select>',
                            '</div>'
                        );
                    }

                } else {
                    items.push('<h5>No options are available</h5>');
                }

                $('#predifined-options').html(items.join(''));

                $('#options-settings').addClass('visible');
            });
        }
    }
})(window);

$(function () {
    $('#form-type').on('change', function (e) {
        if ($(this).val() != 1) {
            $('td[data-type="ticket"]').html('').attr('data-type', 'label');
            $('#fieldType').find('option[value="ticket"]').attr('disabled', 'disabled');
        } else {
            $('#fieldType').find('option[value="ticket"]').removeAttr('disabled');
        }
        if ($(this).val() == 3) {
            $('#new-cell-details').addClass('unattached-container');
            $('#form-save').attr('is_unattached', true);
        } else {
            $('#new-cell-details').removeClass('unattached-container');
            $('#form-save').removeAttr('is_unattached');
        }
    });

    $('#hide-form').on('click', function () {
        $('#new-form').show();
        $('#hide-form').hide();
        $('#forms-list').show();
        $('#form-builder').addClass('hidden');
        $('#form-builder-container').html('');

    });
    $('.form-edit-link').click(function () {
        var id = $(this).attr('data-id');
        $('#forms-list').hide();
        $('#new-form').hide();
        $('#hide-form').show();

        if (id == undefined) {
            $('#form-builder').attr('data-id', '');
            $('#form-name').val('');
            $('#form-type').val('');
            $('#form-report').val('');
            formbuilder.initForm('#form-builder-container');
            $('#form-builder').removeClass('hidden');
        } else {
            $.get(utils.baseUrl() + 'form/load?id=' + id, function (data) {
                $('#form-builder').attr('data-id', id);
                $('#form-name').val(data.name);
                $('#form-type').val(data.type);
                $('#form-report').val(data.report);
                $('#allow-geo').prop('checked', data.geo);
                $('#allow-attachment').prop('checked', data.attachment);
                formbuilder.initForm('#form-builder-container', data.data);
                $('#form-builder').removeClass('hidden');
                formbuilder.initResize();
            });
        }
    });

    var files = [];

    $('.form-save').click(function () {
        if (!formbuilder.checkRequiredFields()) {
            alert('Fill out the mandatory fields before saving the form');
            return false;
        }

        var print = $(this).hasClass('btn-info');

        if (print && !confirm('Do you really want to convert this file to PDF? After this, form data can\'t  be edited!'))
            return false;

        var form = $(this).parents('form');
        form.find('table:not(:visible)').remove();
        form = form.serializeArray();

        $('form').find('canvas').each(function () {
            form.push({
                name: $(this).next('input').attr('name'),
                value: $(this).get(0).toDataURL()
            });
        });

        $.ajax({
            url: '',
            type: 'POST',
            data: form,
            success: function (data) {
                $('#form-data').attr('data-id', data.id);
                if (files.length > 0) {
                    $('#file-queue').find('a').remove();
                    $('#file-queue>li').first().addClass('text-info');
                    var file = files.shift();

                    $('#form-upload').attr('data-redirect', print ? '' : data.url);

                    $('#form-upload').fileupload("send", {
                        files: [file],
                        url: utils.baseUrl() + 'form/upload/' + $('#form-data').attr('data-id')
                    });
                } else {
                    if (print) {
                        var id = $('#form-data').attr('data-id');
                        $.get(utils.baseUrl() + 'form/fill?print&id=' + id, function (data) {
                            window.location = data.url;
                        });
                    } else {
                        window.location = data.url;
                    }
                }
            },
            error: function (e) {
                $('html').html(e.responseText);
            }
        });
    });

    $('#form-data').submit(function () {
        return false;
    });

    $('#form-upload').fileupload({
        autoUpload: false,
        dataType: 'json',
        add: function (e, data) {
            for (var i in data.files) {
                files.push(data.files[i]);
                $('#file-queue').append('<li><a href="javascript:;" class="text-danger remove-file"><span class="glyphicon glyphicon-remove"></span></a> ' + data.files[i].name + ' (' + bytesToSize(data.files[i].size) + ')</li>')
            }
        },
        done: function (e, data) {
            $('#file-queue>li').first().remove();
            if (files.length > 0) {
                var file = files.shift();
                $('#form-upload').fileupload("send", {
                    files: [file],
                    url: utils.baseUrl() + 'form/upload/' + $('#form-data').attr('data-id')
                });
            } else {
                var url = $('#form-upload').attr('data-redirect');
                if (url == '') {
                    var id = $('#form-data').attr('data-id');
                    $.get(utils.baseUrl() + 'form/fill?print&id=' + id, function (data) {
                        window.location = data.url;
                    });
                } else {
                    window.location = url;
                }
            }
        },
        fail: function (e, data) {
            $('html').html(data.jqXHR.responseText);
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    $('#file-queue').on('click', '.remove-file', function () {
        var id = $(this).parent().prevAll('li').length;
        var f = [];
        for (var i in files)
            if (i != id) f.push(files[i]);

        files = f;

        $(this).parent().remove();
    });

    $('#attachments').on('click', '.remove-attachment', function (e) {
        if (!confirm('Do you really want to remove this attachment?')) return;
        var id = $('#form-data').attr('data-id');
        var image = $(this).parent().attr('data-id');
        $.get(utils.baseUrl() + 'form/remove/' + id + '?id=' + image, function () {
            $('div[data-id="' + image + '"]').remove();
        });
    });

});