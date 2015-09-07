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

            $('.save-form').on('click', function(){
                if(confirm('Generate form?')){
                    var orign = $('.form-container');
                    $(orign).find('.tmp-gen').remove();
                    $(orign).find('.selected').removeClass('selected');
                    $('.form-configuration-container').remove();
                    $('.form-btns-container').show();
                }
            });
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
            });

            $('.apply-option').on('click', function(){
                var select = $('.available-options-select').clone().removeClass('available-options-select');
                $('.available-options-select').html('');
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
            $('.config-value-container').find('input').val(value);
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
                    $(field).attr('data-value',value);
                    $(field).html('<input name="'+self.guid()+'"  type="text" class="datepicker" value="'+value+'"/>');
                    $('.fields-config .placeholder-container').show();
                    $(field).find('input[type="text"]').attr('data-placeholder',placeholder);
                    break;
                case 'canvas':
                    var guid = self.guid();
                    $(field).html('<canvas name="' + guid + '" class="panel panel-default" width="200" height="25"></canvas><input type="hidden" data-name="'+guid+'"/>');
                    break;
                case 'ticket':
                    self.handleTicketField($(field));

                    break;
                case 'select':
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
                select = $('.ticket-input-select select');
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
                $('.ticket-type-select').show();
                select.on('change', function(e){
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
            $('.datepicker').datepicker({
                dateFormat: 'dd-mm-yy'
            });
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


$(document).on('ready',function(){
    form.init();

});

