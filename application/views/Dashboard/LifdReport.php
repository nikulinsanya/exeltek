<table class="table small">
    <tr>
        <th>FSA ID</th>
        <th>FSAM ID</th>
        <th>LIFD</th>
        <th>FDA ID</th>
        <th>Contractors</th>
        <th>Total tickets</th>
        <th class="bg-success">ASSIGNED</th>
        <th class="bg-success">NOTIFY</th>
        <th class="bg-success">PLANNED</th>
        <th class="bg-success">Total</th>
        <th class="bg-warning">SCHEDULED</th>
        <th class="bg-warning">IN-PROGRESS</th>
        <th class="bg-warning">HELD-CONTRACTOR</th>
        <th class="bg-warning">Total</th>
        <th class="bg-info">BUILT</th>
        <th class="bg-info">TESTED</th>
        <th class="bg-info">Total</th>
        <th class="bg-danger">DEFERRED</th>
        <th class="bg-danger">DIRTY</th>
        <th class="bg-danger">HELD-NBN</th>
        <th class="bg-danger">Total</th>
    </tr>
    <?php $old_fsa = false; $old_fsam = false; $old_lifd = false; $odd_fsa = false; $odd_fsam = false; $odd_lifd = false; $odd_fda = false;
    foreach ($list as $fsa => $fsams)
        foreach ($fsams as $fsam => $lifds)
            foreach ($lifds as $lifd => $fdas)
                foreach ($fdas as $fda => $data):
                    ?>
                    <tr class="text-center <?=($odd_fda = !$odd_fda) ? 'green' : 'lgreen'?>">
                        <?php if ($old_fsa !== $fsa): $old_fsa = $fsa; $old_fsam = false; $old_lifd = false;?>
                            <td rowspan="<?=Utils::count($fsams, 2)?>" class="<?=($odd_fsa = !$odd_fsa) ? 'green' : 'lgreen'?>"><?=$fsa?></td>
                        <?php endif;?>
                        <?php if ($old_fsam !== $fsam): $old_fsam = $fsam; $old_lifd = false;?>
                            <td rowspan="<?=Utils::count($lifds, 1)?>" class="<?=($odd_fsam = !$odd_fsam) ? 'green' : 'lgreen'?>"><?=$fsam?></td>
                        <?php endif;?>
                        <?php if ($old_lifd !== $lifd): $old_lifd = $lifd; $dates = explode('|', $lifd); $days = Utils::working_days($dates[1]);?>
                            <td rowspan="<?=count($fdas)?>" class="<?=($odd_lifd = !$odd_lifd) ? 'green' : 'lgreen'?>">
                                <?=($dates[0] ? date('d-m-Y', $dates[0]) : '') . '-' . ($dates[1] ? date('d-m-Y', $dates[1]) . ' [' . $days . ' day' . ($days != 1 ? 's ' : ' ') . ($dates[1] < time() ? 'passed' : 'left') . ']' : '')?>
                            </td>
                        <?php endif;?>
                        <td><?=$fda?></td>
                        <td><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::get($data, 'companies', array()))))?></td>
                        <td><?=array_sum($data)?></td>
                        <td><?=Arr::get($data, 'assigned')?></td>
                        <td><?=Arr::get($data, 'notify')?></td>
                        <td><?=Arr::get($data, 'planned')?></td>
                        <td><strong><?=Arr::get($data, 'assigned') + Arr::get($data, 'notify') + Arr::get($data, 'planned')?></strong></td>
                        <td><?=Arr::get($data, 'scheduled')?></td>
                        <td><?=Arr::get($data, 'inprogress')?></td>
                        <td><?=Arr::get($data, 'heldcontractor')?></td>
                        <td><strong><?=Arr::get($data, 'scheduled') + Arr::get($data, 'inprogress') + Arr::get($data, 'heldcontractor')?></strong></td>
                        <td><?=Arr::get($data, 'built')?></td>
                        <td><?=Arr::get($data, 'tested')?></td>
                        <td><strong><?=Arr::get($data, 'built') + Arr::get($data, 'tested')?></strong></td>
                        <td><?=Arr::get($data, 'deferred')?></td>
                        <td><?=Arr::get($data, 'dirty')?></td>
                        <td><?=Arr::get($data, 'heldnbn')?></td>
                        <td><strong><?=Arr::get($data, 'deferred') + Arr::get($data, 'dirty') + Arr::get($data, 'heldnbn')?></strong></td>
                    </tr>
                <?php endforeach;?>
</table>