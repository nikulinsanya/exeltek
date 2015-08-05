<table id="fda_table" class="table small controller">
<!--    <tr>-->
<!--        <th>FSA ID</th>-->
<!--        <th>FSAM ID</th>-->
<!--        <th>LIFD</th>-->
<!--        <th>FDA ID</th>-->
<!--        <th>Contractors</th>-->
<!--        <th>Total tickets</th>-->
<!--        <th class="bg-success">ASSIGNED</th>-->
<!--        <th class="bg-success">NOTIFY</th>-->
<!--        <th class="bg-success">PLANNED</th>-->
<!--        <th class="bg-success">Total</th>-->
<!--        <th class="bg-warning">SCHEDULED</th>-->
<!--        <th class="bg-warning">IN-PROGRESS</th>-->
<!--        <th class="bg-warning">HELD-CONTRACTOR</th>-->
<!--        <th class="bg-warning">Total</th>-->
<!--        <th class="bg-info">BUILT</th>-->
<!--        <th class="bg-info">TESTED</th>-->
<!--        <th class="bg-info">Total</th>-->
<!--        <th class="bg-danger">DEFERRED</th>-->
<!--        <th class="bg-danger">DIRTY</th>-->
<!--        <th class="bg-danger">HELD-NBN</th>-->
<!--        <th class="bg-danger">Total</th>-->
<!--    </tr>-->
        <tr>
            <th  style="width: 340px;"></th>
            <th style="width: 200px;"><div style="transform: translate(122px, 135px) rotate(270deg);"><span>Current contractors</span></div></th>
            <th style="width: 200px;"><div style="transform: translate(122px, 135px) rotate(270deg);"><span>Previous contractors</span></div></th>
            <th class="purple"><div><span>Total tickets</span></div></th>
            <th class="lightcyan"><div><span>ASSIGNED</span></div></th>
            <th class="lightcyan"><div><span>NOTIFY</span></div></th>
            <th class="lightcyan"><div><span>PLANNED</span></div></th>
            <th class="lightcyan"><div><span>Total</span></div></th>
            <th class="yellow"><div><span>SCHEDULED</span></div></th>
            <th class="yellow"><div><span>IN-PROGRESS</span></div></th>
            <th class="yellow"><div><span>HELD-CONTRACTOR</span></div></th>
            <th class="yellow"><div><span>Total</span></div></th>
            <th class="lgreen"><div><span>BUILT</span></div></th>
            <th class="lgreen"><div><span>TESTED</span></div></th>
            <th class="lgreen"><div><span>Total</span></div></th>
            <th class="rose"><div><span>DEFERRED</span></div></th>
            <th class="rose"><div><span>DIRTY</span></div></th>
            <th class="rose"><div><span>HELD-NBN</span></div></th>
            <th class="rose"><div><span>Total</span></div></th>
        </tr>
    <?php $old_fsa = false; $old_fsam = false; $old_lifd = false; $odd_fsa = false; $odd_fsam = false; $odd_lifd = false; $odd_fda = false;?>
    <?php $fsa_c = 0;$fsam_c = 0;$lifd_c = 0;$fda_c = 0;?>
    <?php foreach ($list as $fsa => $fsams):   ?>
        <?php $fsa_c++?>
        <tr  data-level="1" id="level_1_<?=$fsa_c?>" class="text-center">
            <td colspan="3"><?=$fsa?></td>
            <td class="data purple"><strong><?=array_sum(Arr::get($total, $fsa))?></strong></td>
            <td class="data lightcyan"><?=Arr::path($total, array($fsa, 'assigned'))?></td>
            <td class="data lightcyan"><?=Arr::path($total, array($fsa, 'notify'))?></td>
            <td class="data lightcyan"><?=Arr::path($total, array($fsa, 'planned'))?></td>
            <td class="data lightcyan"><strong><?=Arr::path($total, array($fsa, 'assigned')) + Arr::path($total, array($fsa, 'notify')) + Arr::path($total, array($fsa, 'planned')) ? : ''?></strong></td>
            <td class="data yellow"><?=Arr::path($total, array($fsa, 'scheduled'))?></td>
            <td class="data yellow"><?=Arr::path($total, array($fsa, 'inprogress'))?></td>
            <td class="data yellow"><?=Arr::path($total, array($fsa, 'heldcontractor'))?></td>
            <td class="data yellow"><strong><?=Arr::path($total, array($fsa, 'scheduled')) + Arr::path($total, array($fsa, 'inprogress')) + Arr::path($total, array($fsa, 'heldcontractor')) ? : ''?></strong></td>
            <td class="data lgreen"><?=Arr::path($total, array($fsa, 'built'))?></td>
            <td class="data lgreen"><?=Arr::path($total, array($fsa, 'tested'))?></td>
            <td class="data lgreen"><strong><?=Arr::path($total, array($fsa, 'built')) + Arr::path($total, array($fsa, 'tested')) ? : ''?></strong></td>
            <td class="data rose"><?=Arr::path($total, array($fsa, 'deferred'))?></td>
            <td class="data rose"><?=Arr::path($total, array($fsa, 'dirty'))?></td>
            <td class="data rose"><?=Arr::path($total, array($fsa, 'heldnbn'))?></td>
            <td class="data rose"><strong><?=Arr::path($total, array($fsa, 'deferred')) + Arr::path($total, array($fsa, 'dirty')) + Arr::path($total, array($fsa, 'heldnbn')) ? : ''?></strong></td>
        </tr>
        <?php foreach ($fsams as $fsam => $lifds):?>
            <?php $fsam_c++?>
            <tr  data-level="2" id="level_2_<?=$fsam_c?>" class="text-center">
                <td colspan="3"><?=$fsam?></td>
                <td class="data purple"><strong><?=array_sum(Arr::get($total, $fsam))?></strong></td>
                <td class="data lightcyan"><?=Arr::path($total, array($fsam, 'assigned'))?></td>
                <td class="data lightcyan"><?=Arr::path($total, array($fsam, 'notify'))?></td>
                <td class="data lightcyan"><?=Arr::path($total, array($fsam, 'planned'))?></td>
                <td class="data lightcyan"><strong><?=Arr::path($total, array($fsam, 'assigned')) + Arr::path($total, array($fsam, 'notify')) + Arr::path($total, array($fsam, 'planned')) ? : ''?></strong></td>
                <td class="data yellow"><?=Arr::path($total, array($fsam, 'scheduled'))?></td>
                <td class="data yellow"><?=Arr::path($total, array($fsam, 'inprogress'))?></td>
                <td class="data yellow"><?=Arr::path($total, array($fsam, 'heldcontractor'))?></td>
                <td class="data yellow"><strong><?=Arr::path($total, array($fsam, 'scheduled')) + Arr::path($total, array($fsam, 'inprogress')) + Arr::path($total, array($fsam, 'heldcontractor')) ? : ''?></strong></td>
                <td class="data lgreen"><?=Arr::path($total, array($fsam, 'built'))?></td>
                <td class="data lgreen"><?=Arr::path($total, array($fsam, 'tested'))?></td>
                <td class="data lgreen"><strong><?=Arr::path($total, array($fsam, 'built')) + Arr::path($total, array($fsam, 'tested')) ? : ''?></strong></td>
                <td class="data rose"><?=Arr::path($total, array($fsam, 'deferred'))?></td>
                <td class="data rose"><?=Arr::path($total, array($fsam, 'dirty'))?></td>
                <td class="data rose"><?=Arr::path($total, array($fsam, 'heldnbn'))?></td>
                <td class="data rose"><strong><?=Arr::path($total, array($fsam, 'deferred')) + Arr::path($total, array($fsam, 'dirty')) + Arr::path($total, array($fsam, 'heldnbn')) ? : ''?></strong></td>
            </tr>
            <?php   foreach ($lifds as $lifd => $fdas):?>
                <?php $lifd_c++?>
                <?php $dates = explode('|', $lifd); $days = Utils::working_days($dates[1]);?>
                <tr  data-level="3" id="level_3_<?=$lifd_c?>" class="text-center">
                    <td colspan="3"><?=($dates[0] ? date('d-m-Y', $dates[0]) : '') . '-' . ($dates[1] ? date('d-m-Y', $dates[1]) . ' [' . $days . ' day' . ($days != 1 ? 's ' : ' ') . ($dates[1] < time() ? 'passed' : 'left') . ']' : '')?></td>
                    <td class="data purple"><strong><?=array_sum(Arr::get($total, $fsam . $lifd))?></strong></td>
                    <td class="data lightcyan"><?=Arr::path($total, array($fsam . $lifd, 'assigned'))?></td>
                    <td class="data lightcyan"><?=Arr::path($total, array($fsam . $lifd, 'notify'))?></td>
                    <td class="data lightcyan"><?=Arr::path($total, array($fsam . $lifd, 'planned'))?></td>
                    <td class="data lightcyan"><strong><?=Arr::path($total, array($fsam . $lifd, 'assigned')) + Arr::path($total, array($fsam . $lifd, 'notify')) + Arr::path($total, array($fsam . $lifd, 'planned')) ? : ''?></strong></td>
                    <td class="data yellow"><?=Arr::path($total, array($fsam . $lifd, 'scheduled'))?></td>
                    <td class="data yellow"><?=Arr::path($total, array($fsam . $lifd, 'inprogress'))?></td>
                    <td class="data yellow"><?=Arr::path($total, array($fsam . $lifd, 'heldcontractor'))?></td>
                    <td class="data yellow"><strong><?=Arr::path($total, array($fsam . $lifd, 'scheduled')) + Arr::path($total, array($fsam . $lifd, 'inprogress')) + Arr::path($total, array($fsam . $lifd, 'heldcontractor')) ? : ''?></strong></td>
                    <td class="data lgreen"><?=Arr::path($total, array($fsam . $lifd, 'built'))?></td>
                    <td class="data lgreen"><?=Arr::path($total, array($fsam . $lifd, 'tested'))?></td>
                    <td class="data lgreen"><strong><?=Arr::path($total, array($fsam . $lifd, 'built')) + Arr::path($total, array($fsam . $lifd, 'tested')) ? : ''?></strong></td>
                    <td class="data rose"><?=Arr::path($total, array($fsam . $lifd, 'deferred'))?></td>
                    <td class="data rose"><?=Arr::path($total, array($fsam . $lifd, 'dirty'))?></td>
                    <td class="data rose"><?=Arr::path($total, array($fsam . $lifd, 'heldnbn'))?></td>
                    <td class="data rose"><strong><?=Arr::path($total, array($fsam . $lifd, 'deferred')) + Arr::path($total, array($fsam . $lifd, 'dirty')) + Arr::path($total, array($fsam . $lifd, 'heldnbn')) ? : ''?></strong></td>
                </tr>
                <?php foreach ($fdas as $fda => $data):?>
                    <?php $fda_c++?>
                    <tr data-level="4" id="level_4_<?=$fda_c?>" class="text-center">
                        <td><?=$fda?></td>
                        <td class="data"><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::path($data, 'companies.now', array()))))?></td>
                        <td class="data"><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::path($data, 'companies.ex', array()))))?></td>
                        <td class="data purple"><?=array_sum($data)?></td>
                        <td class="data lightcyan"><?=Arr::get($data, 'assigned')?></td>
                        <td class="data lightcyan"><?=Arr::get($data, 'notify')?></td>
                        <td class="data lightcyan"><?=Arr::get($data, 'planned')?></td>
                        <td class="data lightcyan"><strong><?=Arr::get($data, 'assigned') + Arr::get($data, 'notify') + Arr::get($data, 'planned') ? : ''?></strong></td>
                        <td class="data yellow"><?=Arr::get($data, 'scheduled')?></td>
                        <td class="data yellow"><?=Arr::get($data, 'inprogress')?></td>
                        <td class="data yellow"><?=Arr::get($data, 'heldcontractor')?></td>
                        <td class="data yellow"><strong><?=Arr::get($data, 'scheduled') + Arr::get($data, 'inprogress') + Arr::get($data, 'heldcontractor') ? : ''?></strong></td>
                        <td class="data lgreen"><?=Arr::get($data, 'built')?></td>
                        <td class="data lgreen"><?=Arr::get($data, 'tested')?></td>
                        <td class="data lgreen"><strong><?=Arr::get($data, 'built') + Arr::get($data, 'tested') ? : ''?></strong></td>
                        <td class="data rose"><?=Arr::get($data, 'deferred')?></td>
                        <td class="data rose"><?=Arr::get($data, 'dirty')?></td>
                        <td class="data rose"><?=Arr::get($data, 'heldnbn')?></td>
                        <td class="data rose"><strong><?=Arr::get($data, 'deferred') + Arr::get($data, 'dirty') + Arr::get($data, 'heldnbn') ? : ''?></strong></td>
                    </tr>

                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
</table>