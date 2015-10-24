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
        h3 {
            text-align: center;
        }
    </style>
</head>
<body>

<div style="border:1px solid #f7f7f7;">
    <h3><?=$name?></h3>
        <?php foreach ($form as $table):?>
            <table>
                <?php foreach ($table['data'] as $cells): echo '<tr>';
                    foreach ($cells as $input): echo '<td>';
                        $value = Arr::get($input, 'value');
                        switch (Arr::get($input, 'type')):
                            case 'label':
                                echo '<span>' . Arr::get($input, 'placeholder') . '</span>';
                                break;
                            case 'signature':
                                echo '<img src="' . $value . '" />';
                                break;
                            case 'select':
                                echo '<span>' . (is_array($value) ? implode(', ',$value) : $value) . '</span>';
                                break;
                            default:
                                echo '<span>' . $value . '</span>';
                                break;
                        endswitch;
                        echo '</td>';
                    endforeach;
                    echo '</tr>';
                endforeach;
                echo '</table>';
            endforeach;?>
</div>
</body>
</html>
