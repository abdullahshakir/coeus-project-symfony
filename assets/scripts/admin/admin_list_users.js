import $ from 'jquery';
global.jQuery = $;
global.$ = $;
import 'bootstrap';
import 'datatables.net-bs4';

$(document).ready(function () {
    $('#users').DataTable();
});