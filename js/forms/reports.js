$(function () {
    $('#form-reports').on('change',function(e){
        var id = $(this).val();
        if (id)
            $.post(utils.baseUrl() + 'reports/forms', {id: $(this).val()}, function(data) {
                window.location = utils.baseUrl() + 'reports/forms?id=' + data.id;
            });
        else
            window.location = utils.baseUrl() + 'reports/forms';
    });


    $('#reports').on('mousedown','.editable-form-cell:not(".in_edit_mode")',function(e){
        e.preventDefault();
        if(e.target.nodeName != 'TD'){
            return false;
        }
        var self  = this,
            value = $(self).text().trim(),
            type  = $(self).attr('data-type'),
            id    = $(self).attr('data-guid'),
            report = $(self).parents('tr').attr('data-id'),
            input;
        switch (type) {
            case 'float':
                input = $('<input type="number" step="0.01" class="tmp-edit-input" value="' + value + '"/>')
                break;
            case 'int':
            case 'number':
                input = $('<input type="number" class="tmp-edit-input" value="' + value + '"/>')
                break;
            case 'datetime':
                input = $('<input type="text" class="datetimepicker tmp-edit-input" value="' + value + '"/>')
                break;
            case 'date':
                input = $('<input type="text" class="datepicker tmp-edit-input" value="' + value + '"/>')
                break;

            default:
                input = $('<input type="text" class="tmp-edit-input" value="' + value + '"/>')
                break;
        }
        $(self).addClass('in_edit_mode');

        $(self).html(input);

        $(self).find('input.datepicker').datetimepicker({
            format: 'DD-MM-YYYY'
        });
        $(self).find('.datetimepicker').datetimepicker({
            format: 'DD-MM-YYYY HH:mm'
        });

        $(input).trigger('focus').on('blur', function(e) {
            $(self).html($(this).val());
            $(self).removeClass('in_edit_mode');
            updateTebleOnFly(report,id,$(this).val());
        })

    });

    $('#reports').on('click','.apply-filter',function(e){
        e.preventDefault();
        collectFilters();
    });
    $('#reports').on('click','.filter-clear',function(e){
        e.preventDefault();
        $(this).parents('th').find('input[type="text"]').val('');
        collectFilters();
    });

    $('#reports').on('click','.dropdown-toggle',function(e){
        e.preventDefault();
        $('.dropdown-menu.collapse.in').removeClass('in');
    });

    $('.filter-info-container').on('click','.clear-all',function(e){
        e.preventDefault();
        $('tr.table-header').find('input[type="text"]').val('');
        collectFilters();
    });

    $('#applyModalFilters').on('click',function(e) {
        $('ul[data-guid]').each(function(){
            var guid = $(this).attr('data-guid');
            $('ul[data-parent-guid="'+guid+'"]').html($(this).html());
        });
        collectFilters();
    });

    $('.add-items-to-request').on('click',  function(e){
        e.preventDefault();
        var url = $(this).attr('href'),
            items = $('.select-reports[data-id]:checked'),
            ids = items.map(function(){return $(this).attr('data-id')}).toArray();
        url += '&ids=' + ids.join(',');
        OpenInNewTab(url);
    });

    $('#reports-remove').click(function(e) {
        e.preventDefault();
        var url = utils.baseUrl() + 'reports/forms/remove',
            items = $('.select-reports[data-id]:checked'),
            ids = items.map(function(){return $(this).attr('data-id')}).toArray();
        var data = {
            id: $('#form-reports').val(),
            ids: ids.join(',')
        };
        if (prompt('To confirm please type in "delete" and hit ok') !== 'delete') return false;
        $.ajax({
            url: utils.baseUrl() + 'reports/forms/remove',
            type:'POST',
            data: data,
            dataType:'JSON',
            success:function(data){
                items.each(function(i, e) {
                    $(e).parents('tr').remove();
                });
                //alert(dump(data, -1));
            },
            error:function(data){
                $('html').html(data.responseText);
            }
        });
    });

    function OpenInNewTab(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    (function addNecessaryHtml(){
        $('.table-header').on('mousedown','th',function(e){
            if(e.target.nodeName == 'A')
                $('.dropdown-menu.collapse.in').removeClass('in');
        });
        collectFilters(true);
    })();

    function updateTebleOnFly(report, id, value){
        data = {
            id: report,
            key: id,
            value: value
        };
        $.ajax({
            url: utils.baseUrl() + 'reports/forms/update',
            type:'POST',
            data: data,
            dataType:'JSON',
            success:function(data){
                //alert(dump(data, -1));
            },
            error:function(data){
                $('html').html(data.responseText);
            }
        });
    }



    function collectFilters(updateOnly){
        var data = {},
            obj,
            key,
            parent = $('.table-header'),
            extendedList = {};

        parent.find('th[data-type]').each(function(){
            var th = $(this);
            obj = {};
            switch (th.attr('data-type')){
                case 'float':
                case 'int':
                case 'number':
                case 'date':
                case 'datetime':
                    key = th.attr('data-guid');
                    var from = th.find('.from').val();
                    if (from){
                        obj.from = from;
                        extendedList[th.find('a').text().replace(/[^\w\s]/gi, '') + ' > '] = from;
                    }
                    var to = th.find('.to').val();
                    if (to){
                        obj.to = to;
                        extendedList[th.find('a').text().replace(/[^\w\s]/gi, '') + ' < '] = to;
                    }
                    break;
                default:
                    key = th.attr('data-guid');
                    var value = th.find('.text').val();
                    if (value){
                        obj.value = value;
                        extendedList[th.find('a').text().replace(/[^\w\s]/gi, '') + ' contain '] = value.replace(/\|/g,' <b>or</b> ');
                    }
                    break;
            }

            data[key] = obj;

        });
        displayFilters(extendedList);
        if(!updateOnly) {
            loadItems(data);
        }
    }

    function displayFilters(data){
        var container = $('#filter-list'),
            i,
            html = [];
        if(Object.keys(data).length){
            html.push('<ul>');
            for(i in data){
                html.push(
                    '<li>',
                    '<b>',
                    i,
                    '</b>',
                    data[i],
                    '</li>')
            }
            html.push('</ul>');
        }
        container.html(html.join(''));

    }

    function loadItems(data){
        data.id = $('#form-reports').val();
        return $.ajax({
            url: utils.baseUrl() +'reports/forms',
            type:'POST',
            data: data === undefined ? '' : data,
            dataType:'JSON',
            success:function(data){
                window.location = utils.baseUrl() + 'reports/forms?id=' + data.id;
            },
            error: function(data){
                alert('Nothing found');
            }
        });
    }

    function ticket_id_unfocus() {
        var target = $(this).next();
        var separator = target.attr('data-separator');

        $(this).remove();
        if(!separator){
            return false;
        }
        var val = $(this).val()
            .replace(/\n/g, separator);

        while (val.indexOf(separator + separator) !== -1)
            val = val.replace(separator + separator, separator);

        if (val.indexOf(separator) === 0)
            val = val.substring(separator.length);

        if (val.lastIndexOf(separator) === val.length - separator.length)
            val = val.substring(0, val.length - separator.length);

        target.show();
        if (target.val() != val) {
            target.val(val);
            target.trigger('change');
        }
    }

    function collectFormFilters(){
        var filtersList = [];
        $('#form-filter-form').html();
        $('#reports .table-header').find('.dropdown-menu').each(function(){
            var guid = utils.guid(),
                item = $(this).clone(),
                name,
                $html = $('<div class="form-filter-item"></div>');

            item.find('.buttons-row').remove();
            item.removeClass().removeAttr('id').attr('data-guid',guid);
            $(this).attr('data-parent-guid',guid);

            name = $(this).prev().text();
            $html.append('<div class="col-xs-12 col-sm-5 col-md-3"><h5>'+name+'</h5></div><div class="col-xs-12 col-sm-7 col-md-9 field-cont"></div>')
            $html.find('.field-cont').append(item);

            $('#form-filter-form').append($html);
        });
        $('#form-filter-form').find('input.datepicker').datetimepicker({
            format: 'DD-MM-YYYY'
        });
        $('#form-filter-form').find('.datetimepicker').datetimepicker({
            format: 'DD-MM-YYYY HH:mm'
        });

        $('#form-filter-form').find('input[type="text"]').on('blur',function(){
            $(this).attr('value',$(this).val());
        });
    }
    collectFormFilters();
});