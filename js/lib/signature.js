$(function () {

    var signature = new SignaturePad(document.querySelector('canvas'));
    
    $('.clear-signature').click(function() {
        $('#signature-checked').prop('checked', false);
        signature.clear();
    });
    
    $('form').submit(function() {
        $('.custom-jobs-container:not(.hidden)').find('[data-validation="required"]').each(function(i,e) {
            if (!$(e).val()) {
                $(e).focus();
                var name = $(e).parent().parent().find('label').first().text();
                name = name.substring(0, name.length - 1);
                alert(name + ' is required. Please, enter some value!');
                return false;
            }
        });
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
        $('.custom-jobs-container').addClass('hidden');
        if (val) {
            $('#fields-' + val).removeClass('hidden');
        }
    });
});