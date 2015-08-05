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
            <th style="width: 250px;"><div style="transform: translate(122px, 135px) rotate(270deg);"><span>Contractors</span></div></th>
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
            <td colspan="2"><?=$fsa?></td>
            <td class="data purple"><strong>0</strong></td>
            <td class="data lightcyan">0</td>
            <td class="data lightcyan">0</td>
            <td class="data lightcyan">0</td>
            <td class="data lightcyan"><strong>0</strong></td>
            <td class="data yellow">0</td>
            <td class="data yellow">0</td>
            <td class="data yellow">0</td>
            <td class="data yellow"><strong>0</strong></td>
            <td class="data lgreen">0</td>
            <td class="data lgreen">0</td>
            <td class="data lgreen"><strong>0</strong></td>
            <td class="data rose">0</td>
            <td class="data rose">0</td>
            <td class="data rose">0</td>
            <td class="data rose"><strong>0</strong></td>
        </tr>
        <?php foreach ($fsams as $fsam => $lifds):?>
            <?php $fsam_c++?>
            <tr  data-level="2" id="level_2_<?=$fsam_c?>" class="text-center">
                <td colspan="2"><?=$fsam?></td>
                <td class="data purple"><strong>0</strong></td>
                <td class="data lightcyan">0</td>
                <td class="data lightcyan">0</td>
                <td class="data lightcyan">0</td>
                <td class="data lightcyan"><strong>0</strong></td>
                <td class="data yellow">0</td>
                <td class="data yellow">0</td>
                <td class="data yellow">0</td>
                <td class="data yellow"><strong>0</strong></td>
                <td class="data lgreen">0</td>
                <td class="data lgreen">0</td>
                <td class="data lgreen"><strong>0</strong></td>
                <td class="data rose">0</td>
                <td class="data rose">0</td>
                <td class="data rose">0</td>
                <td class="data rose"><strong>0</strong></td>
            </tr>
            <?php   foreach ($lifds as $lifd => $fdas):?>
                <?php $lifd_c++?>
                <?php $dates = explode('|', $lifd); $days = Utils::working_days($dates[1]);?>
                <tr  data-level="3" id="level_3_<?=$lifd_c?>" class="text-center">
                    <td colspan="2"><?=($dates[0] ? date('d-m-Y', $dates[0]) : '') . '-' . ($dates[1] ? date('d-m-Y', $dates[1]) . ' [' . $days . ' day' . ($days != 1 ? 's ' : ' ') . ($dates[1] < time() ? 'passed' : 'left') . ']' : '')?></td>
                    <td class="data purple"><strong>0</strong></td>
                    <td class="data lightcyan">0</td>
                    <td class="data lightcyan">0</td>
                    <td class="data lightcyan">0</td>
                    <td class="data lightcyan"><strong>0</strong></td>
                    <td class="data yellow">0</td>
                    <td class="data yellow">0</td>
                    <td class="data yellow">0</td>
                    <td class="data yellow"><strong>0</strong></td>
                    <td class="data lgreen">0</td>
                    <td class="data lgreen">0</td>
                    <td class="data lgreen"><strong>0</strong></td>
                    <td class="data rose">0</td>
                    <td class="data rose">0</td>
                    <td class="data rose">0</td>
                    <td class="data rose"><strong>0</strong></td>
                </tr>
                <?php foreach ($fdas as $fda => $data):?>
                    <?php $fda_c++?>
                    <tr data-level="4" id="level_4_<?=$fda_c?>" class="text-center">
                        <td><?=$fda?></td>
                        <td class="data"><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::get($data, 'companies', array()))))?></td>
                        <td class="data purple"><?=array_sum($data)?></td>
                        <td class="data lightcyan"><?=Arr::get($data, 'assigned')?></td>
                        <td class="data lightcyan"><?=Arr::get($data, 'notify')?></td>
                        <td class="data lightcyan"><?=Arr::get($data, 'planned')?></td>
                        <td class="data lightcyan"><strong><?=Arr::get($data, 'assigned') + Arr::get($data, 'notify') + Arr::get($data, 'planned')?></strong></td>
                        <td class="data yellow"><?=Arr::get($data, 'scheduled')?></td>
                        <td class="data yellow"><?=Arr::get($data, 'inprogress')?></td>
                        <td class="data yellow"><?=Arr::get($data, 'heldcontractor')?></td>
                        <td class="data yellow"><strong><?=Arr::get($data, 'scheduled') + Arr::get($data, 'inprogress') + Arr::get($data, 'heldcontractor')?></strong></td>
                        <td class="data lgreen"><?=Arr::get($data, 'built')?></td>
                        <td class="data lgreen"><?=Arr::get($data, 'tested')?></td>
                        <td class="data lgreen"><strong><?=Arr::get($data, 'built') + Arr::get($data, 'tested')?></strong></td>
                        <td class="data rose"><?=Arr::get($data, 'deferred')?></td>
                        <td class="data rose"><?=Arr::get($data, 'dirty')?></td>
                        <td class="data rose"><?=Arr::get($data, 'heldnbn')?></td>
                        <td class="data rose"><strong><?=Arr::get($data, 'deferred') + Arr::get($data, 'dirty') + Arr::get($data, 'heldnbn')?></strong></td>
                    </tr>

                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
</table>