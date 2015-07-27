window.utils = (function() {
    var batchFields = {
        'float': '<div data-id="%ID%" data-type="%TYPE%"><input class="input-float" type="number" step="any" value="%VALUE%"></div>',
        'text': '<div data-id="%ID%" data-type="%TYPE%"><textarea>%VALUE%</textarea></div>',
        'string': '<div data-id="%ID%" data-type="%TYPE%"><input type="text" value="%VALUE%"></div>',
        'date': '<div data-id="%ID%" data-type="%TYPE%"><input class="datepicker" type="text" value="%VALUE%"></div>',
        'list': '<div data-id="%ID%" data-type="%TYPE%"><select>%OPTIONS%</select></div>',
        'multi': '<div data-id="%ID%" data-type="%TYPE%"><select class="multiselect" multiple="multiple">%OPTIONS%</select></div>'
    },
    dateRangeFormats = {
        m:{
            interval: moment.duration(1, 'month').valueOf(),
            start:'month',
            format:'MM,YY'
        },
        d:{
            interval: moment.duration(1, 'day').valueOf(),
            start:'day',
            format:'DD-MM-YY'
        },
        w:{
            interval: moment.duration(1, 'week').valueOf(),
            start:'week',
            format:'DD-MM-YY'
        }
    },


    utils = {

        baseUrl : function(){ return $('body').attr('data-url')},

        searchInListById: function (id, list){
            var i = list.length;
            while(i--){
                if(list[i].id == id){
                    return list[i];
                }
            }
            return false
        },

        batchFields : batchFields,
        dateRangeFormats:dateRangeFormats,

        getFieldByType: function(value, data, id){
            var j,field,
                html,
                multiValues,
                valueFound = !data[id],
                fields = this.batchFields;

            switch (value.type){
                case 'date':
                    field = fields[value.type];
                    field = field.replace('%VALUE%', data[id] || '');
                    field = field.replace('%ID%', id);
                    field = field.replace('%TYPE%', value.type);
                    break;
                case 'float':
                    field = fields[value.type];
                    field = field.replace('%VALUE%', data[id] || '');
                    field = field.replace('%ID%', id);
                    field = field.replace('%TYPE%', value.type);
                    break;
                case 'text':
                    field = fields[value.type];
                    field = field.replace('%VALUE%', data[id] || '');
                    field = field.replace('%ID%', id);
                    field = field.replace('%TYPE%', value.type);
                    break;
                case 'string':
                    field = fields[value.type];
                    field = field.replace('%VALUE%', data[id] || '');
                    field = field.replace('%ID%', id);
                    field = field.replace('%TYPE%', value.type);
                    break;
                case 'list':
                    var options = [];
                    field = fields[value.type];
                    field = field.replace('%ID%', id);
                    field = field.replace('%TYPE%', value.type);

                    j = value.values.length;
                    while(j--){
                        options.unshift(
                            '<option value="',
                            value.values[j],
                            '" ',
                            (data[id] ? (data[id] == value.values[j] ? 'selected' : ''): ''),
                            '>',
                            value.values[j],
                            '</option>'
                        )
                        if(data[id] && data[id] == value.values[j]){
                            valueFound = true;
                        }
                    }
                    if(!valueFound && data[id]){
                        options.unshift('<option value="',
                            data[id],
                            '" selected>',
                            data[id],
                            '</option>')
                    }
                    field = field.replace('%OPTIONS%',options.join(''));

                    break;
                case 'multi':
                    var options = [];
                    field = fields[value.type];
                    field = field.replace('%ID%', id);
                    field = field.replace('%TYPE%', value.type);
                    multiValues = data[id] ? data[id].split(', ') : [];
                    j = value.values.length;
                    while(j--){
                        options.unshift(
                            '<option value="',
                            value.values[j],
                            '" ',
                            (data[id] ? (multiValues.indexOf(value.values[j]) != -1 ? 'selected' : ''): ''),
                            '>',
                            value.values[j],
                            '</option>'
                        )
                    }

                    field = field.replace('%OPTIONS%',options.join(''));

                    break;
                default:
            }
            return field;
        },
        isArray: function(variable){
            return variable instanceof Array;
        }
    };
    return utils;
})(window);


