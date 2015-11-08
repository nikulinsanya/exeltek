<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Reports_Forms extends Controller
{

    public function before() {
        parent::before();

        if (!Group::current('show_all_jobs'))
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        $tables = DB::select()->from('reports')->execute()->as_array('id', 'name');

        $geo = false;

        if ($_POST) {
            $id = intval(Arr::get($_POST, 'id'));

            if (!isset($tables[$id])) throw new HTTP_Exception_404('Not found');

            $query = array('report_id' => $id);

            $columns = DB::select('id', 'name', 'type')->from('report_columns')->where('report_id', '=', $query['report_id'])->execute()->as_array('id');

            if ($_POST) foreach ($columns as $column) if (isset($_POST[$column['id']])) {
                $key = $column['id'];
                $type = $column['type'];
                if (isset($_POST[$key]['from']))
                    $query[$key]['$gte'] = Columns::parse($_POST[$key]['from'], $type);

                if (isset($_POST[$key]['to']))
                    $query[$key]['$lte'] = Columns::parse($_POST[$key]['to'], $type);

                if (isset($_POST[$key]['value'])) {
                    $values = explode('|', $_POST[$key]['value']);
                    foreach ($values as $value) if ($value)
                        $query[$key]['$in'][] = new MongoRegex('/' . $value . '/i');
                }
            }
            $query['expires'] = time() + 3600;

            Database_Mongo::collection('search')->insert($query);

            header('Content-type: application/json');
            die(json_encode(array('success' => true, 'id' => strval($query['_id']))));
        } elseif (Arr::get($_GET, 'id')) {
            $query = Database_Mongo::collection('search')->findOne(array('_id' => new MongoId($_GET['id'])));
            if (!$query) $this->redirect('reports/forms');

            $columns = DB::select('id', 'name', 'type')->from('report_columns')->where('report_id', '=', $query['report_id'])->execute()->as_array('id');

            unset($query['_id']);
            unset($query['expires']);

            $result = Database_Mongo::collection('reports')->find($query);

            Pager::$count = $result->count();

            $result->skip(Pager::offset())->limit(Pager::limit());

            $reports = array();
            foreach ($result as $report) {
                $id = strval($report['_id']);
                $data = array(
                    'attachment' => Arr::get($report, 'attachment', 'Unknown file'),
                    'attachment_id' => Arr::get($report, 'attachment_id', 0),
                );
                foreach ($columns as $key => $column)
                    $data[$key] = Arr::get($report, $key) ? Columns::output($report[$key], $column['type']) : '';

                if (isset($report['geo']))
                    $geo = true;

                $reports[$id] = $data;
            }

        } else {
            $columns = array();
            $reports = array();
        }

        $view = View::factory('Reports/Forms')
            ->set('geo', $geo)
            ->bind('tables', $tables)
            ->bind('reports', $reports)
            ->bind('filters', $query)
            ->bind('columns', $columns);

        $this->response->body($view);
    }

    public function action_load() {
        $id = intval(Arr::get($_GET, 'id'));
        $columns = DB::select('id', 'name', 'type')->from('report_columns')->where('report_id', '=', $id)->execute()->as_array('id');

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'columns' => $columns)));
    }

    public function action_search() {
        $query = array(
            'report_id' => intval(Arr::get($_GET, 'id')),
        );
        $columns = DB::select('id', 'name', 'type')->from('report_columns')->where('report_id', '=', $query['report_id'])->execute()->as_array('id');


        if ($_POST) foreach ($columns as $column) if (isset($_POST[$column['id']])) {
            $key = $column['id'];
            $type = $column['type'];
            if (isset($_POST[$key]['from']))
                $query[$key]['$gte'] = Columns::parse($_POST[$key]['from'], $type);

            if (isset($_POST[$key]['to']))
                $query[$key]['$lte'] = Columns::parse($_POST[$key]['to'], $type);

            if (isset($_POST[$key]['value'])) {
                $values = explode('|', $_POST[$key]['value']);
                foreach ($values as $value) if ($value)
                    $query[$key]['$in'][] = new MongoRegex('/' . $value . '/i');
            }
        }
        $result = Database_Mongo::collection('reports')->find($query);

        Pager::$count = $result->count();

        $result->skip(Pager::offset())->limit(Pager::limit());

        $reports = array();
        foreach ($result as $report) {
            $id = strval($report['_id']);
            $data = array(
                'attachment' => Arr::get($report, 'attachment', 'Unknown file'),
                'attachment_id' => Arr::get($report, 'attachment_id', 0),
            );
            foreach ($columns as $key => $column)
                $data[$key] = Arr::get($report, $key) ? Columns::output($report[$key], $column['type']) : '';

            $reports[$id] = $data;
        }

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'columns' => $columns, 'data' => $reports)));
    }

}