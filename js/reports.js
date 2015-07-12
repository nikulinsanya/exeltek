$(function () {
    var allStatuses = [];


    function initData(){
        var defs = [];
        $('#preloaderModal').modal('show');
        defs.push(
            getAllStatuses(),
            getAllFSAStatuses(),
            getAllFSAMStatuses()
        );

        $.when.apply($, defs).then(function(results){
            var result = Array.prototype.slice.call(arguments);
            var allStatuses = result[0][0];
            var allFSAStatuses = result[0][1];
            var allFSAMStatuses = result[0][2];
            console.log(result);
            debugger;
            $('#preloaderModal').modal('hide');
        });
    }

    function getAllStatuses(callback){
        return $.ajax({
            url:"api",
            type:'get',
            dataType:'JSON'
        })
    }
    function getAllFSAStatuses(callback){
        return $.ajax({
            url:"api?type=fsa",
            type:'get',
            dataType:'JSON'
        })
    }
    function getAllFSAMStatuses(id){
        return $.ajax({
            url:"api?type=fsam",
            type:'get',
            dataType:'JSON'
        })
    }



    initData();










    /*var dateFormat = 'DD-MM-YYYY';
    if(!window.REPORTDATA){
        return false;
    }


    var totalProgress = REPORTDATA.totalTickets,
        companyName = totalProgress.companyName,
        totalTicketsPie =  [{
            name: "Total tickets",
            y: totalProgress.data.total
        },{
            name: "Tested tickets",
            y: totalProgress.data.tested
        },{
            name: "Built tickets",
            y: totalProgress.data.built
        }];

    var historySeries = [{
        name: 'Company 1',
        data: [
            [moment('2014-01-01').valueOf(), 7.0],
            [moment('2014-02-01').valueOf(), 6.9],
            [moment('2014-03-01').valueOf(), 9.5],
            [moment('2014-04-01').valueOf(), 14.5],
            [moment('2014-05-01').valueOf(), 18.2],
            [moment('2014-06-01').valueOf(), 21.5],
            [moment('2014-07-01').valueOf(), 25.2],
            [moment('2014-08-01').valueOf(), 26.5],
            [moment('2014-09-01').valueOf(), 23.3],
            [moment('2014-10-01').valueOf(), 18.3],
            [moment('2014-11-01').valueOf(), 13.9],
            [moment('2014-12-01').valueOf(), 9.6]
        ]
    },
        {
            name: 'Company 2',
            data: [
                [moment('2014-01-01').valueOf(), 17.0],
                [moment('2014-02-01').valueOf(), 16.9],
                [moment('2014-03-01').valueOf(), 19.5],
                [moment('2014-04-01').valueOf(), 15.5],
                [moment('2014-05-01').valueOf(), 28.2],
                [moment('2014-06-01').valueOf(), 23.5],
                [moment('2014-07-01').valueOf(), 20.2],
                [moment('2014-08-01').valueOf(), 16.5],
                [moment('2014-09-01').valueOf(), 22.3],
                [moment('2014-10-01').valueOf(), 28.3],
                [moment('2014-11-01').valueOf(), 23.9],
                [moment('2014-12-01').valueOf(), 29.6]
            ]
        },
        {
            name: 'Company 3',
            data: [
                [moment('2014-01-01').valueOf(), 11.0],
                [moment('2014-02-01').valueOf(), 15.9],
                [moment('2014-03-01').valueOf(), 12.5],
                [moment('2014-04-01').valueOf(), 24.5],
                [moment('2014-05-01').valueOf(), 28.2],
                [moment('2014-06-01').valueOf(), 31.5],
                [moment('2014-07-01').valueOf(), 27.2],
                [moment('2014-08-01').valueOf(), 28.5],
                [moment('2014-09-01').valueOf(), 20.3],
                [moment('2014-10-01').valueOf(), 28.3],
                [moment('2014-11-01').valueOf(), 23.9],
                [moment('2014-12-01').valueOf(), 25.6]
            ]
        }];
    var workerList = ['Johnny', 'Loco', 'John', 'Hankook', 'William'];
    var pieCompanies =   [{
            name: "Company 1",
            y: 56
        }, {
            name: "Company 2",
            y: 24,
            sliced: true,
            selected: true
        }, {
            name: "Company 3",
            y: 10
        }, {
            name: "Company 4",
            y: 4
        }, {
            name: "Company 5",
            y: 1
        }, {
            name: "Company 6",
            y: 3
        }];



    initDashboard();

    function initDashboard(){

        initTotalPieChart(totalTicketsPie);





        initWorkerList(workerList);
        initWorkerChart(workerList);


        var list = [];
        var i = historySeries.length;
        while(i--){
            list.push(historySeries[i].name);
        }

        initCompanyHistoryChart(list);
        initCompanyHistoryList(list);


        var list = [];
        var i = pieCompanies.length;
        while(i--){
            list.push(pieCompanies[i].name);
        }
        initPieList(list);
        initPieChart(list);

    }


    function initTotalPieChart(series){
        $('#pie-total-report').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Total company tickets report ('+companyName+')'
            },
            tooltip: {
                pointFormat: '<b>{series.name}</b>: {point.y}(<b>{point.percentage:.1f}%</b>)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            exporting: {
                enabled: true
            },
            series: [{
                name: "Tickets",
                data: series
            }]
        });
    }





    function initWorkerChart(list){
        var series = [{
            name: 'Total',
            data: [107, 31, 635, 203, 20]
        }, {
            name: 'Tested',
            data: [133, 156, 947, 408, 60]
        }, {
            name: 'Built',
            data: [1052, 954, 1250, 740, 308]
        }],i;

        i = series.length;
        while(i--){
            series[i].data = series[i].data.splice(0,list.length);
        }



        $('#worker-report').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: 'Worker progress'
            },
            xAxis: {
                categories: list,
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Tasks',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: ' tasks'
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: 0,
                y: 40,
                floating: true,
                borderWidth: 1,
                backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                shadow: true
            },
            credits: {
                enabled: false
            },
            exporting: {
                enabled: true
            },
            series: series
        });
    }

    function initWorkerList(list){
        var i = list.length,
            htmlList = [];
        while(i--){
            htmlList.push(
                '<li><input type="checkbox" checked="checked" class="worker-checkbox" data-name="',
                list[i],
                '">',
                    '<span>',
                        list[i],
                    '</span>',
                '</li>'
            );
        }
        $('#worker-list').find('ul').html(htmlList.join(''));

        $('#worker-list').on('click','.worker-checkbox',function(){
            var list = [];
            $('#worker-list').find('input:checked').each(function(el){
                list.push($(this).data('name'));
            });
            initWorkerChart(list);
        });
    }

    function initPieList(list){
        var i = list.length,
            htmlList = [];
        while(i--){
            htmlList.push(
                '<li><input type="checkbox" checked="checked" class="company-checkbox" data-name="',
                list[i],
                '">',
                '<span>',
                list[i],
                '</span>',
                '</li>'
            );
        }
        $('#pie-company-list').find('ul').html(htmlList.join(''));

        $('#pie-company-list').on('click','.company-checkbox',function(){
            var list = [];
            $('#pie-company-list').find('input:checked').each(function(el){
                list.push($(this).data('name'));
            });
            initPieChart(list);
        });
    }

    function initPieChart(list){
        var series = [],
            i = pieCompanies.length;
        while(i--){
            if(list.indexOf(pieCompanies[i].name) != -1){
                series.push(pieCompanies[i]);
            }
        }

        $('#pie-company-report').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Company workers'
            },
            tooltip: {
                pointFormat: '{series.name}: {point.y} workers (<b>{point.percentage:.1f}%</b>)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            exporting: {
                enabled: true
            },
            series: [{
                name: "Brands",
                data: series
            }]
        });
    }


    function initCompanyHistoryList(list){
        var i = list.length,
            htmlList = [];
        while(i--){
            htmlList.push(
                '<li><input type="checkbox" checked="checked" class="company-checkbox" data-name="',
                list[i],
                '">',
                '<span>',
                list[i],
                '</span>',
                '</li>'
            );
        }
        $('#company-list').find('ul').html(htmlList.join(''));

        $('#company-list').on('click','.company-checkbox',function(){
            var list = [];
            $('#company-list').find('input:checked').each(function(el){
                list.push($(this).data('name'));
            });
            initCompanyHistoryChart(list);
        });
    }


    function initCompanyHistoryChart(list){
        var series = [],
            i = historySeries.length;
        while(i--){
            if(list.indexOf(historySeries[i].name) != -1){
                series.push(historySeries[i]);
            }
        }


        $('#company-history-report').highcharts({
            title: {
                text: 'Company progress'
            },

            yAxis: {
                title: {
                    text: 'Total items'
                }
            },
            xAxis: {
                type: 'datetime'
            },
            tooltip: {
                valueSuffix: ' tickets'
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            exporting: {
                enabled: true
            },
            series: series
        });
    }
    */

});
