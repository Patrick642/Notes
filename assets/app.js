import './styles/app.css';
import 'bootswatch/dist/solar/bootstrap.min.css';
import $ from 'jquery';
import 'bootstrap';
import './bootstrap.js';
window.$ = window.jQuery = $;

var currrentNote;

$(function () {
    /* Change color of the card. */
    $(document).on('change', '.note-color-change input', function () {
        let color = $(this).val();
        $(this).closest('.card').removeClass(function (index, className) {
            return (className.match(/\bbg-\S+/g) || []).join(' ');
        }).addClass(color);
    });

    /* Get the action url with the note ID and set it as action attribute to the form. */
    $(document).on('click', '[data-bs-target="#modal-edit-note"], [data-bs-target="#modal-delete-note"]', function () {
        currrentNote = $(this).closest('.note-container');

        let action = $(this).data('action');
        let formId;

        switch ($(this).attr('data-bs-target')) {
            case '#modal-edit-note':
                formId = '#edit-note';
                getEditNoteForm($(this).data('id'));
                break;
            case '#modal-delete-note':
                formId = '#delete-note';
                break;
        }

        $('form' + formId).attr('action', action);
    });

    /* An SVG spinner */
    const spinner = '<svg width="64" height="64" fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_z9k8{transform-origin:center;animation:spinner_StKS .75s infinite linear}@keyframes spinner_StKS{100%{transform:rotate(360deg)}}</style><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z" class="spinner_z9k8"/></svg>';

    /* Get the edit note form from controller and append it. */
    function getEditNoteForm(id) {
        $('#modal-edit-note .modal-content').html('<div class="mx-auto my-auto">' + spinner + '</div>');

        $.ajax({
            type: 'POST',
            url: '/notes/get_note/' + id,
            data: {
                id: id
            },
            success: function (out) {
                $('#modal-edit-note .modal-content').html(out);
            }
        });
    }
});