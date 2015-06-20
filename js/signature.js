$(function () {

    var signature = new SignaturePad(document.querySelector('canvas'));
    
    $('.clear-signature').click(function() {
        $('#signature-checked').prop('checked', false);
        signature.clear();
    });
    
    $('form').submit(function() {
        if ($('#signature-checked').prop('checked')) {
            $('#signature-checked').prop('checked');
            $('#signature-checked').parent('label').removeClass('text-danger')
            if (signature.isEmpty()) {
                $('#signature-warning').addClass('text-danger');
                return false;
            } else {
                $('#signature-warning').removeClass('text-danger');
                $('#signature-checked').prop('checked', true);
                $('#signature-checked').parents('label').removeClass('text-danger');
                $('#signature').val(signature.toDataURL());
            }
        } else {
            $('#signature-checked').parent('label').addClass('text-danger');
            return false;
        }
    });
    
    $('#job-completed').quickChange(function() {
        var val = $(this).val();
        $('.fields-group').addClass('hidden');
        if (val) {
            $('#fields-' + val).removeClass('hidden');
        }
    });
});