<nav>
    <ul class="pagination">
        <?php if (Pager::pages() > 1):?>
        <li <?=Pager::page() > 1 ? '' : 'class="disabled"'?>>
            <a href="<?=URL::query(array('page' => max(1, Pager::page() - 1)))?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
        </li>
        <?php for ($i = -3; $i<4; $i++) if (Pager::page() + $i > 0 && Pager::page() + $i <= Pager::pages()): $page = Pager::page() + $i;?>
        <li <?=$i ? '' : 'class="active"'?>><a href="<?=URL::query(array('page' => $page))?>"><?=$page?></a></li>
        <?php endif;?>
        <li <?=Pager::page() < Pager::pages() ? '' : 'class="disabled"'?>>
            <a href="<?=URL::query(array('page' => min(Pager::pages(), Pager::page() + 1)))?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
        </li>
        <?php endif;?>
        <li>
            <span>Total <?=Pager::$count?> records</span>
        </li>
        <li><span>Items on page:</span></li>
        <?php foreach (Pager::$counts as $count):?>
        <li <?=$count == Pager::limit() ? 'class="disabled"' : ''?>><a href="javascript:;" class="pager-count " data-value="<?=$count?>"><?=$count?></a></li>
        <?php endforeach;?>
    </ul>
</nav>