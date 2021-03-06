<table id="fda_table" class="table small controller">
        <tr class="header">
            <th  style="width: 340px; vertical-align: bottom;">
                <button type="button" class="btn btn-success expandAll " onclick="expandCollapseTreeView(true);">
                    <span class="glyphicon glyphicon-menu-down"></span>
                    Expand all
                </button>
                <button type="button" class="btn btn-success collapseAll" style="display: none;" onclick="expandCollapseTreeView(false);">
                    <span class="glyphicon glyphicon-menu-up"></span>
                    Collapse all
                </button>
            </th>
            <?php if (Group::current('allow_assign')): $count = 3;?>
            <th style="width: 180px;"><div style="transform: translate(75px, 135px) rotate(270deg);"><span>Current contractors</span></div></th>
            <th style="width: 180px;"><div style="transform: translate(75px, 135px) rotate(270deg);"><span>Previous contractors</span></div></th>
            <?php else: $count = 1; endif;?>
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
    <?php foreach ($list as $fsa => $fsams): $break = Arr::get($breakdown, $fsa, array());?>
        <?php $fsa_c++?>
        <tr  data-level="1" id="level_1_<?=$fsa_c?>" class="text-center">
            <td colspan="<?=$count?>"><?=$fsa?></td>
            <td class="data purple"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>"><?=array_sum(Arr::get($total, $fsa, array()))?></a></strong></td>
            <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=assigned"><?=Arr::path($total, array($fsa, 'assigned'))?></a></td>
            <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=notify"><?=Arr::path($total, array($fsa, 'notify'))?></a></td>
            <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=planned"><?=Arr::path($total, array($fsa, 'planned'))?></a></td>
            <td class="data lightcyan"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=assigned&columns[]=44&actions[]=0&values[]=notify&columns[]=44&actions[]=0&values[]=planned"><?=Arr::path($total, array($fsa, 'assigned')) + Arr::path($total, array($fsa, 'notify')) + Arr::path($total, array($fsa, 'planned')) ? : ''?></a></strong></td>
            <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=scheduled"><?=Arr::path($total, array($fsa, 'scheduled'))?></a></td>
            <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=in-progress"><?=Arr::path($total, array($fsa, 'inprogress'))?></a></td>
            <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=held-contractor"><?=Arr::path($total, array($fsa, 'heldcontractor'))?></a></td>
            <td class="data yellow"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=scheduled&columns[]=44&actions[]=0&values[]=in-progress&columns[]=44&actions[]=0&values[]=held-contractor""><?=Arr::path($total, array($fsa, 'scheduled')) + Arr::path($total, array($fsa, 'inprogress')) + Arr::path($total, array($fsa, 'heldcontractor')) ? : ''?></a></strong></td>
            <td class="data lgreen" width="65">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true" data-toggle="popover" title="Built statuses"
                    <?php foreach ($break as $key => $value) echo 'data-' . $key . '="' . $value . '"'?>>
                </span>
                <a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=built" class="breakdown-info">
                    <?=Arr::path($total, array($fsa, 'built'))?>
                </a>
            </td>
            <td class="data lgreen"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=tested"><?=Arr::path($total, array($fsa, 'tested'))?></a></td>
            <td class="data lgreen"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=built&columns[]=44&actions[]=0&values[]=tested"><?=Arr::path($total, array($fsa, 'built')) + Arr::path($total, array($fsa, 'tested')) ? : ''?></a></strong></td>
            <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=deferred"><?=Arr::path($total, array($fsa, 'deferred'))?></a></td>
            <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=dirty"><?=Arr::path($total, array($fsa, 'dirty'))?></a></td>
            <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=held-nbn"><?=Arr::path($total, array($fsa, 'heldnbn'))?></a></td>
            <td class="data rose"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=12&actions[]=2&values[]=<?=$fsa?>&columns[]=44&actions[]=0&values[]=deferred&columns[]=44&actions[]=0&values[]=dirty&columns[]=44&actions[]=0&values[]=held-nbn""><?=Arr::path($total, array($fsa, 'deferred')) + Arr::path($total, array($fsa, 'dirty')) + Arr::path($total, array($fsa, 'heldnbn')) ? : ''?></a></strong></td>
        </tr>
        <?php foreach ($fsams as $fsam => $lifds): $break = Arr::get($breakdown, $fsam, array());?>
            <?php $fsam_c++?>
            <tr  data-level="2" id="level_2_<?=$fsam_c?>" class="text-center">
                <td colspan="<?=$count?>"><?=$fsam?></td>
                <td class="data purple"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>"><?=array_sum(Arr::get($total, $fsam, array()))?></a></strong></td>
                <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=assigned"><?=Arr::path($total, array($fsam, 'assigned'))?></a></td>
                <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=notify"><?=Arr::path($total, array($fsam, 'notify'))?></a></td>
                <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=planned"><?=Arr::path($total, array($fsam, 'planned'))?></a></td>
                <td class="data lightcyan"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=assigned&columns[]=44&actions[]=0&values[]=notify&columns[]=44&actions[]=0&values[]=planned"><?=Arr::path($total, array($fsam, 'assigned')) + Arr::path($total, array($fsam, 'notify')) + Arr::path($total, array($fsam, 'planned')) ? : ''?></a></strong></td>
                <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=scheduled"><?=Arr::path($total, array($fsam, 'scheduled'))?></a></td>
                <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=in-progress"><?=Arr::path($total, array($fsam, 'inprogress'))?></a></td>
                <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=held-contractor"><?=Arr::path($total, array($fsam, 'heldcontractor'))?></a></td>
                <td class="data yellow"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=scheduled&columns[]=44&actions[]=0&values[]=in-progress&columns[]=44&actions[]=0&values[]=held-contractor""><?=Arr::path($total, array($fsam, 'scheduled')) + Arr::path($total, array($fsam, 'inprogress')) + Arr::path($total, array($fsam, 'heldcontractor')) ? : ''?></a></strong></td>
                <td class="data lgreen" width="65">
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true" data-toggle="popover" title="Built statuses"
                        <?php foreach ($break as $key => $value) echo 'data-' . $key . '="' . $value . '"'?>>
                    </span>

                    <a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=built" class="breakdown-info">
                        <?=Arr::path($total, array($fsam, 'built'))?>
                    </a></td>
                <td class="data lgreen"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=tested"><?=Arr::path($total, array($fsam, 'tested'))?></a></td>
                <td class="data lgreen"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=built&columns[]=44&actions[]=0&values[]=tested"><?=Arr::path($total, array($fsam, 'built')) + Arr::path($total, array($fsam, 'tested')) ? : ''?></a></strong></td>
                <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=deferred"><?=Arr::path($total, array($fsam, 'deferred'))?></a></td>
                <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=dirty"><?=Arr::path($total, array($fsam, 'dirty'))?></a></td>
                <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=held-nbn"><?=Arr::path($total, array($fsam, 'heldnbn'))?></a></td>
                <td class="data rose"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=44&actions[]=0&values[]=deferred&columns[]=44&actions[]=0&values[]=dirty&columns[]=44&actions[]=0&values[]=held-nbn""><?=Arr::path($total, array($fsam, 'deferred')) + Arr::path($total, array($fsam, 'dirty')) + Arr::path($total, array($fsam, 'heldnbn')) ? : ''?></a></strong></td>
            </tr>
            <?php   foreach ($lifds as $lifd => $fdas): $break = Arr::get($breakdown, $fsam . $lifd, array());?>
                <?php $lifd_c++?>
                <?php $dates = explode('|', $lifd); $days = Utils::working_days($dates[1]);?>
                <tr  data-level="3" id="level_3_<?=$lifd_c?>" class="text-center">
                    <td colspan="<?=$count?>"><?=($dates[0] ? date('d-m-Y', $dates[0]) : '') . '-' . ($dates[1] ? date('d-m-Y', $dates[1]) . ' [' . $days . ' day' . ($days != 1 ? 's ' : ' ') . ($dates[1] < time() ? 'passed' : 'left') . ']' : '')?></td>
                    <td class="data purple"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>"><?=array_sum(Arr::get($total, $fsam . $lifd, array()))?></a></strong></td>
                    <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=assigned"><?=Arr::path($total, array($fsam . $lifd, 'assigned'))?></a></td>
                    <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=notify"><?=Arr::path($total, array($fsam . $lifd, 'notify'))?></a></td>
                    <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=planned"><?=Arr::path($total, array($fsam . $lifd, 'planned'))?></a></td>
                    <td class="data lightcyan"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=assigned&columns[]=44&actions[]=0&values[]=notify&columns[]=44&actions[]=0&values[]=planned"><?=Arr::path($total, array($fsam . $lifd, 'assigned')) + Arr::path($total, array($fsam . $lifd, 'notify')) + Arr::path($total, array($fsam . $lifd, 'planned')) ? : ''?></a></strong></td>
                    <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=scheduled"><?=Arr::path($total, array($fsam . $lifd, 'scheduled'))?></a></td>
                    <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=in-progress"><?=Arr::path($total, array($fsam . $lifd, 'inprogress'))?></a></td>
                    <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=held-contractor"><?=Arr::path($total, array($fsam . $lifd, 'heldcontractor'))?></a></td>
                    <td class="data yellow"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=scheduled&columns[]=44&actions[]=0&values[]=in-progress&columns[]=44&actions[]=0&values[]=held-contractor""><?=Arr::path($total, array($fsam . $lifd, 'scheduled')) + Arr::path($total, array($fsam . $lifd, 'inprogress')) + Arr::path($total, array($fsam . $lifd, 'heldcontractor')) ? : ''?></a></strong></td>
                    <td class="data lgreen" width="65">
                        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true" data-toggle="popover" title="Built statuses"
                            <?php foreach ($break as $key => $value) echo 'data-' . $key . '="' . $value . '"'?>>
                        </span>

                        <a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=built" class="breakdown-info">
                            <?=Arr::path($total, array($fsam . $lifd, 'built'))?>
                        </a>
                    </td>
                    <td class="data lgreen"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=tested"><?=Arr::path($total, array($fsam . $lifd, 'tested'))?></a></td>
                    <td class="data lgreen"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=built&columns[]=44&actions[]=0&values[]=tested"><?=Arr::path($total, array($fsam . $lifd, 'built')) + Arr::path($total, array($fsam . $lifd, 'tested')) ? : ''?></a></strong></td>
                    <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=deferred"><?=Arr::path($total, array($fsam . $lifd, 'deferred'))?></a></td>
                    <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=dirty"><?=Arr::path($total, array($fsam . $lifd, 'dirty'))?></a></td>
                    <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=held-nbn"><?=Arr::path($total, array($fsam . $lifd, 'heldnbn'))?></a></td>
                    <td class="data rose"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=13&actions[]=2&values[]=<?=$fsam?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=deferred&columns[]=44&actions[]=0&values[]=dirty&columns[]=44&actions[]=0&values[]=held-nbn""><?=Arr::path($total, array($fsam . $lifd, 'deferred')) + Arr::path($total, array($fsam . $lifd, 'dirty')) + Arr::path($total, array($fsam . $lifd, 'heldnbn')) ? : ''?></a></strong></td>
                </tr>
                <?php foreach ($fdas as $fda => $data): $break = Arr::get($breakdown, $fda . $lifd, array());?>
                    <?php $fda_c++?>
                    <tr data-level="4" id="level_4_<?=$fda_c?>" class="text-center">
                        <td><?=$fda?></td>
                        <?php if (Group::current('allow_assign')):?>
                        <td class="data"><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::path($data, 'companies.now', array()))))?></td>
                        <td class="data"><?=implode('<br/>', array_intersect_key($companies, array_flip(Arr::path($data, 'companies.ex', array()))))?></td>
                        <?php endif;?>
                        <td class="data purple"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>"><?=array_sum($data)?></a></td>
                        <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=assigned"><?=Arr::get($data, 'assigned') ?></a></td>
                        <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=notify"><?=Arr::get($data, 'notify') ?></a></td>
                        <td class="data lightcyan"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=planned"><?=Arr::get($data, 'planned') ?></a></td>
                        <td class="data lightcyan"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=assigned&columns[]=44&actions[]=0&values[]=notify&columns[]=44&actions[]=0&values[]=planned"><?=Arr::get($data, 'assigned')  + Arr::get($data, 'notify')  + Arr::get($data, 'planned')  ? : ''?></a></strong></td>
                        <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=scheduled"><?=Arr::get($data, 'scheduled') ?></a></td>
                        <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=in-progress"><?=Arr::get($data, 'inprogress') ?></a></td>
                        <td class="data yellow"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=held-contractor"><?=Arr::get($data, 'heldcontractor') ?></a></td>
                        <td class="data yellow"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=scheduled&columns[]=44&actions[]=0&values[]=in-progress&columns[]=44&actions[]=0&values[]=held-contractor""><?=Arr::get($data, 'scheduled')  + Arr::get($data, 'inprogress')  + Arr::get($data, 'heldcontractor')  ? : ''?></a></strong></td>
                        <td class="data lgreen">
                            <span class="glyphicon glyphicon-info-sign" aria-hidden="true" data-toggle="popover" title="Built statuses"
                                <?php foreach ($break as $key => $value) echo 'data-' . $key . '="' . $value . '"'?>>
                            </span>
                            <a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=built" class="breakdown-info">
                                <?=Arr::get($data, 'built') ?>
                            </a></td>
                        <td class="data lgreen"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=tested"><?=Arr::get($data, 'tested') ?></a></td>
                        <td class="data lgreen"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=built&columns[]=44&actions[]=0&values[]=tested"><?=Arr::get($data, 'built')  + Arr::get($data, 'tested')  ? : ''?></a></strong></td>
                        <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=deferred"><?=Arr::get($data, 'deferred') ?></a></td>
                        <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=dirty"><?=Arr::get($data, 'dirty') ?></a></td>
                        <td class="data rose"><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=held-nbn"><?=Arr::get($data, 'heldnbn') ?></a></td>
                        <td class="data rose"><strong><a href="<?=URL::base()?><?=$url ? $url . '&' : '?'?>columns[]=14&actions[]=2&values[]=<?=$fda?>&columns[]=17&actions[]=2&values[]=<?=$dates[0] ? date('d-m-Y', $dates[0]) : ''?>&columns[]=18&actions[]=2&values[]=<?=$dates[1] ? date('d-m-Y', $dates[1]) : ''?>&columns[]=44&actions[]=0&values[]=deferred&columns[]=44&actions[]=0&values[]=dirty&columns[]=44&actions[]=0&values[]=held-nbn""><?=Arr::get($data, 'deferred')  + Arr::get($data, 'dirty')  + Arr::get($data, 'heldnbn')  ? : ''?></a></strong></td>
                    </tr>

                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
</table>