import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    edit() {
        // An SVG spinner made by Utkarsh Verma https://github.com/n3r4zzurr0
        const spinner = '<svg width="64" height="64" fill="#ffffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_z9k8{transform-origin:center;animation:spinner_StKS .75s infinite linear}@keyframes spinner_StKS{100%{transform:rotate(360deg)}}</style><path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z" class="spinner_z9k8"/></svg>';
        $('#modal-edit-note .modal-content').html('<div class="mx-auto my-auto">' + spinner + '</div>');
        let action = $(this.element).data('url');

        // Get the edit note form from controller and append it to modal window.
        $.ajax({
            type: 'GET',
            url: action,
            success: function (out) {
                if (out.success) {
                    $('#modal-edit-note .modal-content').html(out.formEdit);
                }
            }
        });
    }

    delete() {
        let action = $(this.element).data('url');
        $('form#delete-note').attr('action', action);
    }

    changeColor() {
        let color = $(this.element).val();
        $(this.element).closest('.card').removeClass(function (index, className) {
            return (className.match(/\bbg-\S+/g) || []).join(' ');
        }).addClass(color);
    }
}