/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('../css/app.css');
const $ = require('jquery');
global.$ = global.jQuery = $;
require('bootstrap');

$('.custom-file-input').on('change',function(){
    var fileName = $(this).val().split("\\").pop();
    $(this).next('.custom-file-label').html(fileName);
});
