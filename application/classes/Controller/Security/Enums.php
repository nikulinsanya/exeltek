<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Enums extends Controller
{

    public function before()
    {
        parent::before();

        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        $items = DB::select()->from('enumerations')->execute();

        $view = View::factory('Security/Enums')
            ->bind('items', $items);

        $this->response->body($view);
    }

    public function action_load() {
        $id = $this->request->param('id');

        $items = Enums::get_values($id);
        header('Content-type: application/json');
        die(json_encode(array(
            'multi' => Enums::is_multi($id) ? true : false,
            'items' => array_values($items),
        )));
    }

    public function action_save() {
        $id = $this->request->param('id');

        $name = Arr::get($_POST, 'name');
        if (!$name) throw new HTTP_Exception_400('Wrong name');

        $multi = Arr::get($_POST, 'multi') ? 1 : 0;

        if ($id) {
            DB::update('enumerations')->set(array('allow_multi' => $multi, 'name' => $name))->where('id', '=', $id)->execute();
            DB::delete('enumeration_values')->where('id', '=', $id)->execute();
        } else
            $id = Arr::get(DB::insert('enumerations', array('allow_multi', 'name'))->values(array($multi, $name))->execute(), 0);

        $items = Arr::get($_POST, 'items');
        if ($items) {
            $query = DB::insert('enumeration_values', array('enum_id', 'value'));
            foreach ($items as $item)
                $query->values(array($id, $item));
            $query->execute();
        }

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'id' => $id)));
    }
}