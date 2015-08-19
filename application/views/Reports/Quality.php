<style type="text/css">
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
    .trow td{
        padding: 10px;
        border-left: 1px solid #333;
        border-top: 1px solid #333;
    }
    .trow td:last-child{
        border-right: 1px solid #333;
    }
    .trow td input{
        width: 200px;
    }
    canvas{
        height: 100px;
        width: 300px;
        border:1px solid #ccc;
    }
    .trow .noPadd{
        padding: 0px;
        text-align: center;
    }
</style>
<script src="<?=URL::base()?>js/lib/signature_pad.min.js"></script>
<script type="application/javascript">
    $(function () {
        $('.datepicker').datepicker({
            dateFormat: 'dd-mm-yy'
        });


        $('canvas').each(function(){
            var canvas = $(this).get(0);
            var context = canvas.getContext('2d');

            var img = $(this).prev().val();
            if (img) {
                var image = new Image();
                image.onload = function() {
                    context.drawImage(image, 0, 0);
                };
                image.src = img;
            }

            $(this).parent().children('button').click(function() {
                clearCanvas(context, canvas);
            });

            var signature = new SignaturePad($(this).get(0));
        })


        $('form').submit(function(e) {
            $('canvas').each(function(){
                $(this).prev().val($(this).get(0).toDataURL());

            });
            //e.preventDefault();
        });

        $('#clear-canvas').on('click',function(){
            $('canvas').each(function(){
                var canvas = $(this).get(0);
                var context = canvas.getContext('2d');
                clearCanvas(context, canvas)
            });
        })


        function clearCanvas(context, canvas) {
            context.clearRect(0, 0, canvas.width, canvas.height);
            var w = canvas.width;
            canvas.width = 1;
            canvas.width = w;
        }

        $('button[name="finalize"]').click(function() {
            return confirm('Warning: if you finalize this report, it will be converted to PDF file and become non-editable! This action can\'t be undone!');
        });
    });
</script>
<form method="post" action="<?=URL::query()?>">
    <table>
        <tr style="text-align: right;" class="bold">
            <td colspan="5"> QUALITY INSPECTION SUMMARY - DIRN</td>
        </tr>
        <tr>
            <td class="bold" colspan="5"> PROJECT: MIMA         PROJECT CODE: 7XX</td>
        </tr>
        <tr class="small top-b">
            <td colspan="5">Document Revision: <input type="text" name="revision" value="<?=Arr::get($report, 'revision', 1)?>"/></td>
        </tr>
        <tr class="small">
            <td colspan="5"> Revision Date: <input type="text" class="datepicker" name="revisiondate" value="<?=Arr::get($report, 'revisiondate')?>" /></td>
        </tr>
        <tr class="small">
            <td colspan="5"> Prepared By: <input type="text" name="prepared" value="<?=Arr::get($report, 'prepared')?>"/></td>
        </tr>

        <tr>
            <td colspan="5" class="bold pad top-b">  PC REQUEST Date: <input type="text" class="datepicker" name="requestdate" value="<?=Arr::get($report, 'requestdate')?>" /> </td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">  SAM: <?=Arr::path($job, 'data.13')?>     </td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">  DA:  <?=Arr::path($job, 'data.14')?>    </td>
        </tr>
        <tr>
            <td colspan="5" class="bold pad top-b">   Inspection Date: <input type="text" class="datepicker" name="inspectiondate" value="<?=Arr::get($report, 'inspectiondate')?>" />   </td>
        </tr>
        <tr class="thead">
            <td class="bold top-b">  GROUP CODE   </td>
            <td class="bold top-b"> DESCRIPTIONS OF OPERATION(S)</td>
            <td class="bold top-b">  ITRs Completed  </td>
            <td class="bold top-b">  # Defects Identified  </td>
            <td class="bold top-b">  Date to Register  </td>
        </tr>
        <tr class="trow">
            <td>  GC 1</td>
            <td> FDH CABINETS AND SPLITTERS INSTALLATION</td>
            <td><input type="text" name="itrs[1]" value="<?=Arr::path($report, 'itrs.1')?>"></td>
            <td><input type="text" name="defect[1]" value="<?=Arr::path($report, 'defect.1')?>"></td>
            <td><input type="text" name="date[1]" value="<?=Arr::path($report, 'date.1')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 1a</td>
            <td>  FTTN CABINET INSTALLATION</td>
            <td><input type="text" name="itrs[1a]"></td>
            <td><input type="text" name="defect[1a]"></td>
            <td><input type="text" name="date[1a]" class="datepicker"></td>
        </tr>

        <tr class="trow">
            <td>   GC 2</td>
            <td>   PITS INSTALLATION (PIT)</td>
            <td><input type="text" name="itrs[2]" value="<?=Arr::path($report, 'itrs.2')?>"></td>
            <td><input type="text" name="defect[2]" value="<?=Arr::path($report, 'defect.2')?>"></td>
            <td><input type="text" name="date[2]" value="<?=Arr::path($report, 'date.2')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 2a</td>
            <td>  NBN EARTHING PIT</td>
            <td><input type="text" name="itrs[2a]"></td>
            <td><input type="text" name="defect[2a]"></td>
            <td><input type="text" name="date[2a]" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 3</td>
            <td>  (PVC) CONDUIT INSTALLATION</td>
            <td><input type="text" name="itrs[3]" value="<?=Arr::path($report, 'itrs.3')?>"></td>
            <td><input type="text" name="defect[3]" value="<?=Arr::path($report, 'defect.3')?>"></td>
            <td><input type="text" name="date[3]" value="<?=Arr::path($report, 'date.3')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 3a</td>
            <td>  POWER / EARTHING) CONDUIT INSTALLATION</td>
            <td><input type="text" name="itrs[3a]"></td>
            <td><input type="text" name="defect[3a]"></td>
            <td><input type="text" name="date[3a]" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 4</td>
            <td>  FIBRE OPTIC HAULING (DSS, LSS, MSS)</td>
            <td><input type="text" name="itrs[4]" value="<?=Arr::path($report, 'itrs.4')?>"></td>
            <td><input type="text" name="defect[4]" value="<?=Arr::path($report, 'defect.4')?>"></td>
            <td><input type="text" name="date[4]" value="<?=Arr::path($report, 'date.4')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 4a</td>
            <td>   COPPER HAULING</td>
            <td><input type="text" name="itrs[4a]"></td>
            <td><input type="text" name="defect[4a]"></td>
            <td><input type="text" name="date[4a]" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 5</td>
            <td>   AERIAL CABLE INSTALLATION</td>
            <td>N/A</td>
            <td>N/A</td>
            <td><input type="text" name="date[5]" value="<?=Arr::path($report, 'date.5')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 6</td>
            <td>  TYPE 2 ARCHITECTURE (DJL AND LJL)</td>
            <td><input type="text" name="itrs[6]" value="<?=Arr::path($report, 'itrs.6')?>"></td>
            <td><input type="text" name="defect[6]" value="<?=Arr::path($report, 'defect.6')?>"></td>
            <td><input type="text" name="date[6]" value="<?=Arr::path($report, 'date.6')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 7</td>
            <td> TYPE 2 ARCHITECTURE (AJL)</td>
            <td><input type="text" name="itrs[7]" value="<?=Arr::path($report, 'itrs.7')?>"></td>
            <td><input type="text" name="defect[7]" value="<?=Arr::path($report, 'defect.7')?>"></td>
            <td><input type="text" name="date[7]" value="<?=Arr::path($report, 'date.7')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 8</td>
            <td>  UNDERGROUND / BRANCH MULTIPORT INSTALLATION</td>
            <td><input type="text" name="itrs[8]" value="<?=Arr::path($report, 'itrs.8')?>"></td>
            <td><input type="text" name="defect[8]" value="<?=Arr::path($report, 'defect.8')?>"></td>
            <td><input type="text" name="date[8]" value="<?=Arr::path($report, 'date.8')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 9</td>
            <td> PILLAR INSTALLATION</td>
            <td><input type="text" name="itrs[9]" value="<?=Arr::path($report, 'itrs.9')?>"></td>
            <td><input type="text" name="defect[9]" value="<?=Arr::path($report, 'defect.9')?>"></td>
            <td><input type="text" name="date[9]" value="<?=Arr::path($report, 'date.9')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 10</td>
            <td> AERIAL MULTIPORT INSTALLATION</td>
            <td>N/A</td>
            <td>N/A</td>
            <td><input type="text" name="date[10]" value="<?=Arr::path($report, 'date.10')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 11</td>
            <td> AERIAL TO UNDERGROUND BUILD DROP</td>
            <td>N/A</td>
            <td>N/A</td>
            <td><input type="text" name="date[11]" value="<?=Arr::path($report, 'date.11')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td>   GC 12</td>
            <td>FINAL AS-BUILT AND RED LINE MARK UP</td>
            <td><input type="text" name="itrs[12]" value="<?=Arr::path($report, 'itrs.12')?>"></td>
            <td><input type="text" name="defect[12]" value="<?=Arr::path($report, 'defect.12')?>"></td>
            <td><input type="text" name="date[12]" value="<?=Arr::path($report, 'date.12')?>" class="datepicker"></td>
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
        <tr class="trow">
            <td> FH</td>
            <td><input type="text" name="inspector-name" value="<?=Arr::get($report, 'inspector-name')?>"></td>
            <td>PC INSPECTOR</td>
            <td class="noPadd">
                <input name="inspector-signature" type="hidden" value="<?=Arr::get($report, 'inspector-signature')?>" />
                <canvas class="panel panel-default" height="100" width= 300 ></canvas><br/>
                <button type="button" class="btn btn-danger">Clear signature</button>
            </td>
            <td><input type="text" name="inspector-date" value="<?=Arr::get($report, 'inspector-date')?>" class="datepicker"></td>
        </tr>
        <tr class="trow">
            <td> FH</td>
            <td><input type="text" name="drafter-name" value="<?=Arr::get($report, 'drafter-name')?>"></td>
            <td>DRAFTER</td>
            <td class="noPadd">
                <input name="drafter-signature" type="hidden" value="<?=Arr::get($report, 'drafter-signature')?>" />
                <canvas class="panel panel-default" height="100" width= 300 ></canvas><br/>
                <button type="button" class="btn btn-danger">Clear signature</button>
            </td>
            <td><input type="text" name="drafter-date"  value="<?=Arr::get($report, 'drafter-date')?>"class="datepicker"></td>
        </tr>
        <tr class="trow top-l">
            <td> NBN</td>
            <td><input type="text" name="nbn-name" value="<?=Arr::get($report, 'nbn-name')?>"></td>
            <td><input type="text" name="nbn-position" value="<?=Arr::get($report, 'nbn-position')?>"></td>
            <td class="noPadd">
                <input name="nbn-signature" type="hidden" value="<?=Arr::get($report, 'nbn-signature')?>" />
                <canvas class="panel panel-default" height="100" width= 300 ></canvas><br/>
                <button type="button" class="btn btn-danger">Clear signature</button>
            </td>
            <td><input type="text" name="nbn-date"  value="<?=Arr::get($report, 'nbn-date')?>" class="datepicker"></td>
        </tr>

        <tr>
            <td colspan="5" class="bold pad">
                <button type="submit" class="btn btn-success">Save changes</button>
                <button type="submit" name="finalize" value="1" class="btn btn-danger">Finalize</button>
            </td>
        </tr>




    </table>
</form>
