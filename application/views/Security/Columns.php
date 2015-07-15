<table class="table table-striped" data-url="<?=URL::base()?>security/columns/">
    <?php $cnt = 0; foreach (Columns::get_all() as $id => $name):?>
    <?php if ($cnt % 20 == 0):?>
    <tr>
        <th rowspan="2">&nbsp;</th>
        <th colspan="2" class="text-center">Admin</th>
        <?php foreach($groups as $group):?>
            <th colspan="2" class="text-center"><?=HTML::chars($group)?></th>
        <?php endforeach;?>
    </tr>
    <tr>
        <th>Search</th>
        <th>Persistent</th>
        <?php foreach($groups as $group):?>
            <th class="text-center">Job view</th>
            <th class="text-center">Search</th>
        <?php endforeach;?>
    </tr>
    <?php endif;?>
    <tr>
        <th class="text-right"><?=HTML::chars($name)?></th>
        <td class="text-left">
            <?=Form::select(NULL, array(0 => 'Don\'t show', 1 => 'Simple filtering', 2 => 'Advanced filtering'), Columns::get_static($id), array('class' => 'column-show', 'data-id' => $id))?>
        </td>
        <td class="text-center">
            <?=Form::checkbox(NULL, NULL, Columns::get_persistent($id) ? true : false, array('class' => 'column-persistent', 'data-id' => $id))?>
        </td>
        <?php foreach ($groups as $group => $name): $value = Arr::path($permissions, array($group, $id)); $value2 = Arr::path($search, array($group, $id));?>
            <td class="text-center">
                <?=Form::select(NULL, Columns::$states, $value, array('class' => 'column-permission', 'data-id' => $id, 'group-id' => $group))?>
            </td>
            <td class="text-center">
                <?=Form::select(NULL, Columns::$searches, $value2, array('class' => 'column-search', 'data-id' => $id, 'group-id' => $group))?>
            </td>
        <?php endforeach;?>
    </tr>
    <?php $cnt++; endforeach;?>
    <tr>
        <th rowspan="2">&nbsp;</th>
        <th>Search</th>
        <th>Persistent</th>
        <?php foreach($groups as $name):?>
            <th class="text-center">Job view</th>
            <th class="text-center">Search</th>
        <?php endforeach;?>
    </tr>
    <tr>
        <th colspan="2" class="text-center">Admin</th>
        <?php foreach($groups as $name):?>
            <th colspan="2" class="text-center"><?=HTML::chars($name)?></th>
        <?php endforeach;?>
    </tr>
</table>