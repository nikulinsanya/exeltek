$(function () {
    $('#tabEditor').on('click','.edit-tab', function(){
        var id = $(this).attr('data-id'),
            name = id ? $('#tabList').find('span[data-target="'+id+'"]').text() : '';
        $('#tabName').val(name);
        $('#tabId').val(id);
        $('#editTab').modal('show');
    });
    $('#tabEditor').on('click','.remove-tab', function(){
        var self = this;
        if(confirm('Are you sure you want to remove tab?')){
            removeTab($(this).attr('data-id')).then(function(){
                $(self).parents('tr').remove();
            });
        }
    });

    $('#updateTab').on('click',function(){
        var id = $('#tabId').val(),
            name = $('#tabName').val();

        updateTab(id,name).then(function(data){
            if(id) {
                $('#tabList').find('span[data-target="' + $('#tabId').val() + '"]').text($('#tabName').val());
            }else{
                $('#tabList').append(['<tr><td><button class="btn btn-xs btn-warning tab-item edit-tab" data-id="',
                    data.id,
                    '"><span class="glyphicon glyphicon-pencil"></span></button><button class="btn btn-xs btn-danger tab-item remove-tab" data-id="',
                    data.id,
                    '"><span class="glyphicon glyphicon-trash"></span></button></td><td><span data-target="',
                    data.id,
                    '">',
                    name,
                    '</span></td></tr>'].join(''));
            }
            $('#editTab').modal('hide');
        });
    });



    $('#column-type').on('change',function(){
        checkEnum($(this).val());
    });

    $('#tabEditor').on('click','.edit-column', function(){
        if(!$(this).attr('data-id')){
            $('#column-form-data')[0].reset();
        }
        var id = $(this).attr('data-id'),
            parent = $(this).parents('tr[data-id]').first(),
            tabName = (parent ? parent.find('.tab-name').text() : ''),
            columnType = (parent ? parent.find('.column-type').attr('data-type') : ''),
            tabsOptions = $('span[data-target]').map(function(){
                return ['<option value="',$(this).attr('data-target'),'" ',
                    (tabName == $(this).text() ? 'selected': '')
                    ,'>',$(this).text(),'</option>'].join('');
            }).toArray(),
            name = parent ? parent.find('.column-name').text() : '';
        $('#columnId').val(id);


        if(parent.length){
            parent.find('td').each(function(){
                var className = $(this).attr('class');
                if(!className){
                    return;
                }
                switch(className){
                    case 'tab-name':
                        $('#column-tab').html(tabsOptions.join(''));
                        break;
                    case 'column-name':
                        $('#column-name').val(name);
                        break;
                    case 'column-type':
                        $('#column-type').val(columnType);
                        checkEnum(columnType);
                        $('#enum-type').val($(this).attr('data-id'));
                        break;
                    case 'column-financial':
                        $('#column-financial').val($(this).text());
                        break;
                    default:
                        $(this).attr('data-val') ?
                            $('#'+className).attr('checked', 'checked'):
                            $('#'+className).removeAttr('checked');
                        break;
                }


            })
        }else{
            $('#column-tab').html(tabsOptions.join(''));
            $('#column-type').val('');
            checkEnum();
        }

        $('#tabName').val(name);
        $('#tabId').val(id);

        $('#editColumn').modal('show');
    });

    $('#updateColumn').on('click',function(){
        var columnId = $('#columnId').val(),
            row = columnId ?
                $('#tabsTable').find('tr[data-id="'+columnId+'"]'):
                $('#tabsTable').find('tr[data-id]').first().clone();

        $('#column-form').find('.column-field').each(function(){
            var id = $(this).attr('id'),
                val;
            if($(this).prop("tagName") == 'INPUT' && $(this).attr('type') == 'checkbox'){
                val = boolIcon($(this).is(':checked'));
                row.find('td.'+id).html(val);
            }else if($(this).prop("tagName") == 'SELECT'){
                if($('#enum-type').is(':visible')){
                    val = 'Enum('+ $('#enum-type').find('option:selected').text()+')';
                    row.find('td.column-type').text(val).attr('data-id', $('#enum-type').val());
                }else if($(this).attr('id') == 'column-tab'){
                    val = $(this).find('option:selected').text();
                    row.find('td.tab-name').text(val);
                }else{
                    val = getTypeLabel($(this).val());
                    row.find('td.' + id).text(val);
                }
            }else {
                val = $(this).val();
                row.find('td.' + id).text(val);
            }
        });
        if(!columnId){
            $('#tabsTable').append(row);
            $('html, body').animate({scrollTop: $('#tabsTable').find('tr').last().offset().top-55}, 500);
        }


        updateColumn().then(function(data){
            row.find('button.edit-column').attr('data-id', data.id).parents('tr').first().attr('data-id', data.id);
        });

        $('#editColumn').modal('hide');
    });

    $('#tabEditor').on('click','.remove-column', function(){
        var self = this;
        if(confirm('Are you sure you want to remove column?')){
            removeColumn($(this).attr('data-id')).then(function(){
                $(self).parents('tr').remove();
            });
        }
    });


    function removeTab(id){
        return $.ajax({
            url:utils.baseUrl()+'security/structure/tab/remove' + id,
            type:'GET',
            dataType:'JSON',
            error: function(e) {
                console.log(e);
            }
        });
    }

    function removeColumn(id){
        return $.ajax({
            url:utils.baseUrl()+'security/structure/tab/remove' + id,
            type:'GET',
            dataType:'JSON',
            error: function(e) {
                console.log(e);
            }
        });
    }


    function getTypeLabel(val){
        var labels = {
            '': 'Default(string)',
            'text': 'Text',
            'date': 'Date',
            'datetime':'DateTime',
            'float' : 'Floating-point',
            'integer': 'Number',
            'enum': 'Enum'
        }
    }


    function checkEnum(value){
        value == 'enum' ?
            $('.enum-field-visible').show():
            $('.enum-field-visible').hide()

    }
    checkEnum();

    function boolIcon(value) {
        return '<span class="glyphicon glyphicon-' + (value ? 'ok' : 'remove') + ' text-' + (value ? 'success' : 'danger') + '"></span>';
    }

    function updateTab(id,name){
        return $.ajax({
            url:utils.baseUrl()+'security/structure/tab/' + id,
            type:'POST',
            data:{name:name},
            dataType:'JSON',
            error: function(e) {
                console.log(e);
            }
        });
    }
    function updateColumn(){
        var form = $('#column-form-data'),
            data = {
                name: $('#column-name').val(),
                type: $('#enum-type').is(':visible') ?
                    ('enum-'+ $('#enum-type').val()) :
                    $('#column-type').val(),
                tab_id: $('#column-tab').val(),
                financial: $('#column-financial').val(),
                csv: $('#column-export').is(':checked') ? 1 : 0,
                show_reports: $('#column-report').is(':checked') ? 1 : 0,
                direct: $('#column-direct').is(':checked') ? 1 : 0,
                track: $('#column-track').is(':checked') ? 1 : 0,
                persistent: $('#column-persistent').is(':checked') ? 1 : 0,
                editable: $('#column-editable').is(':checked') ? 1 : 0,
                read_only: $('#column-readonly').is(':checked') ? 1 : 0,
            };

        return $.ajax({
            url:utils.baseUrl()+'security/structure/save/' + $('#columnId').val(),
            type:'POST',
            data:data,
            dataType:'JSON',
            error: function(e) {
                $('body').html(e.responseText);
            }
        });
    }
});