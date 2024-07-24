import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    connect() {
        let messageLength = $(this.element).text().trim().length;
        let delay = messageLength * 100;

        $(this.element).delay(delay).fadeOut(300, function () {
            $(this).remove();
        });
    }
}