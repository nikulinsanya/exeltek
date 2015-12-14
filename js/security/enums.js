$(function () {
    $('#enumsTable').on('click','.edit-enum', function(){
        var id = +($(this).attr('data-id')),
            self = this;
        if(id){
            getEnumById(id).then(function(enumItem){
                var name = $(self).parents('tr').find('.enumName').text(),
                    isMulti = enumItem.multi,
                    items = enumItem.items,
                    options = [],
                    i;
                $('#enumName').val(name);
                isMulti ?
                    $('#isMulti').attr('checked','checked'):
                    $('#isMulti').removeAttr('checked');
                for(i in items){
                    options.push(['<option value="',items[i],'">',items[i],'</option>'].join(''));
                }
                $('#optionsPreview').html(options.join(''));
                $('#enumId').val(id);
                $('#editEnum').modal('show');
            })
        }else{
            $('#enumName').val('');
            $('#isMulti').removeAttr('checked');
            $('#optionsPreview').html('');
            $('#enumId').val('');
            $('#editEnum').modal('show');
        }
    });

    $('#enumsTable').on('click','.remove-enum', function(){
        var id = $(this).attr('data-id'),
            self = $(this);
        if(confirm('Are you sure?')) {
            removeEnum(id);
            self.parents('tr').first().remove();
        };
    });

    $('#add-option').on('click',function(){
        var value = $('#option-type-value').val();
        $('#optionsPreview').append('<option value="'+value+'">'+value+'</option>');
        $('#option-type-value').val('');
    });
    $('#remove-option').on('click',function(){
        var value = $('#optionsPreview').val();
        $("#optionsPreview option[value='"+value+"']").remove();
        $('#option-color').val('');
    });
    $('#updateEnum').on('click',function(){
        var id   = $('#enumId').val(),
            data =  {
                multi:$('#isMulti').is(':checked'),
                items:$('#optionsPreview').find('option').map(function(){
                    return $(this).text();
                }).toArray(),
                name: $('#enumName').val()
            };
        updateEnum(id,data).then(function(res){
            if(res.id){
                editEnumTable(res.id,data.name, data.multi);
                $('#editEnum').modal('hide');
            }
        })
    });
    function editEnumTable(id,name,isMulti){
        var tr = $('#enumsTable').find('tr[data-id="'+id+'"]');
        if(tr.length){
            $(tr).find('.enumName').text(name);
            $(tr).find('.isMulti').html(
                '<span class="glyphicon glyphicon-'+(isMulti ? 'ok text-success' : 'remove text-danger')+'></span>'
            );
        }else{
            $('#enumsTable tr:first-of-type').after(
                ['<tr data-id="',
                    id,
                    '">',
                    '<td><button title="Edit Enum" class="btn btn-warning edit-enum" data-id="',
                    id,
                    '"><span class="glyphicon glyphicon-pencil"></span></button><button title="Remove Enum" class="btn btn-danger remove-enum" data-id="',
                    id,
                    '"><span class="glyphicon glyphicon-trash"></span></button></td>',
                    '<td class="enumName">',
                    name,
                    '</td>',
                    '<td class="isMulti"><span class="glyphicon glyphicon-',
                    isMulti ? 'ok text-success' : 'remove text-danger',
                    '"></span></td>',
                    '</tr>'
                ].join('')
            );
        }
    }

    function removeEnum(id){
        return $.ajax({
            url:utils.baseUrl()+'security/enums/delete/'+id,
            type:'get',
            dataType:'JSON'
        });
    }
    function getEnumById(id){
        return $.ajax({
            url:utils.baseUrl()+'security/enums/load/'+id,
            type:'get',
            dataType:'JSON'
        });
    }
    function updateEnum(id,data){
        id = id && +id  || '';
        return $.ajax({
            url:utils.baseUrl()+'security/enums/save/' + id,
            type:'POST',
            dataType:'JSON',
            data:data
        });
    }
});