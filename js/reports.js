$(function () {
    if(!window.REPORTDATA){
        return;
    }
    window.REPORTDATA = window.REPORTDATA || {}
    window.REPORTDATA.allocation = {
        colorByType:{
            'TESTED': '#0EAD00',
            'BUILT': '#64F257',
            'HELD-CONTRACTOR':'#E07F00',
            'IN-PROGRESS':'#E0A500',
            'SCHEDULED': '#E0D100',
            'DEFERRED': '#ED7476',
            'DIRTY': '#EB1014',
            'HEC': '#FC5356',
            'HELD-NBN': '#FA6466'
        },
        order:[
            'TESTED',
            'BUILT',
            'HELD-CONTRACTOR',
            'IN-PROGRESS',
            'SCHEDULED',
            'DEFERRED',
            'DIRTY',
            'HEC',
            'HELD-NBN'
        ]
    }

    Highcharts.setOptions({
        colors:[
            '#0EAD00',
            '#64F257',
            '#E07F00',
            '#E0A500',
            '#E0D100',
            '#ED7476',
            '#EB1014',
            '#FC5356',
            '#FA6466'
        ],
        global: {
            useUTC: false
        }
    });

    function dashboardHandlers(){
        $('#report-container').on('click','.switcher',function(e){
            var id = $(this).attr('href').replace('#','');
            $('#report-container .tab-pane.active').removeClass('active');
            $('.selected_switcher').removeClass('selected_switcher');
            $(this).addClass('selected_switcher');
            $('[data-id="'+id+'"]').addClass('active');
            refreshTabData(id);
        });
        $('#dashboard-report-form').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize(),
                serialized = $(this).serializeArray(),
                id = $('.selected_switcher').attr('href').replace('#','');
            $('#filterModal').modal('hide');
            $('#preloaderModal').modal('show');
            if (data) {
                $('label.no-filters').hide();
                $('#clear-filters').removeClass('hidden');
                var filters = [],
                    existentName = [],
                    text;
                for (var i in serialized){
                    text = [];
                    if(existentName.indexOf(serialized[i].name) != -1){
                        continue;
                    }
                    existentName.push(serialized[i].name);
                    $(this).find('[name="'+serialized[i].name+'"]').find(':selected').each(function(){
                        text.push($(this).text());
                    })
                    filters.push(
                        '<span class="filter-item">' ,
                        serialized[i].name.replace('[]','').toUpperCase() ,
                        ': <label class="filter_value">' ,
                        text ,
                        '</label></span>');
                }

                $('div.text-info-filters>div').html(filters.join(''));

            } else {
                $('div.text-info-filters>div').html('<span class="filter-item"> <label class="filter_value">Empty</label></span>');
                $('label.no-filters').show();
                $('#clear-filters').addClass('hidden');
            }

            window.REPORTDATA.filterParams = data;

            refreshTabData(id);
            return false;
        });

        $('.clear-filters').on('click',function(e){
            e.preventDefault();
            $(this).parents('form').first()[0].reset();
            $('#filterModal .multiselect').multiselect('rebuild');
            $(this).parents('form').trigger('submit');

        })

    }

    (function(){
        var id = window.location.hash.replace('#','');
        if(id){
            $('#report-container .tab-pane.active').removeClass('active');
            $('[data-id="'+id+'"]').addClass('active');
            $('.sidebar .active').removeClass('active');
            $('.sidebar [data-id="'+id+'"]').addClass('active');
        }
        refreshTabData(id);
    })();

    function handleCompanyTab(){
        var start = $('#start-company').val(),
            end = $('#end-company').val();
        getAllTicketsByCompanies(start,end).then(function(data){
            showAllTickets(data);
            showTicketsByCompanies(data);
            $('#preloaderModal').modal('hide');
        });
    }

    function handleOverviewTab(){
        var start = $('#start-overview').val(),
            end = $('#end-overview').val();
        getAllStatuses(start, end).then(function(data){
            showAllAssignedTickets(data);
            $('#preloaderModal').modal('hide');
        });
    }

    function handleTimeTab(format){
        var start = $('#start-time').val(),
            end = $('#end-time').val();
        getHistoryChanges(format, start, end).then(function(data){
            showHistoryProgress(data,utils.dateRangeFormats[format])
            $('#preloaderModal').modal('hide');
        });
    }

    function handleStackedTab(){
        var start = $('#start-stacked').val(),
            end = $('#end-stacked').val();
        getAllTicketsByCompanies(start, end).then(function(data){
            showTicketsInStacked(data);
            $('#preloaderModal').modal('hide');
        });
    }

    function handleFsaTab(){
        var start = $('#start-fsa').val(),
            end = $('#end-fsa').val();
        getAllFSAStatuses(start,end).then(function(data){
            showFSADrillDown(data);
            $('#preloaderModal').modal('hide');
        });
    }



    function refreshTabData(id){
        $('#preloaderModal').modal('show');
        switch (id){
            case 'company':
                handleCompanyTab();
                $('#company-report').off('submit').on('submit',function(e){
                    e.preventDefault();
                    handleCompanyTab();
                });
                break;
            case 'time':
                handleTimeTab($('.history-container .active').attr('data-attr'));
                $('#time-report').off('submit').on('submit',function(e){
                    e.preventDefault();
                    handleTimeTab($('.history-container .active').attr('data-attr'));
                });
                break;
            case 'stacked':
                handleStackedTab();
                $('#stacked-report').off('submit').on('submit',function(e){
                    e.preventDefault();
                    handleStackedTab();
                });
                break;
            case 'fsa-fsam':
                handleFsaTab();
                $('#fsa-report').off('submit').on('submit',function(e){
                    e.preventDefault();
                    handleFsaTab();
                });
                break;

            default:
                handleOverviewTab();
                $('#overview-report').off('submit').on('submit',function(e){
                    e.preventDefault();
                    handleOverviewTab();
                });
                break;
        }

    }

    function uppStatuses(statuses){
        var res = {},
            i;
        for(i in statuses){
            res[i.toUpperCase()] =statuses[i]
        }
        return res;
    }

    function uppTickets(tickets){
        var res = {},
            i;
        for(i in tickets){
            res[i] = {};
            for (j in tickets[i]){
                res[i][j.toUpperCase()] = tickets[i][j];
            }
        }
        return res;
    }

    function showFSAMChart(fsa){
        var def = $.Deferred();
        var start = $('#start-fsa').val(),
            end = $('#end-fsa').val();
        getFSAMStatus(fsa,start, end).then(function(data){
            var series = [],
                name,
                i,
                rawData = {},
                data,
                upperTickets = uppTickets(data);

            for(i in window.REPORTDATA.allocation.order){
                name = window.REPORTDATA.allocation.order[i];
                data = [];
                categories = [];

                for(j in upperTickets){
                    categories.push(j);
                    data.push({
                        y: upperTickets[j][name] || 0,
                        name: name,
                        color:  window.REPORTDATA.allocation.colorByType[name]
                    });
                }
                rawData[name] = data;
            }

            for(i in window.REPORTDATA.allocation.order){
                name = window.REPORTDATA.allocation.order[i];
                series.push({
                    name: name,
                    data: rawData[name]
                })
            }
            $('.fsam-statuses').slideDown(300,function(){
                $('html, body').animate({ scrollTop: $('#fsam-statuses').offset().top-50}, 300);
            });



            $('#fsam-statuses').highcharts({
                chart: {
                    type: 'column'
                },
                title: {
                    text: fsa
                },
                xAxis: {
                    type: 'category',
                    categories:categories
                },

                legend: {
                    enabled: true
                },


                plotOptions: {
                    series: {
                        borderWidth: 0,
                        stacking: 'normal'
                    }
                },

                series: series
            })

            def.resolve();

        });
        return def.promise();
    }

    function showFSADrillDown(allFSAStatuses){
        var series = [],
            name,
            i, d,
            rawData = {},
            upperTickets = uppTickets(allFSAStatuses),
            drilldowns = {},
            drilldown = [],
            data;

        for(i in window.REPORTDATA.allocation.order){
            name = window.REPORTDATA.allocation.order[i];
            data = [];
            categories = [];

            for(j in upperTickets){
                categories.push(j);
                data.push({
                    y: upperTickets[j][name] || 0,
                    name: name,
                    color:  window.REPORTDATA.allocation.colorByType[name]
                });
            }
            rawData[name] = data;
        }

        for(i in window.REPORTDATA.allocation.order){
            name = window.REPORTDATA.allocation.order[i];
            series.push({
                name: name,
                data: rawData[name]
            })
        }

        for(i in series){
            for (j in series[i].data){
                d = series[i].data[j].drilldown;
                drilldowns[d] = drilldowns[d] ||  {
                    id:d,
                    name: d,
                    data:[]
                };

                drilldowns[d].data.push({
                    name:series[i].data[j].name,
                    y: series[i].data[j].y,
                    color: window.REPORTDATA.allocation.colorByType[series[i].data[j].name]
                });
            }
        }

        drilldown = [];
        for(i in drilldowns){
            drilldown.push(drilldowns[i]);
        }

        $('#fsa-statuses').highcharts({
            chart: {
                type: 'column',
                events: {
                    drilldown: function (e) {
                        var chart = this;
                        chart.showLoading('Loading FSAM progress ...');
                        showFSAMChart(e.point.drilldown).then(function(){
                            chart.hideLoading();
                        });
                    },
                    drillup: function(e){
                        $('.fsam-statuses').slideUp();
                    }
                }
            },
            title: {
                text: 'FSA progress'
            },
            subtitle: {
                text: '(click on chart to see details)'
            },
            xAxis: {
                type: 'category',
                categories:categories
            },

            legend: {
                enabled: false
            },


            plotOptions: {
                series: {
                    borderWidth: 0,
                    stacking: 'normal',
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                                var chart = this;
                                showFSAMChart(chart.category).then(function(){
                                });
                            }
                        }
                    }
                }
            },

            series: series,
            drilldown: {
                series: drilldown
            }
        })
    }

    function showTicketsInStacked(tickets){
        var i, j, k, name, currName,
            data,
            rawData = {},
            upperTickets = uppTickets(tickets.companies),
            categories,
            series = [];

        for(i in window.REPORTDATA.allocation.order){
            name = window.REPORTDATA.allocation.order[i];
            data = [];
            categories = [];
            for(j in upperTickets){
                categories.push(j);
                data.push(upperTickets[j][name] || 0);
            }
            rawData[name] = data;
        }

        for(i in window.REPORTDATA.allocation.order){
            name = window.REPORTDATA.allocation.order[i]
            series.push({
                name: name,
                data: rawData[name],
                color:  window.REPORTDATA.allocation.colorByType[name]
            })

        }

        $('#tickets-stacked').highcharts({
            chart: {
                type: 'column',
                zoomType: 'x,y'
            },
            title: {
                text: 'Tickets allocation'
            },
            xAxis: {
                categories: categories
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total tickets'
                },
                stackLabels: {
                    enabled: false,
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                    }
                }
            },
            legend: {
               enabled:true
            },
            tooltip: {
                formatter: function () {
                    return '<b>' + this.x + '</b><br/>' +
                        this.series.name + ': ' + this.y + '<br/>' +
                        'Total: ' + this.point.stackTotal;
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal'
                }
            },
            series: series
        });
    }

    function showHistoryProgress(statuses,interval){
        var i, j, name,
            chartContainer,
            rawSeries={},
            tickInterval =  interval && interval.interval|| 1 * 24 * 3600 * 1000, // one day
            startOf = interval && interval.start || 'month',
            series = [];

        for(i in statuses){
            for(j in statuses[i]){
                name = j;
                rawSeries[name] = rawSeries[name] || {
                    data:[],
                    name:'',
                    color:window.REPORTDATA.allocation.colorByType[name.toUpperCase()]};
                rawSeries[name].data.push([moment.unix(i).startOf(startOf).valueOf(),statuses[i][j]]);
            }
        }

        for(i in rawSeries){
            rawSeries[i].name = i;
            series.push(rawSeries[i]);
        }


        $('#history-block').highcharts({

            title: {
                text: 'Status progress'
            },
            chart: {
                zoomType: 'x'
            },
            xAxis: {
                type: 'datetime',
//                tickPositioner: function () {
//                    var positions = [],
//                        tick = Math.floor(this.dataMin),
//                        increment = Math.ceil((this.dataMax - this.dataMin) / 2);
//
//                    for (; tick - increment <= this.dataMax; tick += increment) {
//                        positions.push(moment(tick).toDate());
//                    }
//                    console.log(positions);
//                    return positions;
//                }
            },

            yAxis: {
                min: 0,
                title: {
                    text: 'Total tickets'
                }
            },
            tooltip: {
                shared:true
            },

            legend: {
               enabled:true
            },

            series: series
        });
    };
    function showTicketsByCompanies(tickets){
        var i, j, name,
            chartContainer,
            rawSeries,
            series;
        $('#tickets-companies').html('');
        for(i in tickets.companies){
            chartContainer = $('<div class="width-1-3 height-300"></div>');
            series = [];
            rawSeries = {};
            for(j in tickets.companies[i]){
                name = j.toUpperCase();
                rawSeries[name] = tickets.companies[i][j];
            }
            for(j in window.REPORTDATA.allocation.order){
                name = window.REPORTDATA.allocation.order[j].toUpperCase();
                if(rawSeries[name]){
                    series.push({
                        y:rawSeries[name],
                        name: name,
                        color: window.REPORTDATA.allocation.colorByType[name]
                    });
                }
            }


            $('#tickets-companies').append(chartContainer);

            $(chartContainer).highcharts({
                chart: {
                    type: 'pie'
                },
                title: {
                    text: i
                },
                tooltip: {
                    pointFormat: '<b>{series.name}</b>: {point.y}(<b>{point.percentage:.1f}%</b>)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        showInLegend: false,
                        dataLabels: {
                            enabled: false
                        }
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
        };
    }
    function showAllTickets(tickets){
        var total = {}, i, j, name,
            series=[];

        total = uppStatuses(tickets.total);
        for(i in window.REPORTDATA.allocation.order){
            name = window.REPORTDATA.allocation.order[i]
            if(total[name]){
                series.push({
                    name: name,
                    y: total[name],
                    color: window.REPORTDATA.allocation.colorByType[name]
                })
            }
        }

        $('#pie-total-tickets').highcharts({
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Tickets allocation by companies'
            },
            tooltip: {
                pointFormat: '<b>{series.name}</b>: {point.y}(<b>{point.percentage:.1f}%</b>)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
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



    function showAllAssignedTickets(tickets){
        var total = {}, i, j, name,
            categories = [],
            statuses = uppStatuses(tickets),
            series=[];
        for(i in window.REPORTDATA.allocation.order){
            name = window.REPORTDATA.allocation.order[i]
            if(statuses[name]){
                categories.push(name);

                series.push({
                    y:statuses[name],
                    color: window.REPORTDATA.allocation.colorByType[name]
                })
            }
        }

        $('#pie-total-tickets-assigned').highcharts({
            chart: {
                type: 'column',
                zoomType: 'x,y'
            },
            xAxis: {
                categories: categories
            },
            title: {
                text: 'All ticket statuses'
            },
            plotOptions: {
                column: {
                    allowPointSelect: true,
                    showInLegend: false
                }
            },
            legend:{
                enabled:false
            },
            exporting: {
                enabled: true
            },
            series: [{
                data: series
            }]
        });
    }

    function getAllStatuses(start,end){
        return $.ajax({
            url:[utils.baseUrl(),
                "dashboard/api?",
                (start ? 'start='+start : ''),
                (end ? 'end='+end : ''),
                (window.REPORTDATA.filterParams ? '&'+window.REPORTDATA.filterParams : '')
            ].join(''),
            type:'get',
            dataType:'JSON'
        })
    }
    function getAllFSAStatuses(start, end){
        return $.ajax({
            url:[utils.baseUrl(),
                "dashboard/api?type=fsa",
                (start ? '&start='+start : ''),
                (end ? '&end='+end : ''),
                (window.REPORTDATA.filterParams ? '&'+window.REPORTDATA.filterParams : '')
            ].join(''),
            type:'get',
            dataType:'JSON'
        })
    }
    function getHistoryChanges(format,start, end){
        if(!format){
            //montly by default
            format = window.localStorage && window.localStorage['_history_format_'] || 'm';
        }
        return $.ajax({
            url:[utils.baseUrl() , "dashboard/api?sep=" ,format,
                (start ? '&start='+start : ''),
                (end ? '&end='+end : ''),
                (window.REPORTDATA.filterParams ? '&'+window.REPORTDATA.filterParams : '')
            ].join(''),
            type:'get',
            dataType:'JSON'
        })
    }

    function getFSAMStatus(fsa, start, end ){
        return $.ajax({
            url:[utils.baseUrl(), "dashboard/api?type=fsam&fsa=",fsa,
                (start ? '&start='+start : ''),
                (end ? '&end='+end : ''),
                (window.REPORTDATA.filterParams ? '&'+window.REPORTDATA.filterParams : '')
            ].join(''),
            type:'get',
            dataType:'JSON'
        })
    }
    function getAllTicketsByCompanies(start,end){
        return $.ajax({
            url:[
                utils.baseUrl(),
                "dashboard/api?type=companies",
                (start ? '&start='+start : ''),
                (end ? '&end='+end : ''),
                (window.REPORTDATA.filterParams ? '&'+window.REPORTDATA.filterParams : '')
            ].join(''),
            type:'get',
            dataType:'JSON'
        })
    }

    function initExpandCollapse(){
        $('.charts-expand').find('.do-expand').on('click',function(e){
            var parent = $(this).parents('.report-block').first();
            $(this).addClass('hidden');
            parent.find('.do-collapse').removeClass('hidden');
            parent.find('.chart-list-container').slideDown();
        });
        $('.charts-expand').find('.do-collapse').on('click',function(e){
            var parent = $(this).parents('.report-block').first();
            $(this).addClass('hidden');
            parent.find('.do-expand').removeClass('hidden');
            parent.find('.chart-list-container').slideUp();
        })
    }
    function initDateRangeSwitcher(){
        $('.history-container').on('click','button[data-attr]', function(e){
            var self = this;
            var start = $('#start-time').val(),
                end = $('#end-time').val();
            e.preventDefault();
            $('#preloaderModal').modal('show');

            $('.history-container').find('.active').removeClass('active');
            $(self).addClass('active');
            getHistoryChanges($(self).attr('data-attr'),start, end).then(function(data){
                showHistoryProgress(data,utils.dateRangeFormats[$(self).attr('data-attr')]);
                $('#preloaderModal').modal('hide');
            });

        });
    }


    dashboardHandlers();
    initExpandCollapse();
    initDateRangeSwitcher();



});
