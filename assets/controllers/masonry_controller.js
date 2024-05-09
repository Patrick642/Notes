import { Controller } from '@hotwired/stimulus';
import Masonry from 'masonry-layout';

export default class extends Controller {
    connect() {
        new Masonry(this.element, {
            itemSelector: '.note-container'
        });
    }
}