import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    connect() {
        $(this.element).delay(2000).fadeOut(300, function () {
            $(this).remove();
        });
    }
}