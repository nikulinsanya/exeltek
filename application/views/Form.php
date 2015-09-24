
<div class="page-header" style="margin: 30px 0px 0px;">
    <h1 style="margin-left: 15px;">Form builder</h1>
</div>

<div class="container" id="formBuildeContainer">

</div>
<div id="newFormContainer"></div>

<link href="<?=URL::base()?>css/forms/form.css" rel="stylesheet">
<script src="<?=URL::base()?>js/lib/signature_pad.min.js"></script>

<script src="<?=URL::base()?>js/forms/form.js"></script>

<script type="application/javascript">
    $(document).on('ready',function(){
        var json = [
            [
                {
                    "type": "label",
                    "value": "text:"
                },
                {
                    "type": "text",
                    "placeholder": "111",
                    "value": "2222",
                    "name": "f552f5cc-a925-0f85-37af-cb7706736f64"
                }
            ],
            "<hr>",
            [
                {
                    "type": "label",
                    "value": "date:"
                },
                {
                    "type": "date",
                    "placeholder": "11111",
                    "value": "",
                    "name": "5a2d4ee9-b6ed-7d8b-260d-0a202438e2d9"
                }
            ],
            "<hr>",
            [
                {
                    "type": "label",
                    "value": "sign:"
                },
                {
                    "type": "canvas",
                    "name": "94c8ad08-b25e-87fe-1436-989ecd73e366"
                }
            ],
            "<hr>",
            [
                {
                    "type": "label",
                    "value": "select"
                },
                {
                    "type": "select",
                    "multiple": false,
                    "values": {
                        "0": "12",
                        "1": "123313",
                        "length": 2,
                        "prevObject": {
                            "0": {},
                            "1": {},
                            "length": 2,
                            "prevObject": {
                                "0": {
                                    "0": {},
                                    "1": {},
                                    "jQuery111306564710114616901": 106
                                },
                                "length": 1,
                                "prevObject": {
                                    "0": {},
                                    "context": {},
                                    "length": 1
                                },
                                "context": {},
                                "selector": "select"
                            },
                            "context": {},
                            "selector": "select option"
                        },
                        "context": {}
                    },
                    "name": "a3544343-65b9-5dff-398e-a8e0e015357b"
                }
            ],
            "<hr>",
            [
                {
                    "type": "label",
                    "value": "tick"
                },
                {
                    "type": "ticket",
                    "fieldId": "252"
                }
            ]
        ];
        form.init($('#formBuildeContainer'),json);
    });
</script>