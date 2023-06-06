const template = function () {
  return `
        <div class="container">
            <hr>
            <nav class="pagination has-text-weight-normal is-left" role="navigation" aria-label="pagination">
                <button type="button" class="pagination-previous" :title="$t('components.pager.clickPreviousNavigationPage')" v-show="isEnabled" v-bind:disabled="disabled || this.data.currentPage <= 1" v-on:click.prevent="navigateToPreviousPage"><span class="icon is-small"><i class="fas fa-caret-left"></i></span> <span>{{ $t("components.pager.previousPage") }}</span></button>
                <button type="button" class="pagination-next" :title="$t('components.pager.clickNextNavigationPage')" v-show="isEnabled" v-bind:disabled="disabled || this.data.currentPage >= this.data.totalPages" v-on:click.prevent="navigateToNextPage">{{ $t("components.pager.nextPage") }} <span class="icon is-small"><i class="fas fa-caret-right"></i></span></button>
                <ul class="pagination-list is-hidden-mobile" v-show="isEnabled">
                    <!-- vuejs pagination inspired by Jeff (https://stackoverflow.com/a/35706926) -->
                    <li v-for="pageNumber in visiblePages">
                        <a href="#" title="Click for navigate to this index page" v-on:click.prevent="navigateToCustomPage(pageNumber)" class="pagination-link" v-bind:class="{ 'is-current': data.currentPage === pageNumber, 'disabled': disabled }">{{ pageNumber }}</a>
                    </li>
                </ul>
                <div class="field has-addons is-hidden-mobile">
                    <div class="control has-icons-left">
                        <div class="select">
                            <select v-model.number="resultsPage" v-bind:disabled="disabled">
                                <option value="1">1 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="2">2 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="4">4 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="8">8 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="16">16 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="32">32 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="64">64 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="128">128 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="256">256 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="256">512 {{ $t("components.pager.resultsPage") }}</option>
                                <option value="0">{{ $t("components.pager.allResultsDisablePagination") }}</option>
                            </select>
                        </div>
                        <span class="icon is-medium is-left">
                            <i class="fas fa-list-ol"></i>
                        </span>
                    </div>
                    <div class="control">
                        <span class="button is-static">{{ $t("components.pager.page") }} {{ data.currentPage }} {{ $t("components.pager.of") }} {{ data.totalPages }} ({{ data.totalResults }} {{ $t("components.pager.totalResults") }})</span>
                    </div>
                </div>
            </nav>
            <hr>
        </div>
    `;
};

export default {
  name: "homedocs-control-pagination",
  template: template(),
  data: function () {
    return {
      resultsPage: initialState.defaultResultsPage,
      visiblePages: [],
    };
  },
  props: ["disabled", "data"],
  created: function () {
    this.resultsPage = this.data.resultsPage;
  },
  computed: {
    totalPages: function () {
      return this.data.totalPages;
    },
    currentPage: function () {
      return this.data.currentPage;
    },
    visible: function () {
      return this.data && this.data.totalPages > 1;
    },
    invalidPage: function () {
      return (
        this.data.totalPages > 0 &&
        (this.data.currentPage < 1 ||
          this.data.currentPage > this.data.totalPages)
      );
    },
    isEnabled: function () {
      return this.resultsPage != 0;
    },
  },
  watch: {
    resultsPage: function (v) {
      this.$emit("change", { currentPage: 1, resultsPage: parseInt(v) });
    },
    currentPage: function (v) {
      this.visiblePages = [
        ...Array(this.totalPages + 1).keys(this.totalPages),
      ].filter(
        (pageNumber) =>
          pageNumber > 0 &&
          (pageNumber < 3 ||
            Math.abs(pageNumber - this.data.currentPage) < 3 ||
            this.data.totalPages - 2 < pageNumber)
      );
    },
    totalPages: function (v) {
      this.visiblePages = [
        ...Array(this.totalPages + 1).keys(this.totalPages),
      ].filter(
        (pageNumber) =>
          pageNumber > 0 &&
          (pageNumber < 3 ||
            Math.abs(pageNumber - this.data.currentPage) < 3 ||
            this.data.totalPages - 2 < pageNumber)
      );
    },
  },
  methods: {
    navigateToPreviousPage: function () {
      if (!this.disabled && this.data.currentPage > 1) {
        this.$emit("change", {
          currentPage: this.data.currentPage - 1,
          resultsPage: this.data.resultsPage,
        });
      }
    },
    navigateToNextPage: function () {
      if (!this.disabled && this.data.currentPage < this.data.totalPages) {
        this.$emit("change", {
          currentPage: this.data.currentPage + 1,
          resultsPage: this.data.resultsPage,
        });
      }
    },
    navigateToCustomPage: function (pageIdx) {
      if (!this.disabled && pageIdx > 0 && pageIdx <= this.data.totalPages) {
        this.$emit("change", {
          currentPage: pageIdx,
          resultsPage: this.data.resultsPage,
        });
      }
    },
  },
};
