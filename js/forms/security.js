$(function () {
    $('#generate-report').on('click',function(e){
        $('#table-name').val('');
        $('#configTable').modal('show');
        $('#table-id').val('');
        $('#table-header').html('');
        $('#configTable').attr('isnew','true');
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
                type: $(this).attr('data-type')
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
                if($('#configTable').attr('isnew')){
                    $('#configTable').removeAttr('isnew');
                    $('#table-list').append('<li class="edit-table-item" href="#" data-id="'+data.id+'">'+name+'</li>');
                }

                $('.edit-table-item[data-id="'+id+'"]').text(name);
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
        if(!$(this).hasClass('active')){
            $(this).addClass('active');
            var text = $(this).text(),
                options = $('#cell-type').html(),
                self = this,
                select,
                input,
                button;
            $(this).html('<input type="text" value="'+text+'" class="editable-input"><select>'+options+'</select><a class="btn btn-success">Save</a>');
            input = $(this).find('input');
            input.focus();
            select = $(this).find('select');
            select.val($(this).attr('data-type'));
            button = $(this).find('a');
            button.off().on('click',function(e){
                e.preventDefault();
                $(self).attr('data-type',select.val());
                $(self).removeClass('active');
                $(self).html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'+input.val());
                setSortable();
            })
        }

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
                        '"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>',
                        data.data[i].name,
                        '</th></tr>'
                    )
                }
                $('#table-header').html(html.join(''));
                $('#table-id').val(data.id);
                $('#table-name').val(data.name);
                setSortable();
                $('#configTable').modal('show');


            }
        });
    });

    function setSortable(){
        $('#table-header tbody').sortable({
            items: "tr",
            placeholder: "ui-state-highlight"
        });
    }


});