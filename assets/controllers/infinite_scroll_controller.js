import { Controller } from '@hotwired/stimulus';
import Masonry from 'masonry-layout';
import $ from 'jquery';

export default class extends Controller {
    load() {
        $('#infiniteScrollButton').prop('disabled', true);
        let offset = $('.note-container').length;

        $.ajax({
            type: "GET",
            url: $('#infiniteScrollButton').data('url') + "?offset=" + offset,
            cache: false,
            success: function (out) {
                if (out.success) {
                    $('.row.notes-container').append(out.render);

                    // Remove 'Load more notes' button after loading all user's notes
                    if (out.isLast) {
                        $('#infiniteScrollButton').closest('.col').remove();
                    }
                }

                // Recollects all container elements
                new Masonry(document.querySelector('.row.notes-container'), {
                    itemSelector: '.note-container'
                }).reloadItems();

                $('#infiniteScrollButton').prop('disabled', false);
            }
        });
    }
}