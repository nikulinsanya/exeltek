window.utils = (function() {
    var batchFields = {
        'text': '<div data-id="%ID%" data-type="%TYPE%"><textarea>%VALUE%</textarea></div>',
        'list': '<div data-id="%ID%" data-type="%TYPE%"><select>%OPTIONS%</select></div>'
    };

    var utils = {

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

        getFieldByType: function(value, data, id){
            var j,field,
                html,
                valueFound = !data[id],
                fields = this.batchFields;
            switch (value.type){
                case 'text':
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
                            (data[id] ? (data[id] == value.values[j] ? 'selected' : ''): ''),
                            value.values[j],
                            '">',
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
                default:
            }
            return field;
        }
    };
    return utils;
})(window);


