$(function () {
    $('#form-reports').on('change',function(e){
            $.ajax({
                url: utils.baseUrl() +'/reports/forms/search?id='+$(this).val(),
                type:'GET',
                dataType:'JSON',
                success:function(data){
                    var html = [], i, j,
                        list = data.attachments;
                    html.push('<h3>Attachments</h3><table class="table table-bordered table-responsive"><tr>');
                    for(i in list){
                        for(j in list[i]){
                            html.push(
                                '<th>',
                                    j,
                                '</th>'
                            );
                        }
                        html.push('</tr>');
                        break;
                    }

                    for(i in list){
                        html.push('<tr>');
                        for(j in list[i]){
                            html.push(
                                '<td>',
                                list[i][j],
                                '</td>'
                            );
                        }
                        html.push('</tr>');
                    }
                    html.push('</table>');
                    $('#attachments').html(html.join(''));

                    html = [];
                    list = data.data;
                    html.push('<h3>Reports</h3><table class="table table-bordered table-responsive"><tr>');
                    for(i in list){
                        for(j in list[i]){
                            html.push(
                                '<th>',
                                j,
                                '</th>'
                            );
                        }
                        html.push('</tr>');
                        break;
                    }

                    for(i in list){
                        html.push('<tr>');
                        for(j in list[i]){
                            html.push(
                                '<td>',
                                list[i][j],
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