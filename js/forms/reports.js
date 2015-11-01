$(function () {
    $('#form-reports').on('change',function(e){
            $.ajax({
                url: utils.baseUrl() +'reports/forms/search?id='+$(this).val(),
                type:'GET',
                dataType:'JSON',
                success:function(data){
                    var html = [], i, j,
                        list = data.attachments;
                    html.push('<h3>Reports</h3><table class="table table-bordered table-responsive"><tr><th>Report</th>');
                    for(j in data.columns){
                        html.push(
                            '<th>',
                                data.columns[j].name,
                            '</th>'
                        );
                    }
                    html.push('</tr>');

                    for(i in data.data){
                        html.push('<tr><td>');
                        var id = data.data[i].attachment_id;
                        html.push('<a href="', utils.baseUrl(), 'download/attachment/', id, '">', data.data[i].attachment, '</a>');
                        html.push('</td>');
                        for(j in data.columns){
                            html.push(
                                '<td>',
                                data.data[i][j] == undefined ? '' : data.data[i][j],
                                '</td>'
                            );
                        }
                        html.push('</tr>');
                    }
                    html.push('</table>');
                    $('#reports').html(html.join(''));

                },
                error: function(data){
                    alert('Nothing found');
                }
            });
    });
});