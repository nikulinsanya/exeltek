$(function () {
    $('#generate-report').on('click',function(e){
        $('#table-name').val('');
        $('#configTable').modal('show');
        $('#table-id').val('');
        $('#table-header').html('');
    });

    $('#save-form').on('click',function(e){
        var id = $('#table-id').val(),
            name = $('#table-name').val();

        if(!name){
            alert('Table name is empty.');
            return false;
        }

        var data = {id: id, name: name, data: {} };

        $('#table-header th').each(function(){
            data.data[$(this).attr('data-guid') || utils.guid()] = {
                name: $(this).text(),
                type: $(this).attr('data-type'),
            };
        });

        $.ajax({
            url: utils.baseUrl() +'security/reports/update',
            type:'POST',
            data:data,
            dataType:'JSON',
            success:function(data){
                $('#table-cell').val('');
                $('#table-header').html('');
                $('#table-list').append('<li class="edit-table-item" href="#" data-id="'+data.id+'">'+name+'</li>');
                $('#configTable').modal('hide');
            },
            error: function(data){
                $('html').html(data.responseText);
            },
            fail:function(data){
                $('html').html(data);
                //alert('Server error.');
            }
        });


    });
    $('#add-cell').on('click',function(e){
        var html = [];
        html.push(
            '<tr><th class="editable-cell" data-type="',
            $('#cell-type').val(),
            '">',
            $('#table-cell').val(),
            '</th></tr>'
        );
        $('#table-header').append(html.join(''));
        $('#table-cell').val('');
    });

    $('#table-header').on('click','.editable-cell',function(e){
        e.preventDefault();
        var text = $(this).text();
        $(this).html('<input type="text" value="'+text+'" class="editable-input">');
        $(this).find('input').focus();

    });

    $('#table-header').on('blur','.editable-input',function(e){
        e.preventDefault();
        var parent = $(this).parent();
        parent.html($(this).val());
    });

    $('#table-list').on('click','.edit-table-item',function(){
        var id = $(this).attr('data-id'),
            html = [],
            guid;

        $.ajax({
            type:'get',
            url: utils.baseUrl() + 'security/reports/load?id='+id,
            dataType:'json',
            success:function(data){
                for(var i in data.data){
                    html.push(
                        '<tr><th class="editable-cell" data-guid="',
                        i,
                        '" data-type="',
                        data.data[i].type,
                        '">',
                        data.data[i].name,
                        '</th></tr>'
                    )
                }
                $('#table-header').html(html.join(''));
                $('#table-id').val(data.id);
                $('#table-name').val(data.name);
                $('#configTable').modal('show');
            }
        });
    });


});