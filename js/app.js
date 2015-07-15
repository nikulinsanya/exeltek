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

    $('#preloaderModal').modal({backdrop: 'static', keyboard: false, show: false});

    function handle_progress(data) {
        var pos = 0;
        var date = $('#file-date').val();
        var tod = $('#file-tod').val();
        var region = $('#file-region').val();

        try {
            data = $.parseJSON(data);

            if (data.error) {
                alert(data.error);
                return;
            }

            if (data.import_name)
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

            if (data.done == '1') {
                $('#import-progress').parent().addClass('hidden');
                $('#import-done').removeClass('hidden');
                alert('Done');
                $('#preloaderModal').modal('hide');
                return;
            } else {
                pos = data.position;
            }
        } catch (e) {
            if (data) {
                $('#import-progress').parent().addClass('hidden');
                alert(data);
                $('#preloaderModal').modal('hide');
                return;
            }
        }

        var url = $('#import-name').attr('data-url');
        var data = {
            pos: pos,
            date: date,
            tod: tod,
            region: region,
            data: {},
        };
        $('#file-mapping').find('select').each(function(i, e) {
            data.data[$(e).attr('data-id')] = $(e).val();
        });

        $.post(url, data, handle_progress);
    }

    $('#fileupload').fileupload({
        type: 'POST',
        dataType: 'json',
        maxChunkSize: 1 * 1024 * 1024,
        submit: function(e, data) {
            $('#upload').addClass('hidden');
            $('#div-progress').removeClass('hidden');
            $('#error').addClass('hidden');
            $('#preloaderModal').modal('show');
        },
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress').text(progress+"%");
        },
        done: function (e, data) {
            $('#prepare').removeClass('hidden');
            $('#div-progress').addClass('hidden');
            var filename = data.result.files[0].name;
            $('#import-name').text(filename);
            $('#import-name').attr('data-url', $('#import-name').attr('data-url') + filename);
            var url = $('#fileupload').attr('data-url') + '/prepare/' + filename;
            $.get(url, function(data) {
                data = $.parseJSON(data);
                if (!data.region) data.region = '';
                $('#file-date').val(data.date);
                $('#file-tod').val(data.tod);
                $('#file-region').val(data.region);
                $('#file-last-update').text(data.last_update);
                $('#file-mapping').html(data.html);
                $('#file-region').trigger('change');

                if ($('#file-mapping').find('select').length == 0)
                    $('#import-start').removeClass('disabled');
                else
                    $('#file-mapping').find('select').change(function() {
                        if ($(this).val() == '0')
                            $(this).parents('tr').removeClass('bg-success').addClass('bg-danger');
                        else
                            $(this).parents('tr').addClass('bg-success').removeClass('bg-danger');

                        var fl = true;
                        $('#file-mapping').find('select').each(function(i, e) {
                            if ($(e).val() == '0') fl = false;
                        });

                        if (fl)
                            $('#import-start').removeClass('disabled');
                        else
                            $('#import-start').addClass('disabled');
                    });
                //dump(data.header);
                $('#preloaderModal').modal('hide');
            });
        },
        fail: function (e, data) {
            alert('Error occured!');
            $('#div-progress').addClass('hidden');
            $('#upload').removeClass('hidden');
            $('#preloaderModal').modal('hide');
        },
    });
    $('#file-region').change(function() {
        var val = $(this).find(':selected').attr('data-date');
        $('#file-last-update').text(val ? val : 'Unknown');
    });
    $('#import-start').click(function() {
        var date = $('#file-date').val();
        var tod = $('#file-tod').val();
        var region = $('#file-region').val();

        if (date == '') {
            alert('Please, select date for this file!');
            return false;
        }
        if (tod == '') {
            alert('Please, select time of day for this file!');
            return false;
        }
        if (region == '') {
            alert('Please, select region for this file!');
            return false;
        }

        var old = $('#file-last-update').text();
        if (old != 'Unknown') old = Date.parse(old.replace(/(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
        date = Date.parse(date.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));

        if (old != 'Unknown' && old >= date && !confirm("You're trying to import old file - newer file was already imported! Are you really want to proceed with import?"))
            return false;

        $('#prepare').addClass('hidden');
        $('#process').removeClass('hidden');

        $('#preloaderModal').modal('show');
        handle_progress('');
    });
    $('#csv-selector').find('label').click(function() {
        var val = $(this).children('input').val();
        $('#csv-input').val(val);
        if ($('#region-input').val() != '' && $('#group-input').val()) $('#export-button').removeClass('disabled');
        switch (val) {
            case 'csv':
                $('#columns').find('input[data-csv="1"]').prop('checked', true);
                $('#columns').find('input[data-csv="0"]').prop('checked', false);
                break;
            case 'non-csv':
                $('#columns').find('input[data-csv="0"]').prop('checked', true);
                $('#columns').find('input[data-csv="1"]').prop('checked', false);
                break;
            case 'all':
                $('#columns').find('input').prop('checked', true);
                break;
            case 'none':
                $('#columns').find('input').prop('checked', false);
                break;
        }
    });

    $('#group-selector').find('label').click(function() {
        var val = $(this).children('input').val();
        $('#group-input').val(val);
        if ($('#region-input').val() != '' && $('#csv-input').val()) $('#export-button').removeClass('disabled');
        if (val == '0')
            $('#columns').removeClass('hidden');
        else
            $('#columns').addClass('hidden');
    });
    $('#region-input').change(function() {
        if ($(this).val() && $('#group-input').val() && $('#csv-input').val())
            $('#export-button').removeClass('disabled');
        else
            $('#export-button').addClass('disabled');
    });




    $('.auto-complete').autocomplete({
        source: function(e, response) {
            $('#preloaderModal').modal('show');
            $.get(this.element.attr('source') + e.term, function (data) {
                data = $.parseJSON(data);
                response(data);
                $('#preloaderModal').modal('hide');
            });
        },
        minLength: 0
    }).focus(function(){
        $(this).trigger('keydown');
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

    $('.column-persistent').click(function() {
        var id = $(this).attr('data-id');
        var url = $(this).parents('table').attr('data-url');
        var state = $(this).prop('checked') ? 1 : 0;

        $.get(url + 'persistent?id=' + id + "&state=" + state);
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

    $('.ms').click(function() {
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
        },
        'search': {
            'show_only_matches': true,
            'show_only_matches_children': true,
        },
        'plugins': ['search'],
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
    var search_timeout = false;
    $('#attachments-filter').keyup(function() {
        if (search_timeout) clearTimeout(search_timeout);
        search_timeout = setTimeout(function() {
            $('.tree').jstree(true).search($('#attachments-filter').val());
            //alert($('#attachments-filter').val());
        }, 250);
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
            $('tr.submission:not(.rose)').addClass('hidden');
            $('tr.discrepancy').addClass('hidden');
        } else {
            $('tr.submission:not(.rose)').removeClass('hidden');
            $('tr.discrepancy').removeClass('hidden');
        }
        $('#submissions_count').text($('.ticket-id:not(.hidden)').length);
    });


    function initPlugins(){
        $('.checkbox-x').checkboxX({ useNative: true});
        $('[data-toggle="tooltip"]').tooltip();
        $('.shorten').shorten();
        $('.input-float').numericInput({allowFloat: true, allowNegative: true});
        $('.input-int').numericInput({allowFloat: false, allowNegative: true});

        setSelectize($('.selectize'));
        setMultiselect($('.multiselect'));
        setFilterDateRangePickers();
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
            $(self).multiselect({});
        }
    }
    function setFilterDateRangePickers(){
        $('.daterange').each(function(){
            if($(this).attr('data-start') && $(this).attr('data-end')){
               var startVal = $('#'+$(this).attr('data-start')).val(),
                   endVal   = $('#'+$(this).attr('data-end')).val(),
                   format   = $(this).attr('format') ? $(this).attr('format') : 'DD-MM-YYYY';

               if(startVal && endVal){
                   $(this).find('span').html(startVal + ' - ' + endVal);
               }
               $(this).daterangepicker({
                   format: format,
                   maxDate: new Date(),
                   startDate: startVal ? startVal : '',
                   endDate: endVal ? endVal : '',
                   ranges: {
                       'Today': [moment(), moment()],
                       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                       'This week': [moment().startOf('week').add(1,'days'), moment()],
                       'Last week': [moment().subtract(1, 'week').startOf('week').add(1,'days'),moment().startOf('week')],
                       'This month': [moment().startOf('month'), moment()],
                       'Last month': [moment().subtract(1, 'month').startOf('month'), moment().startOf('month').subtract(1, 'days')]
                   },
                   locale: { cancelLabel: 'Clear' }
               },
                   function(start, end, label) {
                       $('#preloaderModal').modal('show');
                       this.element.find('span').html((start.isValid() ? start.format(format) : '') + ' - ' + (end.isValid() ? end.format(format) : ''));
                       $('#'+this.element.attr('data-start')).val(start.isValid() ? start.format(format) : '' );
                       $('#'+this.element.attr('data-end')).val(end.isValid() ? end.format(format) : moment().format(format));
                       $(this.element).parents('form').submit();
                   }
               ).on('cancel.daterangepicker', function(ev, picker) {
                   $('#preloaderModal').modal('show');
                   $(this).find('span').html('');
                   $('#'+$(this).attr('data-start')).val('');
                   $('#'+$(this).attr('data-end')).val('');
                   $(this).parents('form').submit();
               });
            }
        });
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
    $('.batch-ticket').on('click',function(){
        if(!$('#your-username').val()){
            alert('Enter your UserName');
            return;
        }
        var data = {
            jobs : collectDataToBatch(),
            username: $('#your-username').val()
        };

        $('#preloaderModal').modal('show');
        $.ajax({
            url:utils.baseUrl()+'search/batch/set',
            dataType:'JSON',
            type:'POST',
            data: data
        }).done(function(data){
            window.location.reload();
        }).fail(function(e){
            $('#preloaderModal').modal('hide');
            alert('Error: ' + e.responseText);
        })
    });


    $('.batch-jobs').on('click',function(e){
        e.preventDefault();
        $('#preloaderModal').modal('show');
        var ids = [],
            i,
            checkboxes = $('#search-table td').find('input:checked');
        $.each(checkboxes, function(){
           ids.push($(this).attr('data-id'));
        });

        getEditableFields(ids).done(function(data){
            var html = [],
                rows = {},
                cellHtml,
                i, j, id;
            data.columns.sort(function(a,b){return a.type > b.type});

            for(i in data.jobs){
                rows[data.jobs[i].id] = prepareField(data.jobs[i], data.columns);
            }

            //fill table row
            html.push('<tr>');
            for (i in data.columns){
                html.push(
                    '<th>',
                    data.columns[i]['name'],
                    '</th>');
            }
            html.push('</tr>');

            //fill table colls
            for (j in rows){
                html.push('<tr data-id="'+j+'">');
                for (i in data.columns){
                    id = data.columns[i]['id'];
                    html.push('<td>');
                    if(rows[j][id]){
                        html.push(rows[j][id].html);
                    }else{
                        cellHtml = utils.getFieldByType(data.columns[i], {}, data.columns[i].id);
                        html.push(cellHtml);
                    }
                    html.push('</td>');
                }
                html.push('</tr>');
            }



            $('#batchEditModal .edit-tickets-table').html(html.join(''));


            $('#preloaderModal').modal('hide');
            $('#batchEditModal').modal('show');
        }).fail(function(){
            $('#preloaderModal').modal('hide');
        })
    });

    function collectDataToBatch(){
        var data = [],
            i,
            ticketId,
            cellId,
            rawData = {},
            child,
            editor,
            container = $('#table-row-details td');

        $.each(container, function(){
            child = $(this).children();
            ticketId = $(this).parent().attr('data-id');
            cellId = child.attr('data-id');
            if(!ticketId || !cellId){
                return true;
            }

            rawData[ticketId] = rawData[ticketId] || {};
            editor = child.children();
            rawData[ticketId][cellId] = editor.val();
        });

        for(i in rawData){
            data.push({
                id: i,
                data: rawData[i]
            });
        }
        return data;
    }

    function prepareField(row, values){
        var i,
            value,
            columns = {},
            field;

        if(!row || !values){
            return '';
        }

        for(i in row.data){
            value = utils.searchInListById(i, values);
            field = utils.getFieldByType(value, row.data, i);
            columns[i] = {
                name: value.name,
                type: value.type,
                html: field
            };

        }
        return columns;

    }

    function getEditableFields(ids){
        var url = [],
            i = ids.length;
        url.push(utils.baseUrl(),'search/batch/get?id=');
        url.push(ids.join(','));

        return $.ajax({
            url:url.join(''),
            dataType:'JSON',
            type:'GET'
        });
    }

    initPlugins();
});
