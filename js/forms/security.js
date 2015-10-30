$(function () {
    $('#generate-report').on('click',function(e){
        $('#configTable').modal('show');
    });

    $('#save-form').on('click',function(e){
        var data=[],
            id = false,
            guid = utils.guid(),
            obj,
            name = $('#table-name').val();
        if(!name){
            alert('Table name is empty.');
            return false;
        }
        $('#table-header th').each(function(){
            obj = {};
            obj[guid] = $(this).text();
            data.push(obj);
        });

        $.ajax({
            url: utils.baseUrl() +'security/reports/update?'+(id ? 'id='+id+'&' : '')+'name='+name,
            type:'POST',
            data:data,
            success:function(){
                $('#table-cell').val('');
                $('#table-header').html('');
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


});