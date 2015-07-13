window.utils = (function() {
    var utils = {
        searchInListById: function (id, list){
            var i = list.length;
            while(i--){
                if(list[i].id == id){
                    return list[i];
                }
            }
            return false
        }
    };
    return utils;
})(window);


