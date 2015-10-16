<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <style type="text/css">
        @page { sheet-size: A4; }

        html, body {
            height: 100%;
        }

        .page-header{
            border-bottom: none;
        }

        .form-container{
            display: inline-block;
            width: 70%;
            float: left;
            padding: 0px 10px;
        }
        .form-block{
            display: inline-block;
            margin-left: 5px;
            height: 40px;
            vertical-align: middle;
        }
        .form-configuration-container{
            display: inline-block;
            max-width: 30%;
            border-radius: 5px;
            float: right;
            margin-top: 10px ;
        }

        .fields-config{
            display: none;
        }

        .form-configuration-container>div{
            padding: 7px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .container{
            height: 100%;
            width: 100%;
        }

        .form-row{
            padding: 7px 10px 10px 10px;
            width: 100%;
            border: 1px solid #F7F7F7;
            margin: 1px 0px;
        }

        .label-input,
        .value-input{
            display: none;
        }

        .value{
            display: inline-block;
            width: 150px;
            height: 25px;
            text-align: left;
            box-shadow:  0px 0px 1px #ccc;
            float: left;
            margin-right: 3px;
            line-height: 25px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

        }

        .value.selected{
            box-shadow: 0px 0px 30px 1px #7FFD7F;
        }

        .value input[type="text"]{
            height: 25px;
        }

        .value-input{
            /*padding: 5px;*/
        }
        .tmp-label{
            max-width: 2000px;
            min-width: 200px;
            overflow: hidden;
            display: inline-block;
            padding: 0px 5px;
            line-height: 25px;
            height: 25px;
            font-weight: bold;
        }
        #form-data .tmp-label{
            min-width: 100px;
            margin-top: 5px;
        }

        .form-btns-container{
            display: none;
        }

        .config-select-container{
            border: 1px solid #f7f7f7;
            padding: 5px;
        }
        .available-options-select{
            width: 150px;
            display: none;
        }

        .remove-field{
            margin-right: 5px;
        }


        .form-block .value .btn-group, .form-block .value .btn-group button{
            width:150px;
            line-height: 12px;
            height: 25px;
        }

        hr{
            margin: 1px 0px;
            border-top: 1px solid #333;
        }

        .remove-hr{
            margin: 0px 2px 2px 13px;
        }

        .kohana{
            display: none;
        }
        .val input[type="text"]{
            width:150px;
        }

        .remove-line{
            float: right;
        }

        .submited .value,
        .submited .value.selected{
            box-shadow: none;
        }
        .submited .value[data-type="label"]{
            box-shadow: none;
        }
        .submited .form-row{
            border:none;
            height: 37px;
        }
        .value[data-type="label"]{
            width: auto;
            min-width: 150px;
        }
        .value[data-type="canvas"]{
            width: 300px;
            height: 59px;
            margin-top: -7px;
        }
        .value[data-type="date"],.value[data-type="text"]{
            margin-top: 5px;
        }

        .form-container.submited{
            width: 100%;
        }

        #newFormContainer{
            display: none;
        }
        h3 {
            text-align: center;
        }
    </style>
</head>
<body>
<div style="border:1px solid #f7f7f7;">
    <h3><?=$name?></h3>
        <?php foreach ($form as $line) if (is_string($line)):
                echo $line;
            else:
                echo '<div style="padding:20px 5px;"><table><tr>';
                foreach ($line as $input):
                    echo '<td>';
                    $value = Arr::get($input, 'value');
                    switch (Arr::get($input, 'type')):
                        case 'label':
                            echo '<div><b><label class="tmp-label">' . $value . '</label></b></div>';
                            break;
                        case 'canvas':
                            echo '<img src="' . $value . '" />';
                            break;
                        default:
                            echo '<span>' . $value . '</span>';
                            break;
                    endswitch;
                    echo '<td>';
                endforeach;
                echo "</tr></table></div>";
            endif;?>
</div>
</body>
</html>