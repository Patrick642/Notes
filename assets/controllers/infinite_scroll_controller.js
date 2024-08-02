import { Controller } from '@hotwired/stimulus';
import Masonry from 'masonry-layout';
import $ from 'jquery';

export default class extends Controller {
    loading = false;
    isLast = ($('#infiniteScrollData').length === 0) ? true : false;

    connect() {

        // In case if the page loads when a user has already scrolled to the bottom of the page, or if they have a large monitor (so there is no scrollbar and they cannot scroll), load more bookmarks.
        this.#checkScrollAndLoadData();
    }

    scroll() {
        this.#checkScrollAndLoadData();
    }

    // Load more bookmarks and append it.
    #loadMoreData() {
        const self = this;

        $('#infiniteScrollSpinner').removeClass('d-none');
        this.loading = true;
        let offset = $('.note-container').length;

        $.ajax({
            type: 'GET',
            url: $('#infiniteScrollData').data('url'),
            data: {
                offset: offset
            },
            cache: false,
            success: function (out) {
                if (out.success) {
                    $('.row.notes-container').append(out.render);
                    self.loading = false;
                    if (out.isLast)
                        self.isLast = true;

                    self.#checkScrollAndLoadData();
                }

                // Recollects all container elements.
                new Masonry(document.querySelector('.row.notes-container'), {
                    itemSelector: '.note-container'
                }).reloadItems();

                $('#infiniteScrollSpinner').addClass('d-none');
            }
        });
    }

    // Check if a user cannot scroll, if loading is not already in progress, and if a user has more bookmarks.
    #checkScrollAndLoadData() {
        if (Math.ceil($(window).scrollTop() + $(window).height()) >= $(document).height() && !this.loading && !this.isLast) {
            this.#loadMoreData();
        }
    }
}