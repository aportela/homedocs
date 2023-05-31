const template = function () {
    return `
        <div class="container">
            <hr>
            <nav class="pagination has-text-weight-normal is-left" role="navigation" aria-label="pagination">
                <button type="button" class="pagination-previous" title="Click for navigate to previous page" v-show="isEnabled" v-bind:disabled="disabled || this.data.currentPage <= 1" v-on:click.prevent="navigateToPreviousPage"><span class="icon is-small"><i class="fas fa-caret-left"></i></span> <span>Previous page</span></button>
                <button type="button" class="pagination-next" title="Click for navigate to next page" v-show="isEnabled" v-bind:disabled="disabled || this.data.currentPage >= this.data.totalPages" v-on:click.prevent="navigateToNextPage">Next page <span class="icon is-small"><i class="fas fa-caret-right"></i></span></button>
                <ul class="pagination-list is-hidden-mobile" v-show="isEnabled">
                    <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
                    <li v-for="pageNumber in data.totalPages" v-if="pageNumber < 3 || Math.abs(pageNumber - data.currentPage) < 3 || data.totalPages - 2 < pageNumber">
                        <a href="#" title="Click for navigate to this index page" v-bind:disabled="disabled" v-on:click.prevent="navigateToCustomPage(pageNumber)" class="pagination-link" v-bind:class="{ 'is-current': data.currentPage === pageNumber }">{{ pageNumber }}</a>
                    </li>
                </ul>
                <div class="field has-addons is-hidden-mobile">
                    <div class="control has-icons-left">
                        <div class="select">
                            <select v-model.number="resultsPage" v-bind:disabled="disabled">
                                <option value="1">1 result/page</option>
                                <option value="2">2 results/page</option>
                                <option value="4">4 results/page</option>
                                <option value="8">8 results/page</option>
                                <option value="16">16 results/page</option>
                                <option value="32">32 results/page</option>
                                <option value="64">64 results/page</option>
                                <option value="128">128 results/page</option>
                                <option value="256">256 results/page</option>
                                <option value="256">512 results/page</option>
                                <option value="0">All results (disable pagination)</option>
                            </select>
                        </div>
                        <span class="icon is-medium is-left">
                            <i class="fas fa-list-ol"></i>
                        </span>
                    </div>
                    <div class="control">
                        <span class="button is-static">Page {{ data.currentPage }} of {{ data.totalPages }} ({{ data.totalResults }} total result/s)</span>
                    </div>
                </div>
            </nav>
            <hr>
        </div>
    `;
};

export default {
    name: 'homedocs-control-pagination',
    template: template(),
    data: function () {
        return ({
            resultsPage: initialState.defaultResultsPage
        });
    },
    props: [
        'disabled',
        'data'
    ],
    created: function () {
        this.resultsPage = this.data.resultsPage;
    },
    computed: {
        visible: function () {
            return (this.data && this.data.totalPages > 1);
        },
        invalidPage: function () {
            return (this.data.totalPages > 0 &&
                (this.data.currentPage < 1 || this.data.currentPage > this.data.totalPages)
            );
        },
        isEnabled: function () {
            return (this.resultsPage != 0);
        }
    },
    watch: {
        resultsPage: function (v) {
            this.$emit("change", { currentPage: 1, resultsPage: parseInt(v) });
        }
    },
    methods: {
        navigateToPreviousPage: function () {
            if (this.data.currentPage > 1) {
                this.$emit("change", { currentPage: this.data.currentPage - 1, resultsPage: this.data.resultsPage });
            }
        },
        navigateToNextPage: function () {
            if (this.data.currentPage < this.data.totalPages) {
                this.$emit("change", { currentPage: this.data.currentPage + 1, resultsPage: this.data.resultsPage });
            }
        },
        navigateToCustomPage: function (pageIdx) {
            if (pageIdx > 0 && pageIdx <= this.data.totalPages) {
                this.$emit("change", { currentPage: pageIdx, resultsPage: this.data.resultsPage });
            }
        }
    }
}