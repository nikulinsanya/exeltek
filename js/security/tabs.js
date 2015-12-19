$(function () {
    $('#tabEditor').on('click','.edit-tab', function(){
        var id = $(this).attr('data-id'),
            name = id ? $('#tabList').find('span[data-target="'+id+'"]').text() : '';
        $('#tabName').val(name);
        $('#tabId').val(id);
        $('#editTab').modal('show');
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
                    '">Edit</button></td><td><span data-target="',
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
        }

        $('#tabName').val(name);
        $('#tabId').val(id);
        $('#editColumn').modal('show');
    });

    $('#updateColumn').on('click',function(){
        $('#column-form').find('.column-field').each(function(){
            var id = $(this).attr('id'),
                columnId = $('#columnId').val(),
                row = $('#tabsTable').find('tr[data-id="'+columnId+'"]'),
                val;
            if($(this).prop("tagName") == 'INPUT' && $(this).attr('type') == 'checkbox'){
                val = boolIcon($(this).is(':checked'));
                row.find('td.'+id).html(val);
            }else if($(this).prop("tagName") == 'SELECT'){
                if($('#enum-type').is(':visible')){
                    val = 'Enum('+ $('#enum-type').find('option:selected').text()+')';
                    row.find('td.column-type').text(val);
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


        updateColumn();

        $('#editColumn').modal('hide');
    });

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
                csv: $('#column-export').is(':checked'),
                show_reports: $('#column-report').is(':checked'),
                direct: $('#column-direct').is(':checked'),
                track: $('#column-track').is(':checked'),
                persistent: $('#column-persistent').is(':checked'),
                editable: $('#column-editable').is(':checked'),
                read_only: $('#column-readonly').is(':checked')
            };

        return $.ajax({
            url:utils.baseUrl()+'security/structure/save/' + $('#columnId').val(),
            type:'POST',
            data:data,
            dataType:'JSON',
            error: function(e) {
                console.log(e);
            }
        });
    }
});