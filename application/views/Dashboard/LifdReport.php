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
    <?php $old_fsa = false; $old_fsam = false; $old_lifd = false; $odd_fsa = false; $odd_fsam = false; $odd_lifd = false; $odd_fda = false;?>
    <?php $fsa_c = 0;$fsam_c = 0;$lifd_c = 0;$fda_c = 0;?>
    <?php foreach ($list as $fsa => $fsams):   ?>
        <?php $fsa_c++?>
        <tr  data-level="1" id="level_1_<?=$fsa_c?>" class="text-center">
            <td><?=$fsa?></td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
            <td class="data">0</td>
        </tr>
        <?php foreach ($fsams as $fsam => $lifds):?>
            <?php $fsam_c++?>
            <tr  data-level="2" id="level_2_<?=$fsam_c?>" class="text-center">
                <td><?=$fsam?></td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
                <td class="data">0</td>
            </tr>
            <?php   foreach ($lifds as $lifd => $fdas):?>
                <?php $lifd_c++?>
                <?php $dates = explode('|', $lifd); $days = Utils::working_days($dates[1]);?>
                <tr  data-level="3" id="level_3_<?=$lifd_c?>" class="text-center">
                    <td><?=($dates[0] ? date('d-m-Y', $dates[0]) : '') . '-' . ($dates[1] ? date('d-m-Y', $dates[1]) . ' [' . $days . ' day' . ($days != 1 ? 's ' : ' ') . ($dates[1] < time() ? 'passed' : 'left') . ']' : '')?></td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                    <td class="data">0</td>
                </tr>
                <?php foreach ($fdas as $fda => $data):?>
                    <?php $fda_c++?>
                    <tr data-level="4" id="level_4_<?=$fda_c?>" class="text-center">
                        <td><?=$fda?></td>
                        <td class="data"><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::get($data, 'companies', array()))))?></td>
                        <td class="data"><?=array_sum($data)?></td>
                        <td class="data"><?=Arr::get($data, 'assigned')?></td>
                        <td class="data"><?=Arr::get($data, 'notify')?></td>
                        <td class="data"><?=Arr::get($data, 'planned')?></td>
                        <td class="data"><strong><?=Arr::get($data, 'assigned') + Arr::get($data, 'notify') + Arr::get($data, 'planned')?></strong></td>
                        <td class="data"><?=Arr::get($data, 'scheduled')?></td>
                        <td class="data"><?=Arr::get($data, 'inprogress')?></td>
                        <td class="data"><?=Arr::get($data, 'heldcontractor')?></td>
                        <td class="data"><strong><?=Arr::get($data, 'scheduled') + Arr::get($data, 'inprogress') + Arr::get($data, 'heldcontractor')?></strong></td>
                        <td class="data"><?=Arr::get($data, 'built')?></td>
                        <td class="data"><?=Arr::get($data, 'tested')?></td>
                        <td class="data"><strong><?=Arr::get($data, 'built') + Arr::get($data, 'tested')?></strong></td>
                        <td class="data"><?=Arr::get($data, 'deferred')?></td>
                        <td class="data"><?=Arr::get($data, 'dirty')?></td>
                        <td class="data"><?=Arr::get($data, 'heldnbn')?></td>
                        <td class="data"><strong><?=Arr::get($data, 'deferred') + Arr::get($data, 'dirty') + Arr::get($data, 'heldnbn')?></strong></td>
                    </tr>

                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
</table>