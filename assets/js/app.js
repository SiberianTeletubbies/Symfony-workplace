require('../css/app.css');
const $ = require('jquery');
global.$ = global.jQuery = $;
require('bootstrap');

$('.custom-file-input').on('change',function(){
    var fileName = $(this).val().split("\\").pop();
    $(this).next('.custom-file-label').html(fileName);
});
