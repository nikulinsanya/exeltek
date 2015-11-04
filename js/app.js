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

function expandCollapseTreeView(expand){
    if(expand){
        $().tabelize(false,'expandAll');
        $('.expandAll').hide();
        $('.collapseAll').show();
    }else{
        $().tabelize(false,'collapseAll');
        $('.expandAll').show();
        $('.collapseAll').hide();
    }
    $('.popover').popover('hide');
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

    $('#payment-company').change(function() {
        var company = $(this).val();
        var total = 0;
        $('div.payment-info').each(function(i, e) {
            var value = $(e).attr('data-company-' + company);
            if (value == undefined) value = 0; else value = parseFloat(value);
            total += value;
            $(e).removeClass (function (index, css) {
                return (css.match (/(^|\s)text-\S+/g) || []) + ' ' + (css.match (/(^|\s)glyphicon-\S+/g) || []);
            });
            var paid = $(e).attr('data-paid-' + company);
            paid = paid == undefined ? 0 : parseFloat(paid);
            if (paid) {

                if (paid < value) {
                    total -= paid;
                    $(e).addClass('text-info glyphicon-flag');
                } else {
                    total -= value;
                    $(e).addClass('text-danger glyphicon-flag');
                }
                $(e).children('span.payment-job-value').text(paid + '/' + value);
            } else if (value) {
                $(e).children('span.payment-job-value').text(value);
                $(e).addClass('text-success glyphicon-ok');
            } else {
                $(e).addClass('text-muted glyphicon-minus');
            }
        });
        $('#payment-avail').text(total.toFixed(2));
        $('#payment-amount').val(total.toFixed(2));
        if (total > 0)
            $('.payment-continue').removeClass('hidden');
        else
            $('.payment-continue').addClass('hidden');
    });

    $('#payment-continue').click(function() {
        var company = $('#payment-company').val();

        var html = [];
        html.push('<tr><th>Ticket</th><th>Total</th><th>Payed</th><th>Left</th><th>Add value</th><th>% value</th></tr>');
        $('div.payment-info[data-company-' + company + ']').each(function (i, e) {
            var id = $(e).children('span.payment-job-id').text();
            var total = parseFloat($(e).attr('data-company-' + company));
            var paid = $(e).attr('data-paid-' + company);
            paid = paid == undefined ? 0 : parseFloat(paid);
            if (total > paid)
                html.push('<tr><td>',id,'</td><td>',total.toFixed(2),'</td><td>',paid.toFixed(2),'</td><td class="payment-total">',(total-paid).toFixed(2),'</td><td>' +
                    '<input type="text" class="form-control payment-value" name="payment[',id,']" value="',(total-paid).toFixed(2),'" />','</td><td>' +
                    '<input type="text" class="form-control payment-percentage" value="100.00" />','</td></tr>');
        });
        html = '<table class="table">'+html.join('')+'</table>';
        $('#payment-details').prepend(html);
        $('#payment-details').removeClass('hidden');
        $('#payment-pre').addClass('hidden');
    });

    $('#payment-amount').keyup(function() {
        var sum = parseFloat($(this).val());
        total = 0;
        $('.payment-total').each(function(i, e) {
            total += parseFloat($(e).text());
        });
        $('#payment-details').find('tr').each(function(i, e) {
            var value = parseFloat($(e).find('.payment-total').text());
            var val = value / total * sum;
            $(e).find('input.payment-value').val(val.toFixed(2));
            $(e).find('input.payment-percentage').val((val/value*100).toFixed(2));
        });
    })

    $('#payment-details').on('keyup', 'input.payment-percentage', function() {
        var total = parseFloat($(this).parents('tr').children('.payment-total').text());
        var value = parseFloat($(this).val());
        if (isNaN(value) || value < 0) {
            value = 0;
            $(this).val('0');
        }

        $(this).parents('tr').find('input.payment-value').val((value/100 * total).toFixed(2));
        total = 0;
        $('.payment-value').each(function(i, e) {
            total += parseFloat($(e).val());
        });
        $('#payment-amount').val(total);
    });

    $('#payment-details').on('keyup', 'input.payment-value', function() {
        var total = parseFloat($(this).parents('tr').children('.payment-total').text());
        var value = parseFloat($(this).val());
        if (isNaN(value) || value < 0) {
            value = 0;
            $(this).val('0');
        }

        $(this).parents('tr').find('input.payment-percentage').val((value/total * 100).toFixed(2));
        total = 0;
        $('.payment-value').each(function(i, e) {
            total += parseFloat($(e).val());
        });
        $('#payment-amount').val(total);
    });

    $('#payment-cancel').click(function() {
        $('#payment-details').addClass('hidden');
        $('#payment-pre').removeClass('hidden');
    });

    $('#payment-form').submit(function() {
        var has_paid = $('div.payment-info.text-danger').length > 0;
        var has_unpaid = $('div.payment-info.text-success,div.payment-info.text-info').length > 0;
        if (!$('#payment-company').val()) {
            alert('Please, select contractor!');
            return false;
        }

        if (!has_unpaid && !confirm('Warning! There is no unpaid tickets for this contractor! Are you really want to create payment for paid tickets only?'))
            return false;

        if (has_paid && !confirm('Warning! There are paid tickets for this contractor! Are you really want to create new payments for paid tickets?'))
            return false;

        var data = $(this).serialize();
        $.post(utils.baseUrl() + 'search/payment', data, function(data) {
            try {
                data = $.parseJSON(data)
                if (data.success)
                    window.location = utils.baseUrl() + 'search';
            } catch (e) {
                alert(data);
            }
        });

        return false;
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
        }
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

    $('.auto-submit').submit(function() {
        if ($(this).prop('hold'))
            return false;
    });
    $('.auto-submit').find('select:not(.no-submit),input:not(.no-submit),textarea:not(.no-submit)').change(function() {
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


    $('.tickets-search-assign').click(function(e) {
        var date = $(this).attr('data-date');

        var data = {};
        data['columns[]'] = 266;
        data['actions[]'] = 2;
        data['values[]'] = date;
        $.post(utils.baseUrl() + 'search', data, function(data) {
            window.location = utils.baseUrl() + 'search?id=' + data.id;
        });
        return false;
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
        var select = $(this).find('select[name="company"]');
        if (select) {
            $(this).append('<input type="hidden" name="company" value="' + ($(select).val() && $(select).val().join(',') || '') + '">');
            $(select).remove();
        }

        var data = $(this).serializeArray();
        $.post(utils.baseUrl() + 'search', data, function(data) {
            window.location = utils.baseUrl() + 'search?id=' + data.id;
        });
        return false;
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
        $('.pending-'+id).parent().parent().removeClass('yellow').addClass('rose');
        
        if ($(this).val() != 0) {
            $('#submission-' + $(this).val()).removeClass('text-danger glyphicon-remove').addClass('text-success glyphicon-ok');
            $('#submission-' + $(this).val()).parent().parent().removeClass('rose').addClass('lgreen');
        }
        
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
        var target = $(this).parents('table').parent('div');
        $.get($(this).attr('href'), function() {
            target.remove();
        });
        return false;
    }
    
    $('a.remove-link').click(remove_link);
    
    $('.back-button').click(function() {
        history.go(historyStateCount ? (historyStateCount+1) * -1 : -1);

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
            var input = ul.find('input.form-control');
            var value = input.val().split(input.attr('data-separator'));
            value.forEach(add);
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

    $('.date-table-filter').click(function() {
        var ul = $(this).parent().parent();
        var filter = $('#filter-form .add-filter').parents('.filter-row'),
            id = ul.attr('data-id'),
            endValue = $(this).parent().parent().find('input.end-date').val(),
            startValue = $(this).parent().parent().find('input.start-date').val();

        $('#filter-form').parents('form').attr('hold', '1');
        if(startValue){
            add(startValue,id,5);
        }
        if(endValue){
            add(endValue,id,4);
        }

        filter.parents('form').attr('hold', '').submit();
        function add (value,id, action) {
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

    $('.date-clear').click(function () {
        var ul = $(this).parent().parent(),
            id = ul.attr('data-id');
        ul.find('input').each(function(i, e) {
            $(this).val('');
            $($(e).attr('data-target')).val('');
        });

        $('[name="columns[]"]').each(function(){
            if($(this).val() == id){
                $(this).parent().parent().remove();
            }
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

    $('#search-table').on('change','input[type="checkbox"]', checkAbility);
    function checkAbility(){
        if($('#search-table td.checkbox-container input[type="checkbox"]:checked').length){
            $('.search-buttons-bottom button').removeAttr('disabled');
        }else{
            $('.search-buttons-bottom button').attr('disabled','disabled');
        }
    }
    checkAbility();

    $('#export-map').on('click',function (e) {
        if(!$('#search-table').find('[type="checkbox"]:checked').length){
            e.preventDefault();
            alert('Please, select at least one ticket to export');
        }
    });

    $('.export-button').click(function() {
        var url = '';
        if ($(this).attr('data-url')) url = $(this).attr('data-url') + '?'; else url = '?';
        if ($(this).attr('data-id')) url += $(this).attr('data-id') + '&';
        $(this).attr('href', url + $('div.content').find('form').serialize());
    });

    $('.export-attachments').click(function() {
        var ids = [];
        $('#search-table input[type=checkbox][data-id]:checked').each(function(i, e) {
            ids.push($(e).attr('data-id'));
        });

        document.location = utils.baseUrl() + 'attachments/tickets?id=' + ids.toString();
    });


    $('#payment-jobs').click(function() {
        $(this).parents('form').attr('action', './search/payment');
    });

    $('.export-jobs').click(function() {
        $(this).parents('form').attr('action', './imex/export');
    });

    $('.export-result').click(function() {
        $(this).parents('form').attr('action', './search/export');
    });

    $('.export-excel').click(function() {
        $(this).parents('form').attr('action', './search/export/excel');
    });

    $('.export-map').click(function() {
        $(this).parents('form').attr('action', './search/map');
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
        $('#file-content').attr('accept', selected.attr('data-accept'));
    });

    var showTimer;
    var filesList = [];
    $('#file-content').fileupload({
        autoUpload: true,
        type: 'POST',
        dataType: 'json',
        maxChunkSize: 64 * 1024,
        paramName: 'attachment',
        replaceFileInput: false,
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#upload-progress').text(progress+"%").attr('aria-valuenow', progress).css('width', progress + '%');
        },
        done: function (e, data) {
            $('#upload-count').val(parseInt($('#upload-count').val()) + 1);
            //var parent = $('.files-container').find('tr').first();

            var link = $('<div style="overflow: hidden;" class="col-xs-12 col-md-6 col-lg-4 ' + ($('.files-container').find('div').first().hasClass('bg-warning') ? 'yellow' : 'bg-warning') + '">' + data.result.attachment.content + '</div>');

            link.find('.remove-link').click(confirm_link).click(remove_link);
            $('.files-container').prepend(link);
            $('.modal-footer.upload-footer').find('button.btn-success').after(data.result.attachment.message);

            if (filesList.length > 0) {
                $('#start-upload').click();
            }else{
                setTimeout(function(){
                    $('.modal-footer.upload-footer').find('.alert').fadeOut();
                },5000);
            }
        },
        fail: function (e, data) {
            dump(data.jqXHR);
        },
        always: function (e, data) {
            data.files.pop();
            $('#upload-progress').parent().fadeOut();
            $('.modal-footer').find('button').removeClass('hidden');
            $('#start-upload').addClass('hidden');
            //$('#preloaderModal').modal('hide');
            setTimeout(function(){
                $('.modal-footer.upload-footer').find('.alert:visible').first().fadeOut();
            },3000);
            clearTimeout(showTimer);
        },
        add: function(e, target) {
            for (var i in target.files)
                filesList.push(target.files[i]);

            $('#start-upload').removeClass('hidden');
        }
    });
    $('#start-upload').click(function() {
        var file = filesList.pop();
        if (filesList.length < 1)
            $('#start-upload').addClass('hidden');

        var id = $('.upload').attr('data-id');
        var url = $('.upload').attr('data-target');
        var type = $('#file-type').val();
        var title = $('#file-title').val();
        var location = $('#location').val();

        url = url + 'prepare/' + id + '?location=' + location + '&type=' + type + '&title=' + title;

        $.get(url, function (data) {
            data = $.parseJSON(data);
            id = data.id;
            clearTimeout(showTimer);
            showTimer = setTimeout(function(){
                $('#upload-progress').parent().fadeIn();
                $('#upload-progress').text("0%").attr('aria-valuenow', 0).css('width', '0%');
            },1000);

            $('.modal-footer').find('button').addClass('hidden');

            $('#file-content').fileupload({url: $('.upload').attr('data-target') + 'upload/' + id});
            $('#file-content').fileupload('send', {files: [ file ]});
            $('#file-content').val('');
        })
    });


    $('.tree').jstree({
        'core' : {
            'multiple': false
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
        var target = $(this).next();
        var separator = target.attr('data-separator');
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
        $(this).remove();
        if (target.val() != val) {
            target.val(val);
            target.trigger('change');
        }
    }

    $('.multiline:not(".datepicker")').focus(function() {
        var separator = $(this).attr('data-separator');
        $('form').prop('hold', true);
        var val = $(this).val();
        while (val.indexOf(separator) !== -1)
            val = val.replace(separator, '\n');
        var textarea = $('<textarea class="form-control" width="100%"></textarea>').val(val).focusout(ticket_id_unfocus);
        $(this).hide().before(textarea);
        textarea.focus();
        $('form').prop('hold', false);
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
        var region = $(this).attr('data-region');
        $.get(url + '?id=' + column + '&company=' + company + '&region=' + region + '&rate=' + $(this).val());
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

    $('.approve-financial,.unapprove-financial').click(function() {
        var id = $(this).attr('data-id');
        var value = $(this).attr('data-value');
        var max = $(this).attr('data-max');
        var rate = $(this).attr('data-rate');
        var row = $(this).parents('tr');
        var action = $(this).hasClass('approve-financial') ? true : false;

        if (action)
            value = prompt('Please, enter confirmed value (max - ' + max + '):', value);
        else
            if (!confirm('Are you really want to unapprove this submission? In case of unapprovement it can be re-approved later.')) return false;

        if (value || !action) {
            var url = utils.baseUrl() + 'reports/financial/' + (action ? 'approve' : 'unapprove') + '?id=' + id + '&value=' + value;
            $.get(url, function(data) {
                try {
                    data = $.parseJSON(data);
                    if (action) {
                        row.removeClass('yellow').addClass('lgreen');
                        row.find('td.time').text(data.time);
                        row.find('td.paid').text(data.value);
                        row.find('td.rate').text(data.rate);
                        row.find('td>button').removeClass('approve-financial btn-success').addClass('unapprove-financial btn-warning').text('Unapprove');
                    } else {
                        row.removeClass('lgreen').addClass('yellow');
                        row.find('td.time').text('');
                        row.find('td.paid').text('');
                        row.find('td.rate').text(rate);
                        row.find('td>button').removeClass('unapprove-financial btn-warning').addClass('approve-financial btn-success').text('Approve');
                    }
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


    $('.show-content-in-popup').on('click',function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        $.ajax({
            url:url,
            type:'get',
            dataType:'html',
            success: function(html){
                var dialog = $('#modalHtmlContainer'),
                    container = dialog.find('.modal-body');
                container.html(html);
                initPlugins();
                $('#modalHtmlContainer').modal('show');

            },
            error: function(e){
                console.log(e);
                alert('Internal server error');
            }
        })

        return false;
    });


    function initPlugins(){
        $('.checkbox-x').checkboxX({ useNative: true});
        $('[data-toggle="tooltip"]').tooltip();
        $('.shorten').shorten();
        $('.input-float').numericInput({allowFloat: true, allowNegative: true});
        $('.input-int').numericInput({allowFloat: false, allowNegative: true});
        $('.datepicker').datepicker({
            dateFormat: 'dd-mm-yy'
        });

        setSelectize($('.selectize'));
        setMultiselect($('.multiselect'));
        setFilterDateRangePickers();
    }


    function setSelectize(self){
        if(self){
            $(self).selectize({
                create: true,
                sortField: 'text',
                onDropdownClose      : function($dropdown) {
                    //fix for page jump on small screens
                    var left = $dropdown.position().left,
                        top = $dropdown.position().top;
                    setTimeout(function(){
                        window.scrollTo(left,top);
                    },10);
                }
            });
        }
    }

    function setMultiselect(self){
        if(self){
            $(self).multiselect({
                maxHeight: 200
            });
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
                       this.element.find('span').html((start.isValid() ? start.format(format) : '') + ' - ' + (end.isValid() ? end.format(format) : moment().format(format)));
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
        if(!data.jobs.length){
            alert('Nothing to send');
            return;
        }


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
            html.push('<th>Ticket</th>');
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
                html.push('<td>',j,'</td>');
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

            initPlugins();


            $('#preloaderModal').modal('hide');
            $('#batchEditModal').modal('show');
        }).fail(function(){
            $('#preloaderModal').modal('hide');
        })
    });

    $('.clear-filters').click(function() {
        $('.clear-filters').addClass('hidden');
        $('#export-lifd').addClass('hidden');
        $('#lifd-report').html('');
    });

    $('#export-lifd').click(function() {
        $('#lifd-report-form').prop('export', true);
        $('#lifd-report-form').submit();
        $('#lifd-report-form').prop('export', false);
    });


    var testedLabels = {
        'data-no-result': 'No Test Result',
        'data-seq-required':'Tested SEQ Number Required',
        'data-no-issue':'Tested No Issue',
        'data-text-exception':'Test Exception',
        'data-qa-issues':'QA Issues'
    }
    $('#lifd-report-form').submit(function() {
        if ($(this).prop('export')) return true;
        var data = $(this).serialize(),
            attrs = ['data-no-result','data-seq-required','data-no-issue','data-text-exception','data-qa-issues'],
            i
            ;
        $('#filterModal').modal('hide');
        $('#preloaderModal').modal('show');
        if (data) {
            $('label.no-filters').hide();
            $('.clear-filters').removeClass('hidden');
        } else {
            $('div.text-info-filters>div').html('');
            $('label.no-filters').show();
            $('.clear-filters').addClass('hidden');
        }
        $.post('', data, function(data) {
            $('#filterModal').modal('hide');
            try {
                data = $.parseJSON(data);
                var filters = [];
                for (var i in data.filters)
                    filters.push(
                        '<span class="filter-item">',
                            data.filters[i].name ,
                            ': <label class="filter_value">',
                            data.filters[i].value ,
                        '</label></span>'
                    );
                $('div.text-info-filters>div').html(filters.join(''));
                $('#lifd-report').html(data.html);
                initTreeView();
                $('[data-toggle="popover"]').each(function(){
                    var html = [];

                    var found = false;
                    html.push('<ul>');

                    for(i=0;i<attrs.length;i++){
                         if($(this).attr(attrs[i])){
                             found = true;
                             html.push('<li>',
                                 '<b>',
                                 $(this).attr(attrs[i]),
                                 '</b>  - &nbsp;',
                                 testedLabels[attrs[i]],
                                 '</li>');
                         };
                    }

                    if(!found){
                        $(this).remove();
                        return;
                    }

                    html.push('</ul>');

                    $(this).popover({
                        html:true,
                        content:html.join('')
                    });
                    $(this).on('show.bs.popover', function () {
                        $('.popover').popover('hide');
                    })
                });
                $('.glyphicon[data-toggle="popover"]').on('click', function(e){
//                    $('.popover').popover('hide');
                    e.preventDefault();
                    return false;
                });
            } catch (e) {
                alert(data);
            }
            $('#preloaderModal').modal('hide');
            $('#export-lifd').removeClass('hidden');
        });
        return false;
    });

    if($('.submission-filter-container').length ||
        $('.financial-filter-container').length){
        initFsaFdaFill();
    }

    function initFsaFdaFill(){
        fillFsa();
        fillFsam();
        fillFda();
    }
    function fillFsa(){
        var selected = $('[data-fsa-selected]').attr('data-fsa-selected') &&
            $('[data-fsa-selected]').attr('data-fsa-selected').split(',');
        $.ajax({
            url:utils.baseUrl() + "json/fsa",
            type:'get',
            dataType:'JSON',
            success: function(data){
                var i = data.length,
                    html = [];
                while(i--){
                    html.unshift('<option value="',
                        data[i],
                        '"',
                        selected.indexOf(data[i]) != -1 ? 'selected="selected"' : '',
                        '>',
                        data[i],
                        '</option>')
                }
                $('.fsa-filter').html(html.join(''));
                $('.fsa-filter').multiselect('rebuild');
                $('.fsam-filter').html('');
            },
            error: function(e){
                alert(e.responseText);
                $('.fsam-filter').html('');
                $('.fsa-filter').html('');
            }
        });
    }
    function fillFsam(){
        var selected = $('[data-fsam-selected]').attr('data-fsam-selected') &&
            $('[data-fsam-selected]').attr('data-fsam-selected').split(',');
        
        $.ajax({
            url:utils.baseUrl() + "json/fsam",
            type:'get',
            dataType:'JSON',
            success: function(data){

                var i = data.length,
                    html = [];
                while(i--){
                    html.unshift('<option value="',
                        data[i],
                        '"',
                        selected.indexOf(data[i]) != -1 ? 'selected="selected"' : '',
                        '>',
                        data[i],
                        '</option>');
                }
                $('.fsam-filter').html(html.join(''));
                $('.fsam-filter').multiselect('rebuild');
            },
            error: function(e){
                alert(e.responseText);
                $('.fsam-filter').html('');
                $('.fsa-filter').html('');
            }
        });
    }
    function fillFda(){
        var selected = $('[data-fda-selected]').attr('data-fda-selected') &&
            $('[data-fda-selected]').attr('data-fda-selected').split(',');
        $.ajax({
            url:utils.baseUrl() + "json/fda",
            type:'get',
            dataType:'JSON',
            success: function(data){
                var i = data.length,
                    html = [];
                while(i--){
                    html.unshift('<option value="',
                        data[i],
                        '"',
                        selected.indexOf(data[i]) != -1 ? 'selected="selected"' : '',
                        '>',
                        data[i],
                        '</option>');
                }
                $('.fda-filter').html(html.join(''));
                $('.fda-filter').multiselect('rebuild');
            },
            error: function(e){
                alert(e.responseText);
            }
        })
    }


    $('.company-filter, .region-filter').on('change',function(){
        $.ajax({
            url:utils.baseUrl() + "json/fsa?company="+
                ($('.company-filter').val() ? $('.company-filter').val().join(','): '') +
                '&region='+
                $('.region-filter').val(),
            type:'get',
            dataType:'JSON',
            success: function(data){
                var i = data.length,
                    html = [];
                while(i--){
                    html.unshift('<option value="',
                        data[i],
                        '">',
                        data[i],
                        '</option>');
                }
                $('.fsa-filter').html(html.join(''));
                $('.fsa-filter').multiselect('rebuild');
                $('.fsam-filter').html('');
            },
            error: function(e){
                alert(e.responseText || e);
            }
        })

    });

    $('.fsa-filter').on('change',function(){
            $.ajax({
                url:utils.baseUrl() + "json/fsam?company="+
                    ($('.company-filter').val() ? $('.company-filter').val().join(',') : '') +
                    '&fsa='+
                    ($(this).val() ? $(this).val().join(',') : ''),
                type:'get',
                dataType:'JSON',
                success: function(data){
                    var i = data.length,
                        html = [];
                    while(i--){
                        html.unshift('<option value="',
                            data[i],
                            '">',
                            data[i],
                            '</option>')
                    }
                    $('.fsam-filter').html(html.join(''));
                    $('.fsam-filter').multiselect('rebuild');
                },
                error: function(e){
                    alert(e.responseText);
                }
            })
    });

    $('.fsam-filter').on('change',function(){
        if(!$('.fda-filter').length){
            return false;
        }
        $.ajax({
            url:utils.baseUrl() + "json/fda?fsam="+
                ($('.fsam-filter').val() ? $('.fsam-filter').val().join(',') : ''),
            type:'get',
            dataType:'JSON',
            success: function(data){
                var i = data.length,
                    html = [];
                while(i--){
                    html.unshift('<option value="',
                        data[i],
                        '">',
                        data[i],
                        '</option>')
                }
                $('.fda-filter').html(html.join(''));
                $('.fda-filter').multiselect('rebuild');
            },
            error: function(e){
                alert(e.responseText);
            }
        })
    });

    $('.time-machine-item').click(function (e) {
        if(e.target.nodeName == 'INPUT' || e.target.nodeName == 'A') return;

        var val = $(this).prev('tr').attr('data-id');
        if (val) {
            $('#time-machine-start').removeClass('disabled').attr('data-id', $(this).attr('data-id'));
            $('tr.time-machine-item').each(function (i, e) {
                $(e).removeClass($(e).attr('data-saved')).addClass('active');
            });
            $('tr.time-machine-item[data-id=' + val + '] ~ tr').each(function (i, e) {
                $(e).removeClass('active').addClass($(e).attr('data-saved'));
            });
        } else {
            $('#time-machine-start').addClass('disabled');
            $('tr.time-machine-item.active').each(function (i, e) {
                $(e).addClass($(e).attr('data-saved')).removeClass('active');
            });
        }
    });

    $('#time-machine-start').click(function() {
        if (!confirm('Rolling back can\'t be undone! Are you really want to continue?')) return;

        var id = $('#ticket-id').text().trim();
        var val = $(this).attr('data-id');

        $.get(utils.baseUrl() + 'timemachine?id=' + id + '&point=' + val, function(data) {
            document.location.reload(true);
        });
    });


    function initTreeView(){
        $('#fda_table').tabelize({
            fullRowClickable : true
        });

    }


    $('.approve-submission').click(function() {
        var id = $(this).attr('data-id');
        var parent = $(this).parent('td').parent('tr');
        if (confirm('Warning: current job data will be overwritten! Are you really want to approve this submission?')) {
            $.get(utils.baseUrl() + 'submissions/approve?id=' + id, function(data) {
                var elm = parent.find('td');
                if (elm.length == 1) {
                    elm.find('button').remove();
                    parent.removeClass('rose').addClass('lgreen');
                    parent.find('span.glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok').removeClass('text-danger').addClass('text-success');
                } else {
                    elm.last().html('');
                    elm = elm.last().prev();
                    elm.html(elm.prev().html());
                    parent.removeClass('bg-danger').addClass('bg-success');
                    parent.find('span.glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok').removeClass('text-danger').addClass('text-success');
                }
            })
        }
    });


    $.fn.wPaint.menus.main.img ='js/lib/wpaint/plugins/main/img/icons-menu-main.png';
    if(utils.isMobileBrowser){
        $("#wPaintMobile").wPaint({
            path:utils.baseUrl(),
            // auto center images (fg and bg, default is left/top corner)
            menuHandle:      false,               // setting to false will means menus cannot be dragged around
            menuOrientation: 'horizontal',       // menu alignment (horizontal,vertical)
            menuOffsetLeft:  5,                  // left offset of primary menu
            menuOffsetTop:   5
        });
        $('.files-container').on('click','.image-attachments',function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $("#wPaintMobile").wPaint('clear');
            $(".wpaintContainer").show();
            var canvas=$('.wPaint-canvas-bg').get(0);
            var drawCanvas=$('.wPaint-canvas').get(0);
            var ctx=canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            var img=new Image();
            img.onload=function(){
                var x, y, w, h,xR,yR,ratio,
                    initWidth =500,
                    initHeight = 500;

                if (img.width > initWidth || img.height > initHeight) {
                    xR = initWidth / img.width;
                    yR = initHeight / img.height;

                    ratio = xR < yR ? xR : yR;

                    w = img.width * ratio;
                    h = img.height * ratio;
                }
                x = (initWidth - w) / 2;
                y = (initHeight - h) / 2;

                drawCanvas.width = w;
                drawCanvas.height = h;
                $(drawCanvas).css({
                    top:y,
                    left:x
                });
                ctx.drawImage(img,x, y, w, h);
            }
            img.src=url;
            $('.new-window-open').attr('data-url', url);
            $('.update-image').attr('data-id', $(this).attr('data-id'));
        });

        $('.new-window-open').on('click', function(){
            window.open($(this).attr('data-url'));
        });
        $('.close-wpaint').on('click', function(){
            $(".wpaintContainer").hide();
        });

        $('.update-image').on('click', function(){
            var base64 = $('.wPaint-canvas').get(0).toDataURL();
            $(".wpaintContainer").show();
            $('#preloaderModal').modal('show');
            $.ajax({
                type:'post',
                url:utils.baseUrl() + 'search/update/'+$(this).attr('data-id'),
                data: base64,
                success: function(){
                    window.location.reload();
                },
                error: function(e){
                    alert('Internal server error');
                    console.log(e);
                }
            });
        });

    }else{
        $("#wPaint").wPaint({
            path:utils.baseUrl(),
            // auto center images (fg and bg, default is left/top corner)
            menuHandle:      false,               // setting to false will means menus cannot be dragged around
            menuOrientation: 'horizontal',       // menu alignment (horizontal,vertical)
            menuOffsetLeft:  5,                  // left offset of primary menu
            menuOffsetTop:   5
        });




        $('.files-container').on('click','.image-attachments',function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            $("#wPaint").wPaint('clear');
            var canvas=$('.wPaint-canvas-bg').get(0);
            var drawCanvas=$('.wPaint-canvas').get(0);

            var ctx=canvas.getContext("2d");

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            var img=new Image();
            img.onload=function(){
                var x, y, w, h,xR,yR,ratio,
                    initWidth = $(window).width() > 800 ? 800 : $(window).width(),
                    initHeight = $(window).height() > 500 ? 500 : $(window).height();

                if (img.width > initWidth || img.height > initHeight) {
                    xR = initWidth / img.width;
                    yR = initHeight / img.height;

                    ratio = xR < yR ? xR : yR;

                    w = img.width * ratio;
                    h = img.height * ratio;
                }

                // get left/top (centering)
                x = (initWidth - w) / 2;
                y = (initHeight - h) / 2;
                drawCanvas.width = w;
                drawCanvas.height = h;
                $(drawCanvas).css({
                    top:y,
                    left:x
                });



                ctx.drawImage(img,x, y, w, h);

            }
            img.src=url;


            $('#editImage .new-window-open').attr('data-url', url);
            $('#editImage .update-image').attr('data-id', $(this).attr('data-id'));

            $('#editImage').modal('show');
        });
        $('.new-window-open').on('click', function(){
            window.open($(this).attr('data-url'));
        });
        $('.update-image').on('click', function(){
            var base64 = $('.wPaint-canvas').get(0).toDataURL();
            $('#editImage').modal('hide');
            $('#preloaderModal').modal('show');
            $.ajax({
                type:'post',
                url:utils.baseUrl() + 'search/update/'+$(this).attr('data-id'),
                data: base64,
                success: function(){
                    window.location.reload();
                },
                error: function(e){
                    alert('Internal server error');
                    console.log(e);
                }
            });
        });
    }




    $('.job-details-table [data-has-variation-relation] .form-control').on('change', function(e){
        var relation = $(this).parents('td').attr('data-has-variation-relation'),
            relationElement = $('[name="data['+relation+']"]'),
            activeTabId = $('.view-tab-header').find('li.active').attr('data-id');

        if(relationElement.length){
            if($(this).val() != relationElement.val()){
                relationElement.parents('td').addClass('bg-warning');
                $(this).parents('td').addClass('bg-warning');
            }else{
                relationElement.parents('td').removeClass('bg-warning');
                $(this).parents('td').removeClass('bg-warning');
            }
            recalcBadges();
            recalcBadges($('.view-tab-header').find('li[data-id="'+(activeTabId-1)+'"]'));
        }
    });

    $('.job-details-table [data-has-actual-relation] .form-control').on('change', function(e){
        var relation = $(this).parents('td').attr('data-has-actual-relation'),
            relationElement = $('[name="data['+relation+']"]'),
            activeTabId = $('.view-tab-header').find('li.active').attr('data-id');

        if(relationElement.length){
            if($(this).val() != relationElement.val()){
                relationElement.parents('td').addClass('bg-warning');
                $(this).parents('td').addClass('bg-warning');
            }else{
                relationElement.parents('td').removeClass('bg-warning');
                $(this).parents('td').removeClass('bg-warning');
            }
            recalcBadges();
            recalcBadges($('.view-tab-header').find('li[data-id="'+(parseInt(activeTabId,10)+1)+'"]'));
        }
    });

//    logic for non stardard variations
    $('#data-181, #data-209, #data-255').on('change', function(){
        var additionalVal    = $('#data-255').val() && parseInt($('#data-255').val(),10) || 0,
            actuallVal       = $('#data-209').val() && parseInt($('#data-209').val(),10) || 0,
            variationVal     = $('#data-181').val() && parseInt($('#data-181').val(),10) || 0,
            additionalParent = $('#data-255').parents('td').first(),
            actualParent     = $('#data-209').parents('td').first(),
            variationParent  = $('#data-181').parents('td').first();
        //highlight all
        if(additionalVal && (additionalVal > actuallVal || additionalVal > variationVal)){
            $([variationParent,actualParent,additionalParent]).each(function(){
                $(this).addClass('bg-warning');
            });
        }else{
            $([variationParent,actualParent,additionalParent]).each(function(){
                $(this).removeClass('bg-warning');
            });
        }

        recalcBadges($('.view-tab-header').find('li[data-id="3"]'));
        recalcBadges($('.view-tab-header').find('li[data-id="4"]'));
        recalcBadges($('.view-tab-header').find('li[data-id="5"]'));
    });

    recalcBadges($('.view-tab-header').find('li[data-id="3"]'));
    recalcBadges($('.view-tab-header').find('li[data-id="4"]'));

    function recalcBadges(tab){
        tab = tab || $('.view-tab-header').find('li.active');
        var tabId = tab.attr('data-id'),
            container = $('.panel-body[data-id="'+tabId+'"]'),
            items = container.find('.bg-danger, .bg-warning');

        if(tab.find('a .badge').length){
            items.length ?
                tab.find('a .badge').text(items.length):
                tab.find('a .badge').remove();
        }else{
            if(items.length){
                tab.find('a').html(tab.find('a').html() + '<span class="badge">'+items.length+'</span>');
            }
        }
    }



    var historyStateCount = 0;

    (function refreshRoute(){
        historyStateCount = 0;
        var path = window.location.hash;
        $('.refreshClick[href="'+path+'"]').trigger('click');
    })();


    $('.refreshClick').on('click',function(){
        historyStateCount++;
    })


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
            if(utils.isArray(rawData[ticketId][cellId])){
                rawData[ticketId][cellId] = rawData[ticketId][cellId].join(', ')
            }
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

    function handleScrollOnLifdReport(){
        $(window).on('scroll')
    }
    initPlugins();
});
