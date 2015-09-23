window.form = (function() {
    var formBuilder = {
        init: function(){
            this.setHandlers();
            this.editorEvents();
            $('.add-row').trigger('click');
        },
        focusedField : false,
        setHandlers: function(){
            var self = this,
                rowTemplate = $('[data-template-row]'),
                valTemplate = $('[data-template-value]');


            $('.add-row').on('click', function(){
                var row = rowTemplate.clone();
                row.show();
                $('.form-container').append(row);
            });

            $('.add-line').on('click', function(){
                var row = '<hr><button class="tmp-gen remove-hr tmp-gen btn btn-danger"> - </button>'
                $('.form-container').append(row);
            });

            $('.form-container').on('click','.add-value', function(){
                var val = valTemplate.clone().html();
                $(this).before(val);
            });


            $('.form-container').on('click','.value', function(){
                self.focusedField = $(this),
                self.changeConfigContext($(this));
            });

            $('.form-container').on('click','.remove-field',function(){
                $(this).parent().remove();
                $('.fields-config').hide();
            });
            $('.form-container').on('click','.remove-line',function(){
                if(confirm('Remove row ?')){
                    $(this).parents('.form-row').first().remove();
                }
            });
            $('.form-container').on('click','.remove-hr',function(){
                $(this).prev().remove();
                $(this).remove();
            });



            $('.save-form').on('click', function(){
                if(confirm('Generate form?')){
                    self.sendForm();
                }
            });
        },

        sendForm: function(){
            var json = this.createJson();
            this.buildFormByJson('#newFormContainer',json);

//            return $.ajax({
//                url : utils.baseUrl() + 'form/generate',
//                type: 'POST',
//                data: JSON.stringify(json),
//                success: function(){
//                    alert('Posted');
//                }
//            });
        },

        buildFormByJson: function(container, json){
            console.log(json);
            var html = [],
                el,
                htmlContainer,
                i, j, l, k;

            for(i = 0;i<json.length;i++){
                if(typeof(json[i]) == 'string'){
                    html.push('<hr>');
                }else{
                    html.push('<div class="form-row">');
                    for(j = 0;j<json[i].length;j++){
                        el = json[i][j];
                        switch (el.type) {
                            case 'text':
                                html.push(
                                    '<div class="form-block">',
                                        '<div class="value" data-type="text" data-value="" data-placeholder="',
                                            el.placeholder,
                                            '">',
                                            '<input name="',
                                             el.name,
                                            '" type="text" value="',
                                                el.value,
                                                '" placeholder="',
                                            el.placeholder,
                                            '">',
                                        '</div>',
                                    '</div>');
                                break;
                            case 'label':
                                html.push('<div class="form-block">',
                                    '<div class="value" data-type="label" data-value="123123" data-placeholder="">',
                                    '<span class="tmp-label">',
                                    el.value,
                                    '</span>',
                                    '</div>');
                                break;
                            case 'date':
                                html.push(
                                    '<div class="form-block">',
                                        '<div class="value" data-type="date" data-value="" data-placeholder="',
                                            el.placeholder,
                                        '">',
                                        '<input class="datepicker" name="',
                                            el.name,
                                            '" type="text" value="',
                                            el.value,
                                            '" placeholder="',
                                            el.placeholder,
                                            '">',
                                        '</div>',

                                    '</div>');
                                break;
                            case 'canvas':
                                html.push(
                                    '<div class="form-block">',
                                        '<div class="value" data-type="date" data-value="" data-placeholder="',
                                        el.placeholder,
                                        '">',
                                            '<canvas name="',
                                            el.name,
                                            '" class="panel panel-default" width="150" height="25"></canvas>',
                                        '</div>',
                                    '</div>');
                                break;
                            case 'ticket':
                                html.push(
                                    '<div class="form-block">',
                                    '<div class="value" data-type="ticket" data-value="" data-placeholder="" data-field-id="',
                                    el.fieldId,
                                    '">',
                                    el.value || '',
                                    '</div>',
                                    '</div>');
                                break;
                            case 'select':
                                var options = [];
                                el.values.each(function(){
                                    options.push('<option value="'+this+'">'+this+'</option>');
                                });
                                html.push(
                                    '<div class="form-block">',
                                        '<div class="value" data-type="date" data-value="" data-placeholder="',
                                        el.placeholder,
                                        '">',
                                            '<select class="" name="',
                                            el.name,
                                            '" ',
                                            el.multiple ? 'multiple="multiple"' : '',
                                            ' >',
                                                options.join(''),
                                            '</select>',
                                        '</div>',
                                    '</div>');
                                break;
                        }
                    }
                }
                html.push('</div>');
            }

            $(container).html(html.join('')).show().addClass('submited');
            this.initPluginsOnBuiltForm();
        },

        createJson: function(){
            var items = $('.form-container').find('.form-row'),
                i,
                vals,
                row = [],
                valObject,
                rowObjects;
            items.each(function(){
                rowObjects = [];
                vals = $(this).find('.value');
                vals.each(function(){
                    valObject = {};
                    switch ($(this).attr('data-type')) {
                        case 'text':
                            valObject = {
                                type:'text',
                                placeholder: $(this).attr('data-placeholder'),
                                    value: $(this).attr('data-value'),
                                    name: $(this).find('input[type="text"]').attr('name')
                            };
                            break;
                        case 'label':
                            valObject = {
                                type:'label',
                                value: $(this).attr('data-value')
                            };
                            break;
                        case 'date':
                            valObject = {
                                type:'date',
                                placeholder: $(this).attr('data-placeholder'),
                                value: $(this).find('input[type="text"]').val(),
                                name: $(this).find('input[type="text"]').attr('name')
                            };
                            break;
                        case 'canvas':
                            valObject = {
                                type:'canvas',
                                name: $(this).find('canvas').attr('name')
                            };
                            break;
                        case 'ticket':
                            valObject = {
                                type:'ticket',
                                fieldId: $(this).attr('data-field-id')
                            };
                            break;
                        case 'select':
                            valObject = {
                                type:'select',
                                multiple: !!$(this).find('select').attr('multiple'),
                                values: $(this).find('select').find('option').map(function(){
                                    return $(this).text();
                                }),
                                name: $(this).find('select').attr('name')
                            };

                            break;
                    }
                    rowObjects.push(valObject);
                    });
                row.push(rowObjects);
                if($(this).next()[0] && $(this).next()[0].nodeName == 'HR'){
                    row.push('<hr>');
                }
            });
            return(row);
        },
        editorEvents: function(){
            var self = this;
            $('.field-type-select').on('change', $.proxy(this.updateField, this));
            $('.config-value-container').find('input').on('keyup', $.proxy(this.updateField, this));
            $('.field-placeholder').on('change', $.proxy(this.updateField, this));
            $('.field-width-select').on('change', $.proxy(this.updateField, this));

            $('.add-option').on('click', function(){
                var val = $('.select-option').val();
                $('.available-options-select').append('<option value="val">'+val+'</option>');
                $('.select-option').val('');
                $('.apply-option').trigger('click');
            });

            $('.apply-option').on('click', function(){
                var select = $('.available-options-select').clone().removeClass('available-options-select');
//                $('.available-options-select').html('');
                $('.select-option').val('');
                select.attr('name',self.guid());
                if($('.multiselect-option').is(':checked')){
                    select.attr('multiple','multiple');
                }

                $('.value.selected').html(select);
                $(select).multiselect('refresh');
            });


        },
        changeConfigContext: function(field){
            var type        = $(field).attr('data-type') || 'label',
                value       = $(field).attr('data-value'),
                placeholder = $(field).attr('data-placeholder'),
                width       = $(field).attr('data-width') || 10;

            if(type != 'ticket'){
                $('.ticket-type-select').hide();
            }else{
                $('.ticket-type-select').show();
            }

            $('.field-type-select').val(type);
            $('.field-type-select').multiselect('refresh');
            $('.config-value-container').find('input').val(value).focus();
            $('.field-placeholder').val(placeholder);
            $('.field-width-select').val(width);


            $('.selected').removeClass('selected');
            $(field).addClass('selected');

            $('.fields-config .placeholder-container').hide();
            $('.fields-config').show();

            this.updateField();
        },
        updateField: function(){
            var self = this,
                field = self.focusedField,
                type  = $('.field-type-select').val(),
                value = $('.config-value-container').find('input').val(),
                placeholder = $('.field-placeholder').val(),
                width = $('.field-width-select').val();

            $('.ticket-type-select').hide();
            $('.fields-config .config-select-container').hide();
            $('.fields-config .placeholder-container').hide();
            $('.fields-config .value-container').hide();

            $(field).attr('data-type',type);

            switch (type) {
                case 'text':
                    $(field).attr('data-value',value);
                    $(field).html('<input name="'+self.guid()+'" type="text" value="'+value+'" placeholder="'+placeholder+'"/>');
                    $('.fields-config .placeholder-container').show();
                    $('.fields-config .value-container').show();
                    break;
                case 'label':
                    $(field).attr('data-value',value);
                    $(field).html('<span class="tmp-label">'+value+'</span>');
                    $('.fields-config .value-container').show();
                    break;
                case 'date':
                    $(field).attr('data-value','');
                    $(field).html('<input name="'+self.guid()+'"  type="text" class="datepicker" value="" placeholder="'+placeholder+'"/>');
                    $('.fields-config .placeholder-container').show();
                    $(field).find('input[type="text"]').attr('data-placeholder',placeholder);
                    break;
                case 'canvas':
                    var guid = self.guid();
                    $(field).html('<canvas name="' + guid + '" class="panel panel-default" width="150" height="25"></canvas><input type="hidden" data-name="'+guid+'"/>');
                    break;
                case 'ticket':
                    self.handleTicketField($(field));
                    break;
                case 'select':
                    if($(field).find('select').length){
                        $('.available-options-select').html($(field).find('select').html());
                    }
                    else{
                        $('.available-options-select').html('');
                    }
                    $('.fields-config .config-select-container').show();
                    break;
                default:
                    $(field).attr('data-value',value);
                    $(field).html(value);
                    break
            }

            self.initPlugins();
            $(field).attr('data-placeholder',placeholder);
            $(field).attr('data-width',width);
        },

        handleTicketField: function(field){
            var html = [], i,
                select = $('.ticket-input-select select'),
                value = field.attr('data-field-id'),
                text;
            $('.ticket-input-select select').html('');

            getColumns().then(function(data){
                for(i in data){
                    html.push(
                        '<option value="',
                        data[i].id,
                        '">',
                        data[i].name,
                        '</option>');
                }
                $('.ticket-input-select select').html(html.join(''));
                $('.ticket-input-select select').selectize();
                if(value){
                    $('.ticket-input-select select').val(value);
                    text = $('.ticket-input-select select').find('option:selected').text();
                    $('.selectize-input').find('.item').text(text);
                }



                $('.ticket-type-select').show();
                select.off().on('change', function(e){
                    var fieldId = $(this).val(),
                        label = $(this).find(":selected").text();
                    $(field).html(label).attr('data-field-id', fieldId);
                });

                var fieldId = $(select).val(),
                    label = $(select).find(":selected").text();
                $(field).html(label).attr('data-field-id', fieldId);
            });

            $(field).html('ticket value');
        },

        initPlugins: function(){
            if($('canvas').length){
                var signature = new SignaturePad(document.querySelector('canvas'));
            }
//            $('.datepicker').datepicker({
//                dateFormat: 'dd-mm-yy'
//            });
        },

        initPluginsOnBuiltForm: function(){
            if($('canvas').length){
                var signature = new SignaturePad(document.querySelector('canvas'));
            }
            $('.datepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
            $('select.selectize').selectize();
        },

        guid: function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        }



    };

    function getColumns(){
        return $.ajax({
            url:utils.baseUrl() + 'json/columns',
            type: 'get',
            dataType: 'JSON'
        })
    }

    return formBuilder;
})(window);



