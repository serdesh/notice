var app = require('express')();
const log = require('simple-node-logger').createSimpleLogger('server.log');
const fs = require('fs');
const cors = require('cors');
const bodyParser = require('body-parser');
const $request = require('request');

// $request({
//     url: 'https://ads.asusmkd.ru/api/v1/add-incoming-call',
//     method: 'POST',
//     json: true,
//     body: {number: 'super'},
// }, function(error, response, data){
//     console.log(data);
// });

// $request.post(
//                 'https://ads.asusmkd.ru/api/v1/add-incoming-call',
//                 {phone: 'super'},
//                 function(error, response, data){
//                     console.log(data);
//                     if(clients[companyCallApiKey] != undefined){
//                         for(let i = 0; i < clients[companyCallApiKey].length; i++)
//                         {
//                             clients[companyCallApiKey][i].emit('message', body.from.number);
//                         }
//                     }
//                 }
//             );

app.use(bodyParser.urlencoded({ extended: false }));
app.use(cors({
    origin: true,
    credentials: true,
}));

var privateKey = fs.readFileSync(__dirname+'/key.pem');
var certificate = fs.readFileSync(__dirname+'/cert.pem');

var https = require('https').createServer({
    key: privateKey,
    cert: certificate
}, app);

var server = https.listen(3000, function(){
    log.info('Server started');
});

const io = require('socket.io').listen(server);

var callsEntryId = [];

// app.post('/api/megapbx', function(request, response){

//     log.info('call action from megapbx');

//     let companyCallApiKey = request.body.vpbx_api_key;
//     let body = JSON.parse(request.body.json);
//     let entryId = body.entry_id;

//     if(callsEntryId.includes(entryId) == false)
//     {
//         // io.emit('message', body.from.number);
//         $request({
//             url: 'https://ads.asusmkd.ru/api/v1/add-incoming-call',
//             method: 'POST',
//             json: true,
//             body: {number: body.from.number, token: request.body.vpbx_api_key},
//         }, function(error, response, data){
//             console.log(data);
//             if(clients[companyCallApiKey] != undefined){
//                 for(let i = 0; i < clients[companyCallApiKey].length; i++)
//                 {
//                     clients[companyCallApiKey][i].emit('message', data);
//                 }
//             }
//         });
//         // $request.post(
//         //         'https://notice.teo-crm.ru/api/v1/add-incoming-call',
//         //         {phone: body.from.number},
//         //         function(error, response, data){
//         //             console.log(data);
//         //         }
//         //     );

//         log.info('call action send to http server');
//         callsEntryId.push(entryId);
//     }

//     response.send('ok');
// });

app.post('/api/events/call', function(request, response){

    log.info('call action');

    if(request.body.cmd != undefined){ // MEGAPBX

        if(request.body.cmd == 'contact'){
            let phone = request.body.phone;
            let companyCallApiKey = request.body.crm_token;

            $request({
                url: 'https://ads.asusmkd.ru/api/v1/add-incoming-call',
                method: 'POST',
                json: true,
                body: {number: phone, token: companyCallApiKey},
            }, function(error, response, data){
                console.log(data);
                if(clients[companyCallApiKey] != undefined){
                    for(let i = 0; i < clients[companyCallApiKey].length; i++)
                    {
                        clients[companyCallApiKey][i].emit('message', data);
                    }
                }
            });

            response.send('ok');
        }

    } else if(request.body.vpbx_api_key != undefined) { // MANGO
        let companyCallApiKey = request.body.vpbx_api_key;
        let body = JSON.parse(request.body.json);
        let entryId = body.entry_id;

        if(callsEntryId.includes(entryId) == false)
        {
            // io.emit('message', body.from.number);
            $request({
                url: 'https://ads.asusmkd.ru/api/v1/add-incoming-call',
                method: 'POST',
                json: true,
                body: {number: body.from.number, token: request.body.vpbx_api_key},
            }, function(error, response, data){
                console.log(data);
                if(clients[companyCallApiKey] != undefined){
                    for(let i = 0; i < clients[companyCallApiKey].length; i++)
                    {
                        clients[companyCallApiKey][i].emit('message', data);
                    }
                }
            });
            // $request.post(
            //         'https://ads.asusmkd.ru/api/v1/add-incoming-call',
            //         {phone: body.from.number},
            //         function(error, response, data){
            //             console.log(data);
            //         }
            //     );

            log.info('call action send to http server');
            callsEntryId.push(entryId);
        }

        response.send('ok');
    }
});



var clients = [];

io.sockets.on('connection', function(socket){
    var data = socket.request;

    log.info('Connected unkown user');

    if(data._query['companyCallApiKey'] !== undefined)
    {
        var apiKey = data._query['companyCallApiKey'].toString();
        console.log(apiKey);
        if(apiKey != '')
        {
            log.info('User company call api key: '+apiKey+'');

            if(clients[apiKey] !== undefined){
                clients[apiKey].push(socket);
            } else {
                clients[apiKey] = [];
                clients[apiKey].push(socket);
            }
            console.log(clients);
        }
    }

});