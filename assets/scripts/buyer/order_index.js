import $ from 'jquery';
import './jquery-ui.min.js';
import 'bootstrap';
import './datatables.bootstrap4.min.js';

$(document).ready(function () {
    $('#orders').DataTable();
});