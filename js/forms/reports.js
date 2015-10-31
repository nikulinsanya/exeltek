$(function () {
    $('#form-reports').on('change',function(e){
            $.ajax({
                url: utils.baseUrl() +'/reports/forms/search?id='+$(this).val(),
                type:'GET',
                dataType:'JSON',
                success:function(data){
                    var html = [], i, j,
                        list = data.attachments;
                    html.push('<div>');
                    for(i in list){
                        html.push('<a class="btn btn-primary" role="button" data-toggle="collapse" href="#block_',
                            i
                            ,'" aria-expanded="false" aria-controls="collapseExample">',
                            i,
                            '</a><div class="collapse" id="block_',
                            i
                            ,'"><div class="well"><ul>');
                        for(j in list[i]){
                            html.push(
                                '<li>',
                                '<b>',
                                j,
                                '</b>: ',
                                list[i][j],
                                '</li>'
                            );
                        }
                        html.push('</ul></div></div>');
                    }
                    html.push('</div>');
                    $('#attachments').html(html.join(''));

                },
                error: function(data){
                    alert('Nothing found');
                }
            });
    });
});