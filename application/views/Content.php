<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--    <meta name="viewport" content="width=device-width, initial-scale=1">-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Tickets Management Portal</title>

    <script src="<?=URL::base()?>js/lib/jquery/jquery-1.11.3.min.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/jquery.ui.widget.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/jquery.iframe-transport.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/jquery.fileupload.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/jquery-ui.min.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/jquery.shorten.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/jquery.tabelizer.min.js"></script>

      <link href="<?=URL::base()?>css/tabelizer.css" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="<?=URL::base()?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=URL::base()?>css/dashboard.css" rel="stylesheet">
    <link href="<?=URL::base()?>css/style.css" rel="stylesheet">
    <script src="<?=URL::base()?>js/lib/bootstrap/bootstrap.min.js"></script>



    <script src="<?=URL::base()?>js/lib/bootstrap/selectize.js"></script>
    <link href="<?=URL::base()?>css/selectize.css" rel="stylesheet">

    <script src="<?=URL::base()?>js/lib/moment.js"></script>
    <script src="<?=URL::base()?>js/lib/jquery/daterangepicker.js"></script>
    <link href="<?=URL::base()?>css/daterangepicker.css" rel="stylesheet">

    <script src="<?=URL::base()?>js/lib/bootstrap/bootstrap-multiselect.js"></script>
    <link href="<?=URL::base()?>css/bootstrap-multiselect.css" rel="stylesheet">
      <!-- Latest compiled and minified CSS -->
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/css/bootstrap-select.min.css">

      <!-- Latest compiled and minified JavaScript -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/bootstrap-select.min.js"></script>
    <script src="<?=URL::base()?>js/lib/highcharts/highcharts.src.js"></script>
    <script src="<?=URL::base()?>js/lib/highcharts/exporting.js"></script>
    <script src="<?=URL::base()?>js/lib/highcharts/no-data-to-display.js"></script>
    <script src="<?=URL::base()?>js/lib/highcharts/drilldown.js"></script>

      <script src="<?=URL::base()?>js/lib/wpaint/wPaint.utils.js"></script>
      <script src="<?=URL::base()?>js/lib/wpaint/wPaint.js"></script>
      <link href="<?=URL::base()?>css/wPaint.css" rel="stylesheet">
      <link href="<?=URL::base()?>css/wColorPicker.min.css" rel="stylesheet">
      <script src="<?=URL::base()?>js/lib/wpaint/wColorPicker.min.js"></script>
      <script src="<?=URL::base()?>js/lib/wpaint/plugins/main/src/wPaint.menu.main.js"></script>
      <script src="<?=URL::base()?>js/lib/wpaint/plugins/text/wPaint.menu.text.min.js"></script>
      <script src="<?=URL::base()?>js/lib/wpaint/plugins/shapes/wPaint.menu.main.shapes.min.js"></script>
      <script src="<?=URL::base()?>js/lib/wpaint/plugins/file/wPaint.menu.main.file.min.js"></script>

    <script src="<?=URL::base()?>js/utils.js"></script>
    <script src="<?=URL::base()?>js/app.js"></script>
    <script src="<?=URL::base()?>js/reports.js"></script>


    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js"></script>
    
    <link href="<?=URL::base()?>css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <script src="<?=URL::base()?>js/lib/bootstrap/bootstrap-datetimepicker.min.js"></script>

    <link href="<?=URL::base()?>css/style.min.css" rel="stylesheet">
    <script src="<?=URL::base()?>js/lib/jstree.min.js"></script>

    <script src="<?=URL::base()?>js/lib/jquery/jquery.numeric.min.js"></script>

    <link href="<?=URL::base()?>css/checkbox-x.min.css" rel="stylesheet">
    <script src="<?=URL::base()?>js/lib/checkbox-x.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body data-url="<?= URL::base()?>">
  <div class="modal fade" id="preloaderModal" tabindex="-1" role="dialog">
      <div id="loading"><img src="<?=URL::base()?>img/loading.gif">Loading...</div>

  </div>
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?=URL::base()?>"><img src="<?=URL::base()?>img/logo.png" height="30" alt="Dashboard" /></a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="<?=Request::current()->directory() == '' && Request::current()->controller() == 'Search' ? 'active' : ''?>"><a href="<?=URL::base()?>search">Search</a></li>
              <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dashboard <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                      <li class="<?=Request::current()->controller() == 'Dashboard' && Request::current()->action() == 'reports' ? 'active' : ''?>"><a href="<?=URL::base()?>dashboard/reports">Reports</a></li>
                      <li class="<?=Request::current()->controller() == 'Dashboard' && Request::current()->action() == 'fsa' ? 'active' : ''?>"><a href="<?=URL::base()?>dashboard/fsa">FSA progress</a></li>
                      <li class="<?=Request::current()->controller() == 'Dashboard' && Request::current()->action() == 'lifd' ? 'active' : ''?>"><a href="<?=URL::base()?>dashboard/lifd">LIFD progress</a></li>
                  </ul>
              </li>
              <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Reports <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                      <li class="<?=Request::current()->directory() == 'Reports' && Request::current()->controller() == 'Submissions' ? 'active' : ''?>"><a href="<?=URL::base()?>reports/submissions">Submissions</a></li>
                      <?php if (Group::current('allow_finance')):?>
                          <li class="<?=Request::current()->directory() == 'Reports' && Request::current()->controller() == 'Financial' ? 'active' : ''?>"><a href="<?=URL::base()?>reports/financial">Financial</a></li>
                      <?php endif;?>
                      <?php if (Group::current('allow_custom_forms')):?>
                      <li class="<?=Request::current()->directory() == '' && Request::current()->controller() == 'Form' ? 'active' : ''?>"><a href="<?=URL::base()?>form/unattached">Unattached Forms</a></li>
                      <li class="<?=Request::current()->directory() == 'Reports' && Request::current()->controller() == 'Forms' ? 'active' : ''?>"><a href="<?=URL::base()?>reports/forms">Forms Reports</a></li>
                      <?php endif;?>
                  </ul>
              </li>
            <?php if (Group::current('allow_reports')):?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Import / Export <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li class="<?=Request::current()->directory() == 'Imex' && Request::current()->controller() == 'Upload' ? 'active' : ''?>"><a href="<?=URL::base()?>imex/upload">Jobs upload</a></li>
                    <li class="<?=Request::current()->directory() == 'Imex' && Request::current()->controller() == 'Reports' ? 'active' : ''?>"><a href="<?=URL::base()?>imex/reports">Jobs reports</a></li>
                    <li class="<?=Request::current()->directory() == 'Imex' && Request::current()->controller() == 'Discrepancies' ? 'active' : ''?>"><a href="<?=URL::base()?>imex/discrepancies">SOD Discrepancies</a></li>
                    <li class="<?=Request::current()->directory() == 'Imex' && Request::current()->controller() == 'Export' ? 'active' : ''?>"><a href="<?=URL::base()?>imex/export">Jobs export</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Defects <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li class="<?=Request::current()->directory() == 'Defects' && Request::current()->controller() == 'Upload' ? 'active' : ''?>"><a href="<?=URL::base()?>defects/upload">Defects upload</a></li>
                    <li class="<?=Request::current()->directory() == 'Defects' && Request::current()->controller() == 'Reports' && Request::current()->action() == 'index' ? 'active' : ''?>"><a href="<?=URL::base()?>defects/reports">Defects reports</a></li>
                </ul>
            </li>
            <?php endif;?>
            <?php if (Group::current('is_admin')):?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Security <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Users' ? 'active' : ''?>"><a href="<?=URL::base()?>security/users">Users</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Groups' ? 'active' : ''?>"><a href="<?=URL::base()?>security/groups">Groups</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Companies' ? 'active' : ''?>"><a href="<?=URL::base()?>security/companies">Companies</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Rates' ? 'active' : ''?>"><a href="<?=URL::base()?>security/rates">Rates</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Columns' ? 'active' : ''?>"><a href="<?=URL::base()?>security/columns">Columns</a></li>
                    <li class="<?=Request::current()->directory() == '' && Request::current()->controller() == 'Form' ? 'active' : ''?>"><a href="<?=URL::base()?>form">Forms</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Reports' ? 'active' : ''?>"><a href="<?=URL::base()?>security/reports">Reports</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Structure' ? 'active' : ''?>"><a href="<?=URL::base()?>security/structure">Structure</a></li>
                    <li class="<?=Request::current()->directory() == 'Security' && Request::current()->controller() == 'Enums' ? 'active' : ''?>"><a href="<?=URL::base()?>security/enums">Enumerations</a></li>
                </ul>
            </li>
            <?php endif;?>
            <?php if (Group::current('allow_assign')):?>
            <li class="<?=Request::current()->directory() == '' && Request::current()->controller() == 'Attachments' ? 'active' : ''?>"><a href="<?=URL::base()?>attachments">Attachments</a></li>
            <?php endif;?>
            <li class="divider"></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="javascript:;"><?=User::current('login') . (User::current('company_id') ? ' (' . Company::current('name') . ')' : '')?></a></li>
            <li><a href="<?=URL::base()?>login/deauth">Log out</a></li>
          </ul>
        </div>
      </div>
    </nav>
  <div class="container col-xs-12">
    <div class="content">
        <?php foreach ($notifications as $notification):?>
        <div class="alert alert-<?=$notification['type']?>">
            <a href="javascript:;" class="pull-right text-danger notification" data-id="<?=$notification['id']?>"><span class="glyphicon glyphicon-remove"></span></a>
            <div><?=$notification['message']?></div>
        </div>
        <?php endforeach;?>
        <?=Messages::render();?>
        <?=$content?>
    </div>
  </div>
  <div class="modal fade" id="modalHtmlContainer" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title">Details</h4>
              </div>
              <div class="modal-body">
              </div>
          </div>
      </div>
  </div>
  <div class="modal fade" id="modal-download" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">Preparing attachments...</h4>
              </div>
              <div class="modal-body">
                  Files count: <b><span id="download-count">0</span></b>/<b><span id="download-count-total">0</span></b><br/>
                  Total size: <b><span id="download-size">0 B</span></b>/<b><span id="download-size-total">0</span></b><br/>
              </div>
          </div>
      </div>
  </div>

  <?php if (Kohana::$environment == Kohana::DEVELOPMENT) echo '<div>&nbsp;</div>' . View::factory("profiler/stats");?>
  </body>
</html>
