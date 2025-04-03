require(['jquery', 'Magento_Ui/js/modal/modal'], function($) {
    $(document).on('click', '.ajax', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');

        $.get(url, function(data) {
            $('#order-status-count').html('<table><tr><th>Status</th><th>Count</th></tr>');
            $.each(data, function(status, count) {
                $('#order-status-count table').append('<tr><td>' + status + '</td><td>' + count + '</td></tr>');
            });
            $('#order-status-count').append('</table>');
        });
    });
});
