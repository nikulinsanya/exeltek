$(function () {
    $('#generate-report').on('click',function(e){
        $('#table-name').val('');
        $('#configTable').modal('show');
        $('#table-id').val('');
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
            data.data[utils.guid()] = $(this).text();
        });

        $.ajax({
            url: utils.baseUrl() +'security/reports/update',
            type:'POST',
            data:data,
            dataType:'JSON',
            success:function(data){
                $('#table-cell').val('');
                $('#table-header').html('');
                $('#table-list').append('<li><a class="edit-table-item" href="#" data-id="'+data.id+'">'+name+'</a></li>')
                $('#configTable').modal('hide');
            },
            error:function(){
                alert('Server error.');
            }
        });


    });
    $('#add-cell').on('click',function(e){
        var html = [];
        html.push(
            '<th class="editable-cell">',
            $('#table-cell').val(),
            '</th>'
        );
        $('#table-header').append(html.join(''));
        $('#table-cell').val('');
    });

    $('#table-preview').on('click','.editable-cell',function(e){
        e.preventDefault();
        var text = $(this).text();
        $(this).html('<input type="text" value="'+text+'" class="editable-input">');
        $(this).find('input').focus();

    });

    $('#table-preview').on('blur','.editable-input',function(e){
        e.preventDefault();
        var parent = $(this).parent();
        parent.html($(this).val());
    });

    $('#table-list').on('click','.edit-table-item',function(){
        var id = $(this).attr('data-id');

        $.ajax({
            type:'get',
            url: utils.baseUrl() + 'security/reports/load?id='+id,
            dataType:'json',
            success:function(data){
                console.log(data);

                $('#table-id').val(data.id);
                $('#table-name').val(data.name);
                $('#configTable').modal('show');
            }
        });
    });


});