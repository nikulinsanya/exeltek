$.fn.quickChange = function(handler) {
    return this.each(function() {
        var self = this;
        self.qcindex = self.selectedIndex;
        var interval;
        function handleChange() {
            if (self.selectedIndex != self.qcindex) {
                self.qcindex = self.selectedIndex;
                handler.apply(self);
            }
        }
        $(self).focus(function() {
            interval = setInterval(handleChange, 100);
        }).blur(function() { window.clearInterval(interval); })
        .change(handleChange); //also wire the change event in case the interval technique isn't supported (chrome on android)
    });
};

function dump(obj) {
    var out = "";
    if(obj && typeof(obj) == "object"){
        for (var i in obj) {
            out += i + ": " + obj[i] + "\n";
        }
    } else {
        out = obj;
    }
    alert(out);
}
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

$(function () {
    function bytesToSize(bytes) {
       if(bytes == 0) return '0 B';
       var k = 1024;
       var sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
       var i = Math.floor(Math.log(bytes) / Math.log(k));
       return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
    }
    $('#file-type').find('input').click(function() {
        $('#file-type').addClass('hidden');
        $('#upload').removeClass('hidden');
        $('#upload>h2').text($('#upload>h2').text() + ' (' + $(this).parent().text().trim() + '):')
    });
    function handle_progress(data) {
        data = $.parseJSON(data);
        
        var url = $('#import-name').attr('data-url') + '?pos=' + data.position;
        
        if ($('#file-type-partial').prop('checked'))
            url += '&partial';
            
        if (data.error) {
            $('#error').html(data.error).removeClass('hidden');
            return;
        } else if (data.forced)
            if (!confirm(data.forced.message)) {
                $('#upload').removeClass('hidden');
                $('#process').addClass('hidden');
                return;
            } else {
                url = data.forced.link;
            }
        else if (data.import_name)
            $('#reports-link').attr('href', $('#reports-link').attr('href') + data.import_name);

        var time = parseFloat($('#import-time').text()) + parseFloat(data.time);
        $('#import-time').text(time.toPrecision(3));

        var memory = Math.max(parseInt($('#import-memory').attr('value')), parseInt(data.memory));
        $('#import-memory').attr('value', memory);
        $('#import-memory').text(bytesToSize(memory));

        var inserted = parseInt($('#import-inserted').text()) + parseInt(data.inserted);
        $('#import-inserted').text(inserted);

        var updated = parseInt($('#import-updated').text()) + parseInt(data.updated);
        $('#import-updated').text(updated);

        var deleted = parseInt($('#import-deleted').text()) + parseInt(data.deleted);
        $('#import-deleted').text(deleted);

        var skipped = parseInt($('#import-skipped').text()) + parseInt(data.skipped);
        $('#import-skipped').text(skipped);

        var total = inserted + updated + skipped;
        $('#import-total').text(total);
        
        $('#import-progress').text(data.progress + '%');
        
        if (data.done == '0') {
            $.get(url, handle_progress);
        } else {
            $('#import-progress').parent().addClass('hidden');
            $('#import-done').removeClass('hidden');
        }
    }
    
    $('#fileupload').fileupload({
        type: 'POST',
        dataType: 'json',
        maxChunkSize: 1 * 1024 * 1024,
        submit: function(e, data) {
            $('#upload').addClass('hidden');
            $('#div-progress').removeClass('hidden');
            $('#error').addClass('hidden');
        },
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress').text(progress+"%");
        },
        done: function (e, data) {
            $('#process').removeClass('hidden');
            $('#div-progress').addClass('hidden');
            var filename = data.result.files[0].name;
            $('#import-name').text(filename); 
            $('#import-name').attr('data-url', $('#import-name').attr('data-url') + filename);
            var url = $('#fileupload').attr('data-url') + '/start/' + filename;
            $.get(url, handle_progress);
        },
        fail: function (e, data) {
            alert('Error occured!');
            $('#div-progress').addClass('hidden');
            $('#upload').removeClass('hidden');
        },
    });
    $('.auto-complete').autocomplete({
        source: function(e, response) {
            $.get(this.element.attr('source') + e.term, function (data) {
                data = $.parseJSON(data);
                response(data);
            });
        },
        minLength: 0,
    });
    
    $('.auto-submit').find('select,input,textarea').change(function() {
        if ($(this).attr('name'))
            $(this).parents('form').submit();
    });
    
    $('.datetimepicker').datetimepicker({
        format: 'DD-MM-YYYY HH:mm'
    });
    
    $('.datepicker').datepicker({
        dateFormat: 'dd-mm-yy'
    });
    
    $('.date-range').click(function () {
        var suffix = $(this).attr('data-suffix');
        if (suffix && suffix != undefined)
            suffix += '-';
        else
            suffix = '';
        $('#' + suffix + 'start').val($(this).attr('data-start'));
        $('#' + suffix + 'end').val($(this).attr('data-end'));
        $('#' + suffix + 'start').parents('form').submit();
    });
    
    $('.btn-toggle').click(function() {
        var state = $(this).attr('state');
        var id = $(this).attr('data-target');
        if (state == 'visible') {
            $('.visible-'+id).attr("rowspan", $(this).attr('data-base'));
            $('.hidden-'+id).addClass('hidden');
            $(this).attr('state', 'hidden');
        } else {
            $('.visible-'+id).attr("rowspan", parseInt($(this).attr('data-base')) + parseInt($(this).attr('data-count')));
            $('.hidden-'+id).removeClass('hidden');
            $(this).attr('state', 'visible');
        }
        
    });
    
    remove_filter = function() {
        var par = $(this).parent().parent();
        var val = par.find('input').val();
        var form = $(this).parents('form');

        $(this).parent().parent().remove();
        if (val)
            form.submit();
    }
    
    $('#filter-form').on('click','.remove-filter',remove_filter);
    
    $('#filter-form').on('click','.add-filter',function() {
        addFilterRow(this);
        return false;
    });

    $('#clearFilters').on('click',function(e){
        $('#filterModal').modal('hide');
        $('#preloaderModal').modal('show');
    });
    
    $('.filters-form').submit(function (e) {
        $('#filterModal').modal('hide');
        $('#preloaderModal').modal('show');
        if ($(this).attr('hold')){
            $('#preloaderModal').modal('hide');
            return false;
        }



        $(this).find('.filter-row').each(function () {
            if ($(this).find('select').first().val() == '') $(this).remove();
        });
        $(this).find('input:checkbox[value="0"]').each(function(i, e) {
            $(e).prepend('<input type="hidden" name="' + $(e).attr('name') + '" value="0"/>');
        });
    });




    //tabs on search view
    $('.content ul.nav').find('a').on('click',function() {
        var li = $(this).parent();
        var id = li.attr('data-id');
        var ul = li.parent();
        ul.find('li.active').removeClass('active');
        //activate upsidedown tabs
        $('.upsidedown').find('.active').removeClass('active');
        $('.topsideup').find('.active').removeClass('active');
        $('.upsidedown').find('[data-id="'+id+'"]').addClass('active');
        $('.topsideup').find('[data-id="'+id+'"]').addClass('active');

        li.addClass('active');
        $('.panel.panel-default').find('div.active').removeClass('active').addClass('hidden');
        $('.panel.panel-default').find('div[data-id="' + id + '"]').removeClass('hidden').addClass('active');
    });
    $('ul.nav.upsidedown').find('a').on('click',function() {
        $("html, body").animate({ scrollTop: $('.topsideup').offset().top - 100 +"px" });
    });





    
    $('.column-permission').change(function() {
        var group = $(this).attr('group-id');
        var id = $(this).attr('data-id');
        var state = $(this).val();
        var url = $(this).parents('table').attr('data-url');
        
        $.get(url + 'save?id=' + id + '&group=' + group + '&state=' + state);
    });
    
    $('.column-search').change(function() {
        var group = $(this).attr('group-id');
        var id = $(this).attr('data-id');
        var state = $(this).val();
        var url = $(this).parents('table').attr('data-url');
        
        $.get(url + 'search?id=' + id + '&group=' + group + '&state=' + state);
    });
    
    $('.column-show').change(function() {
        var id = $(this).attr('data-id');
        var url = $(this).parents('table').attr('data-url');
        var state = $(this).val();
        
        $.get(url + 'show?id=' + id + "&state=" + state);
    });
    
    $('.column-form').change(function() {
        var type = $(this).attr('type-id');
        var id = $(this).attr('data-id');
        var state = $(this).prop('checked') ? 1 : 0;
        var url = $(this).parents('table').attr('data-url');
        
        $.get(url + '?id=' + id + '&type=' + type + '&state=' + state);
    });
    
    $('.check-all').click(function() {
        $(this).parents('table').find('td>input:checkbox').prop('checked', $(this).prop('checked'));
    });
    $('.submission-select').click(function() {
        var parent = $(this).parents('td').first();
        var id = $(this).attr('data-id');
        $('.pending-'+id).removeClass('glyphicon-edit text-info').addClass('text-danger glyphicon-remove');
        
        if ($(this).val() != 0)
            $('#submission-' + $(this).val()).removeClass('text-danger glyphicon-remove').addClass('text-success glyphicon-ok');
        
        if (parent.hasClass('bg-danger')) {
            parent.removeClass('bg-danger');
            var tab = parent.parents('.panel-body').attr('data-id');
            tab = $('li[data-id="' + tab + '"]').find('.badge');
            var count = parseInt(tab.html(),10) - 1;
            if (count)
                tab.text(count);
            else
                tab.remove();
        }
        
        if ($('div[data-id="submissions"]').find('.glyphicon-edit').length == 0)
            $('#finish-job>button').removeClass('disabled');
    });
    
    $('#finish-job').click(function() {
        if (confirm('Do you really want to unassign all contractors?'))
            $('div[data-id="assigned"]').find('select').val('');
        else
            return false;
    });
    
    function confirm_link(e) {
        if (!confirm($(this).attr('confirm'))) {
            e.stopImmediatePropagation();
            return false;
        }
    }
    
    $('[confirm]').click(confirm_link);
    
    function remove_link(e) {
        var target = $(this).parent();
        $.get($(this).attr('href'), function() {
            target.remove();
        });
        return false;
    }
    
    $('a.remove-link').click(remove_link);
    
    $('.back-button').click(function() {
        history.go(-1);
    });
    
    $('.table-filter').click(function() {
        var ul = $(this).parent().parent();
        var quick = ul.find('input:checked');
        var filter = $('#filter-form .add-filter').parents('.filter-row');
        $('#filter-form').parents('form').attr('hold', '1');
        
        var id = ul.attr('data-id');
        var action = ul.find('select').val();

        if (quick.length) {
            action = 2;
            quick.each(function(i, e) {
                add($(e).attr('data-value'));
            });
        }else{
            //restore action
            //action = ul.find('select').val();
            add(ul.find('input.form-control').val());
        }

        filter.parents('form').attr('hold', '').submit();



        function add (value) {
            addFilterRow(
                $('#filter-form .add-filter'),
                {
                    'field-select':id,
                    'action-select':action,
                    'action-value':value
                }
            );
            ul.prev().click();
        };
    });
    
    $('.date-filter').click(function() {
        $(this).parent().parent().find('input').each(function(i, e) {
            $($(e).attr('data-target')).val($(e).val());
        });
        $('#filter-form .add-filter').parents('form').attr('hold', '').submit();
    });
    
    $('.filter-clear').click(function () {
        $(this).parent().parent().find('input').each(function(i, e) {
            $($(e).attr('data-target')).val('');
        });
        $('#filter-form .add-filter').parents('form').attr('hold', '').submit();
    });
    
    $('.upload').click(function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(data) {
                $('#location').val(data.coords.latitude + ',' + data.coords.longitude);
            });
        }
        
        $('#upload-dialog').modal({backdrop: 'static', keyboard: false});
    });

    $('.export-jobs').click(function() {
        $(this).parents('form').attr('action', './imex/export');
    });

    $('.export-result').click(function() {
        $(this).parents('form').attr('action', './search/export');
    });

    $('.assign-jobs').click(function() {
        $(this).parents('form').attr('action', './search/assign');
    });
    
    $('.archive-jobs').click(function() {
        $(this).parents('form').attr('action', './search/archive');
    });
    
    $('.complete-jobs').click(function() {
        $(this).parents('form').attr('action', './search/complete');
    });
    
    $('.reset-jobs').click(function() {
        $(this).parents('form').attr('action', './search/reset');
    });
    
    $('.pager-count').click(function() {
        var count = $(this).attr('data-value');
                
        $.get('?limit=' + count, function() {
            window.location = window.location;
        });
    });
    
    $('.sorting').change(function () {
        var order = $(this).find(':selected').attr('data-order');
        var dir = $(this).find(':selected').attr('data-dir');
        $('#sort-order').val(order);
        $('#sort-dir').val(dir);
        $(this).parents('form').submit();
    });
    
    $('ul.status-filter').find('a').click(function() {
        var filter = $(this).attr('data-id');
        if ($('#status-filter').val() !== filter) {
            $('#status-filter').val(filter).parents('form').submit();
        }
    });
    
    $('#file-type').quickChange(function() {
        if ($(this).val() == 'other')
            $('#file-title').parents('tr').first().show();
        else
            $('#file-title').parents('tr').first().hide();
            
        var selected = $(this).find(':selected');
        $('#file-content').attr('capture', selected.attr('data-capture'));
        $('#file-content').attr('accept', selected.attr('data-accept'));
    });
    $('#file-content').fileupload({
        autoUpload: true,
        type: 'POST',
        dataType: 'json',
        maxChunkSize: 64 * 1024,
        paramName: 'attachment',
        replaceFileInput: false,
        submit: function(e, data) {
            $('#upload-progress').parent().removeClass('hidden');
            $('#upload-progress').text("0%").attr('aria-valuenow', 0).css('width', '0%');
            $('.modal-footer').find('button').addClass('hidden');
            //$('#preloaderModal').modal({backdrop: 'static', keyboard: false});
        },
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#upload-progress').text(progress+"%").attr('aria-valuenow', progress).css('width', progress + '%');
        },
        done: function (e, data) {
            $('#upload-count').val(parseInt($('#upload-count').val()) + 1);
            var parent = $('.files-container').find('tr').first();
            if (parent) {
                var link = $('<tr><td>' + data.result.attachment.content + '</td></tr>');
                link.find('.remove-link').click(confirm_link).click(remove_link);
                parent.before(link);
            }
            $('.modal-footer').find('button.btn-success').before(data.result.attachment.message);
            $('#file-content').val('');
        },
        fail: function (e, data) {
            dump(data.jqXHR);
        },
        always: function (e, data) {
            data.files.pop();
            $('#upload-progress').parent().addClass('hidden');
            $('.modal-footer').find('button').removeClass('hidden');
            $('#start-upload').addClass('hidden');
            //$('#preloaderModal').modal('hide');
        },
        add: function(e, target) {
            $('#start-upload').off('click').on('click', function() {
                var id = $('.upload').attr('data-id');
                var url = $('.upload').attr('data-target');
                var type = $('#file-type').val();
                var title = $('#file-title').val();
                var location = $('#location').val();
                
                url = url + 'prepare/' + id + '?location=' + location + '&type=' + type + '&title=' + title;
                
                $.get(url, function(data) {
                    data = $.parseJSON(data);
                    id = data.id;
                    $('#file-content').fileupload({ url: $('.upload').attr('data-target') + 'upload/' + id });
                    target.submit();
                })
            }).removeClass('hidden');
        }
    });
    
    $('.tree').jstree({
        'core' : {
            'multiple': false,
        }
    }).on('select_node.jstree', function(e, data) {
        if (data.node.data.folder != undefined) {
            var folder = data.node.data.folder;
            var fda = data.node.data.fda;
            var address = data.node.data.address;
            
            var url = $('.tree').attr('data-url') + 'files?folder=' + folder + '&fda=' + fda + '&address=' + address;
        
            $.get(url, function(data) {
                $('#files').html(data);
            });
        }
    });
    
    $('#download-folder').click(function() {
        var selected = $('.tree').jstree().get_selected(true);
        if (selected.length > 0) {
            data = selected[0].data;
            var folder = data.folder;
            var fda = data.fda;
            var address = data.address;
            
            var url = $('.tree').attr('data-url') + 'folder?folder=' + folder + '&fda=' + fda + '&address=' + address;
            
            window.location = url;
        }
        //dump(selected);
    });
    
    var ticket_id_unfocus = function() {
        var val = $(this).val().replace(/\n/g, ',');
        $('#ticket-id').val(val).show();
        $(this).remove();
    }
    
    $('#ticket-id').focus(function() {
        var val = $(this).val().replace(/,/g, "\n");
        var textarea = $('<textarea class="form-control" width="100%"></textarea>').val(val).focusout(ticket_id_unfocus);
        $(this).hide().before(textarea);
        textarea.focus();
    });
    
    $('.notification').click(function() {
        var parent = $(this).parent('div');
        $.get('?dismiss=' + $(this).attr('data-id'), function() {
            parent.remove();
        });
    });
    
    $('.rate-change').change(function() {
        var url = $(this).parents('table').attr('data-url');
        var company = $(this).attr('data-company');
        var column = $(this).attr('data-column');
        $.get(url + '?id=' + column + '&company=' + company + '&rate=' + $(this).val());
    })
    
    $('ul.dropdown-menu').find('input,select,textarea').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $(this).parents('ul.dropdown-menu').find('.btn-success').click();
        }
    })
    
    $('.sortable').each(function (i, e) {
        var column = $(e).attr('data-id');
        //$(e).attr('nowrap', 'nowrap');
        var sort = false;
        var cnt = '';

        if ($('#sorting').find('input[value="-' + column + '"]').length) {
            sort = 'desc';
            cnt = $('#sorting').find('input[value="-' + column + '"]').prevAll().length + 1;
        } else if ($('#sorting').find('input[value="+' + column + '"]').length) {
            sort = 'asc';
            cnt = $('#sorting').find('input[value="+' + column + '"]').prevAll().length + 1;
        }
            
        if (sort == 'asc') {
            sort = '<a href="javascript:;" class="glyphicon glyphicon-sort-by-attributes">' + cnt + '</a>';
        } else if (sort == 'desc') {
            sort = '<a href="javascript:;" class="glyphicon glyphicon-sort-by-attributes-alt">' + cnt + '</a>';
        } else {
            sort = '<a href="javascript:;" class="glyphicon glyphicon-sort"></a>';
        }
        sort = $(sort);
        sort.click(function(e) {
            var column = $(this).parent().attr('data-id');
            var order = ($(this).hasClass('glyphicon-sort-by-attributes') ? '-' : '+');
            e.preventDefault();
            
            var data = '<input type="hidden" name="sort[]" value="' + order + column + '" />';
            if (e.ctrlKey) {
                //alert(column);
                $('#sorting').find('input[value="+' + column + '"]').remove();
                $('#sorting').find('input[value="-' + column + '"]').remove();
            } else if (e.shiftKey) 
                $('#sorting').append(data);
            else if ($('#sorting').find('input[value="+' + column + '"]').length)
                $('#sorting').find('input[value="+' + column + '"]').replaceWith(data);
            else if ($('#sorting').find('input[value="-' + column + '"]').length)
                $('#sorting').find('input[value="-' + column + '"]').replaceWith(data);
            else
                $('#sorting').html(data);
            $('#sorting').parents('form').submit();
            
            return false;
        });
        $(e).prepend('&nbsp;');
        $(e).prepend(sort);
    });
    
    $('.approve-financial').click(function() {
        var id = $(this).attr('data-id');
        var value = $(this).attr('data-value');
        var max = $(this).attr('data-max');
        var row = $(this).parents('tr');
        
        value = prompt('Please, enter confirmed value (max - ' + max + '):', value);
        
        if (value) {
            var url = $(this).parents('table').attr('data-url') + '?id=' + id + '&value=' + value;
            $.get(url, function(data) {
                try {
                    data = $.parseJSON(data);
                    row.removeClass('bg-warning').addClass('bg-success');
                    row.find('td.time').text(data.time);
                    row.find('td.paid').text(data.value);
                    row.find('td.rate').text(data.rate);
                    row.find('td').last().html('');
                } catch (e) {
                    alert(data);
                }
            });
        }
    });

    $('input:checkbox.discrepancy').click(function() {
        if ($(this).prop('checked')) {
            var list = [];
            $('tr.submission:not(.bg-danger)').addClass('hidden');
            $('tr.discrepancy').addClass('hidden');
        } else {
            $('tr.submission:not(.bg-danger)').removeClass('hidden');
            $('tr.discrepancy').removeClass('hidden');
        }
    });


    function initPlugins(){
        $('.checkbox-x').checkboxX({ useNative: true});
        $('[data-toggle="tooltip"]').tooltip();
        $('.shorten').shorten();
        $('.input-float').numericInput({allowFloat: true, allowNegative: true});
        $('.input-int').numericInput({allowFloat: false, allowNegative: true});

        setSelectize($('.selectize'));
        setMultiselect($('.multiselect'));
    }


    function setSelectize(self){
        if(self){
            $(self).selectize({
                create: true,
                sortField: 'text'
            });
        }
    }

    function setMultiselect(self){
        if(self){
            $(self).multiselect({
//                numberDisplayed: 2
            });
        }
    }



    function addFilterRow(self, values){
        var par = $('#filter_row_template'),
            realPar = $(self).parent().parent(),
            filter = $('<div></div>').html(par.html()),
            val = realPar.find('input.form-control').val(),
            i;
        realPar.find('.add-filter').parent().html('<button type="button" class="btn btn-danger remove-filter"><span class="glyphicon glyphicon-minus"></span></button>');

        if(values){
            filter.find('.field-select').val(values['field-select']);
            filter.find('.action-select').val(values['action-select']);
            filter.find('.action-value').val(values['action-value']);
        }
        realPar.after(filter);
        setSelectize(filter.find('select'));

        if (val) {
            $(self).parents('form').submit();
        }
    }

    $('table').on('click','a.dropdown-toggle',function(e){
        $(".dropdown-menu.collapse").removeClass("in").addClass("collapse");
        e.preventDefault();
    });

    $('table#search-table').on('click','tr:not(.table-header)',function(e){
        if(e.target.nodeName != 'INPUT' && e.target.nodeName != 'A'){
            e.preventDefault();
            var html = $(this).html(),
                newHtml = $('<div></div>').html(html),
                buttons = $(newHtml).find('td.table-buttons').html(),
                details = $(newHtml).find('td:not(.table-buttons):not(.checkbox-container)'),
                className = $(this).attr('class') + ' text-center tr-body';

            $('#tableRowButtons').html(buttons);
            $('#table-row-details table tr.tr-body').attr('class',className).html(details);

            $('#tableRowDetails').modal('show');
            $(".dropdown-menu.collapse").removeClass("in").addClass("collapse");
        }
    });

    initPlugins();
});
