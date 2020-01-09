(function(global) {

    var companyCallApiKey = $('meta[name="company-call-api-key"]').attr('content');
    var io = global.io('https://notice.teo-crm.ru:3000', { secure: true, query: 'companyCallApiKey='+companyCallApiKey });

    if(Notification.permission !== 'granted')
    {
        Notification.requestPermission();
    }

    io.on('message', function(data){
        var alert = $('[data-template="alert"]').clone();
        // var alert_btn = $('#resident-btn').clone();
        // var elements = $('[data-template="elements"]').clone();
        // elements.removeAttr('data-template');
        $('.content').prepend(alert.removeAttr('data-template').text('Входящий вызов от '+data.phone).show());
        $('#warning-call-btn').show();

        var alertBtn = $('[data-template="elements"]').clone();
        alertBtn.removeAttr('data-template');

        if (data.is_resident === 'true'){
            alertBtn.find('.btn').text('Открыть карточку жильца');
            alertBtn.find('.btn').attr('href', data.url);
            alertBtn.find('.btn').show();
            alert.append(alertBtn);
        } else {
            alertBtn.find('.btn').text('Создать карточку жильца');
            alertBtn.find('.btn').attr('href', data.url);
            alertBtn.find('.btn').show();
            alert.append(alertBtn);
        }

        // $.post(
        //     '/call/create-incoming-call',
        //     {
        //         number: msg
        //     },
        //     function (response) {
        //         console.log(response[1]);
        //         if (response[0] === 'success'){
        //             if (response['is_resident'] === 'true'){
        //                 alert_btn.attr('href', response['url']);
        //                 alert_btn.show();
        //                 alert.append(elements);
        //             }
        //         }
        //     }
        // );

        // $.ajax({
        //     'url': 'call/create-incoming-call',
        //     'data': {'number': msg},
        //     'method': 'POST',
        //     success: function(response){
        //         console.log(response[1]);
        //         var alertBtn = $('[data-template="elements"]').clone();
        //         alertBtn.removeAttr('data-template');
        //         // if (response[0] == 'success'){
        //             if (response['is_resident'] === 'true'){
        //                 alertBtn.find('.btn').text('Открыть карточку жильца');
        //                 alertBtn.find('.btn').attr('href', response['url']);
        //                 alertBtn.find('.btn').show();
        //                 alert.append(alertBtn);
        //             } else {
        //                 alertBtn.find('.btn').text('Создать карточку жильца');
        //                 alertBtn.find('.btn').attr('href', response['url']);
        //                 alertBtn.find('.btn').show();
        //                 alert.append(alertBtn);
        //             }
        //         // }
        //     }
        // });

        var notification = new Notification('Новый входящий вызов', {body: 'Входящий вызов от ' + data.phone});
    });

    // (new Notification(''));
})(window);