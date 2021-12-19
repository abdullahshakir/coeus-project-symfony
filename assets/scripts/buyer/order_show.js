import $ from 'jquery';
import './jquery-ui.min.js';
import 'bootstrap';
import 'datatables.net-bs4';

$(document).ready(function () {
    $('#order-products').DataTable();
});