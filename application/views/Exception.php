<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tickets Management Portal</title>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <!-- Bootstrap -->
    <link href="<?=URL::base()?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=URL::base()?>css/style.css" rel="stylesheet">
    <script src="<?=URL::base()?>js/bootstrap.min.js"></script>
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="container col-xs-12">
    <div class="content text-center">
        <div class="col-md-3 col-lg-4">&nbsp;</div>
        <div class="col-xs-12 col-md-6 col-lg-4">
            <div class="jumbotron">
                <img src="<?=URL::base()?>img/logo.png" />
                <?php if (in_array($code, array(403, 404), true)):?>
                <h2><?=$code?>: <?=$message?></h2>
                <?php else:?>
                <h3>Oops... Something wrong happened!</h3>
                <?php endif;?>
            </div>
        </div>
    </div>
  </div>
  </body>
</html>
