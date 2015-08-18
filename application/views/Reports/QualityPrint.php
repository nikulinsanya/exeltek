<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>

    <style type="text/css">
        @page { sheet-size: A4; }

        table{
            width: 100%;
            padding: 10px;
        }
        .bold{
            font-weight: bold;
        }
        .top-b{
            border-top: 2px solid #333;
        }
        .top-l td{
            border-bottom: 2px solid #333;
        }
        .small{
            font-size: 12px;
        }
        .pad{
            padding: 10px 0px;
        }
        .thead td{
            border-left: 1px solid #333;
            padding: 10px;
        }

        .thead td:last-child{
            border-right: 1px solid #333;
        }
        .row td{
            padding: 10px;
            border-left: 1px solid #333;
            border-top: 1px solid #333;
        }
        .row td:last-child{
            border-right: 1px solid #333;
        }
        .row td input{
            width: 200px;
        }
        canvas{
            height: 40px;
            width: 300px;
            border:1px solid #ccc;
        }
        .row .noPadd{
            padding: 0px;
            text-align: center;
        }
        input[type="submit"],#clear-canvas{
            height: 40px;
            width: 150px;
            background-color: #32B732;
            border-radius: 3px;
            color: #fff;
            font-size: 18px;
        }
        #clear-canvas{
            background-color: #b70817;
        }
    </style>
</head>

<body>
<div style="position:fixed; left: 0; right: 0; bottom: 0; top: 0;">
    <table>
        <tr style="text-align: right;" class="bold">
            <td colspan="5"> QUALITY INSPECTION SUMMARY - DIRN</td>
        </tr>
        <tr>
            <td class="bold" colspan="5"> PROJECT: MIMA         PROJECT CODE: 7XX</td>
        </tr>
        <tr class="small top-b">
            <td colspan="5">Document Revision: <?=Arr::get($report, 'revision')?></td>
        </tr>
        <tr class="small">
            <td colspan="5"> Revision Date: <?=Arr::get($report, 'revisiondate')?></td>
        </tr>
        <tr class="small">
            <td colspan="5"> Prepared By: <?=Arr::get($report, 'prepared')?></td>
        </tr>

        <tr>
            <td colspan="5" class="bold pad top-b">  PC REQUEST Date:  <?=Arr::get($report, 'requestdate')?>     </td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">  SAM: <?=Arr::path($job, 'data.13')?>     </td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">  DA:  <?=Arr::path($job, 'data.14')?>    </td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">   Inspection Date: <?=Arr::get($report, 'inspectiondate')?>   </td>
        </tr>
        <tr class="thead">
            <td class="bold top-b">  GROUP CODE   </td>
            <td class="bold top-b"> DESCRIPTIONS OF OPERATION(S)</td>
            <td class="bold top-b">  ITRs Completed  </td>
            <td class="bold top-b">  # Defects Identified  </td>
            <td class="bold top-b">  Date to Register  </td>
        </tr>
    <tr class="row">
            <td>  GC 1</td>
            <td> FDH CABINETS AND SPLITTERS INSTALLATION</td>
            <td><?=Arr::path($report, 'itrs.1')?></td>
            <td><?=Arr::path($report, 'defect.1')?></td>
            <td><?=Arr::path($report, 'date.1')?></td>
        </tr>
        <tr class="row">
            <td>   GC 1a</td>
            <td>  FTTN CABINET INSTALLATION</td>
            <td><?=Arr::path($report, 'itrs.1')?></td>
            <td><?=Arr::path($report, 'defect.1')?></td>
            <td><?=Arr::path($report, 'date.1')?></td>
        </tr>

        <tr class="row">
            <td>   GC 2</td>
            <td>   PITS INSTALLATION (PIT)</td>
            <td><?=Arr::path($report, 'itrs.2')?></td>
            <td><?=Arr::path($report, 'defect.2')?></td>
            <td><?=Arr::path($report, 'date.2')?></td>
        </tr>
        <tr class="row">
            <td>   GC 2a</td>
            <td>  NBN EARTHING PIT</td>
            <td><?=Arr::path($report, 'itrs.2')?></td>
            <td><?=Arr::path($report, 'defect.2')?></td>
            <td><?=Arr::path($report, 'date.2')?></td>
        </tr>
        <tr class="row">
            <td>   GC 3</td>
            <td>  (PVC) CONDUIT INSTALLATION</td>
            <td><?=Arr::path($report, 'itrs.3')?></td>
            <td><?=Arr::path($report, 'defect.3')?></td>
            <td><?=Arr::path($report, 'date.3')?></td>
        </tr>
        <tr class="row">
            <td>   GC 3a</td>
            <td>  POWER / EARTHING) CONDUIT INSTALLATION</td>
            <td><?=Arr::path($report, 'itrs.3')?></td>
            <td><?=Arr::path($report, 'defect.3')?></td>
            <td><?=Arr::path($report, 'date.3')?></td>
        </tr>
        <tr class="row">
            <td>   GC 4</td>
            <td>  FIBRE OPTIC HAULING (DSS, LSS, MSS)</td>
            <td><?=Arr::path($report, 'itrs.4')?></td>
            <td><?=Arr::path($report, 'defect.4')?></td>
            <td><?=Arr::path($report, 'date.4')?></td>
        </tr>
        <tr class="row">
            <td>   GC 4a</td>
            <td>   COPPER HAULING</td>
            <td><?=Arr::path($report, 'itrs.4')?></td>
            <td><?=Arr::path($report, 'defect.4')?></td>
            <td><?=Arr::path($report, 'date.4')?></td>
        </tr>
        <tr class="row">
            <td>   GC 5</td>
            <td>   AERIAL CABLE INSTALLATION</td>
            <td>N/A</td>
            <td>N/A</td>
            <td><?=Arr::path($report, 'date.5')?></td>
        </tr>
        <tr class="row">
            <td>   GC 6</td>
            <td>  TYPE 2 ARCHITECTURE (DJL AND LJL)</td>
            <td><?=Arr::path($report, 'itrs.6')?></td>
            <td><?=Arr::path($report, 'defect.6')?></td>
            <td><?=Arr::path($report, 'date.6')?></td>
        </tr>
        <tr class="row">
            <td>   GC 7</td>
            <td> TYPE 2 ARCHITECTURE (AJL)</td>
            <td><?=Arr::path($report, 'itrs.7')?></td>
            <td><?=Arr::path($report, 'defect.7')?></td>
            <td><?=Arr::path($report, 'date.7')?></td>
        </tr>
        <tr class="row">
            <td>   GC 8</td>
            <td>  UNDERGROUND / BRANCH MULTIPORT INSTALLATION</td>
            <td><?=Arr::path($report, 'itrs.8')?></td>
            <td><?=Arr::path($report, 'defect.8')?></td>
            <td><?=Arr::path($report, 'date.8')?></td>
        </tr>
        <tr class="row">
            <td>   GC 9</td>
            <td> PILLAR INSTALLATION</td>
            <td><?=Arr::path($report, 'itrs.9')?></td>
            <td><?=Arr::path($report, 'defect.9')?></td>
            <td><?=Arr::path($report, 'date.9')?></td>
        </tr>
        <tr class="row">
            <td>   GC 10</td>
            <td> AERIAL MULTIPORT INSTALLATION</td>
            <td>N/A</td>
            <td>N/A</td>
            <td><?=Arr::path($report, 'date.10')?></td>
        </tr>
        <tr class="row">
            <td>   GC 11</td>
            <td> AERIAL TO UNDERGROUND BUILD DROP</td>
            <td>N/A</td>
            <td>N/A</td>
            <td><?=Arr::path($report, 'date.11')?></td>
        </tr>
        <tr class="row">
            <td>   GC 12</td>
            <td>FINAL AS-BUILT AND RED LINE MARK UP</td>
            <td><?=Arr::path($report, 'itrs.12')?></td>
            <td><?=Arr::path($report, 'defect.12')?></td>
            <td><?=Arr::path($report, 'date.12')?></td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">  Signature Legend of all parties </td>
        </tr>
        <tr>
            <td class="bold top-b"> Co.</td>
            <td class="bold top-b">Name</td>
            <td class="bold top-b">Position</td>
            <td class="bold top-b"> Signature </td>
            <td class="bold top-b"> Date </td>
        </tr>
        <tr class="row">
            <td> FH</td>
            <td><?=Arr::get($report, 'inspector-name')?></td>
            <td>PC INSPECTOR</td>
            <td class="noPadd">
                <img src="<?=$report['inspector-signature']?>" />
            </td>
            <td><?=Arr::get($report, 'inspector-date')?></td>
        </tr>
        <tr class="row">
            <td> FH</td>
            <td><?=Arr::get($report, 'drafter-name')?></td>
            <td>DRAFTER</td>
            <td class="noPadd">
                <img src="<?=$report['drafter-signature']?>" />
            </td>
            <td><?=Arr::get($report, 'drafter-date')?></td>
        </tr>
        <tr class="row top-l">
            <td> NBN</td>
            <td><?=Arr::get($report, 'nbn-name')?></td>
            <td><?=Arr::get($report, 'nbn-position')?></td>
            <td class="noPadd">
                <img src="<?=$report['nbn-signature']?>" />
            </td>
            <td><?=Arr::get($report, 'nbn-date')?></td>
        </tr>
    </table>
</div>

</body>

</html>