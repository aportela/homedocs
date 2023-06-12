import { default as controlInputTags } from "../vue-components/control-input-tags.js";
import { default as controlDateSelector } from "../vue-components/control-date-selector.js";
import { default as controlPagination } from "../vue-components/control-pagination.js";
import { default as controlTableHeaderSortable } from "../vue-components/control-table-header-sortable.js";

const template = `
    <div>
        <h1 class="title">{{ $t("pages.search.labels.headerField") }}</h1>
        <div class="tabs">
            <ul>
                <li v-bind:class="{ 'is-active': tab == 'conditions' }">
                    <a v-on:click.prevent="tab = 'conditions'">
                        <span class="icon is-small"><i class="fas fa-search" aria-hidden="true"></i></span>
                        <span>{{ $t("pages.search.labels.conditions") }}</span>
                    </a>
                </li>
                <li v-bind:class="{ 'is-active': tab == 'results' }">
                <a v-on:click.prevent="tab = 'results'">
                        <span class="icon is-small"><i class="far fa-list-alt" aria-hidden="true"></i></span>
                        <span>{{ $t("pages.search.labels.results") }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <form v-show="tab == 'conditions'" v-on:submit.prevent="">
            <div class="field">
                <label class="label">{{ $t("pages.search.labels.searchOnTitle") }}</label>
                <div class="control">
                    <input class="input" type="text" ref="titleCondition" :placeholder="$t('pages.search.labels.searchOnTitleInputPlaceholder')" v-model.trim="titleCondition">
                </div>
            </div>
            <div class="field">
                <label class="label">{{ $t("pages.search.labels.searchOnDescription") }}</label>
                <div class="control">
                    <input class="input" type="text" :placeholder="$t('pages.search.labels.searchOnDescriptionInputPlaceholder')" v-model.trim="descriptionCondition">
                </div>
            </div>
            <div class="field">
                <label class="label">{{ $t("pages.search.labels.createdOn") }}</label>
                <homedocs-control-date-selector v-bind:disabled="loading" v-on:changed="fromTimestampCondition = $event.fromTimestamp; toTimestampCondition = $event.toTimestamp;"></homedocs-control-date-selector>
            </div>
            <div class="field">
                <label class="label">{{ $t("pages.search.labels.tags") }}</label>
                <homedocs-control-input-tags v-bind:tags="tags" v-on:update="tags = $event.tags"></homedocs-control-input-tags>
            </div>
            <button type="submit" class="button is-dark is-fullwidth" v-on:click.prevent="onSearch(true)"><span class="icon"><i class="fas fa-search"></i></span> <span>{{ $t("pages.search.labels.submitButton") }}</span></button>

        </form>

        <homedocs-control-pagination v-show="tab == 'results'" v-bind:data="pager" v-bind:disabled="loading" v-on:change="refreshFromPager($event.currentPage, $event.resultsPage)"></homedocs-control-pagination>

        <table class="table is-narrow is-striped is-fullwidth" v-show="tab == 'results'">
            <thead>
                <tr>
                    <homedocs-table-header-sortable v-bind:name="$t('pages.search.labels.resultsHeaderCreated')" v-bind:isSorted="sortBy == 'createdOnTimestamp'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('createdOnTimestamp');"></homedocs-table-header-sortable>
                    <homedocs-table-header-sortable v-bind:name="$t('pages.search.labels.resultsHeaderTitle')" v-bind:isSorted="sortBy == 'title'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('title');"></homedocs-table-header-sortable>
                    <homedocs-table-header-sortable v-bind:name="$t('pages.search.labels.resultsHeaderDescription')" v-bind:className="'is-hidden-mobile'" v-bind:isSorted="sortBy == 'description'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('description');"></homedocs-table-header-sortable>
                    <homedocs-table-header-sortable v-bind:name="$t('pages.search.labels.resultsHeaderFiles')" v-bind:className="'has-text-right'" v-bind:isSorted="sortBy == 'fileCount'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('fileCount');"></homedocs-table-header-sortable>
                </tr>
            </thead>
            <tbody>
                <tr v-for="document in documents" v-bind:key="document.id">
                    <td class="nowrap">{{ $utils.timestamp2HumanDateTime(document.createdOnTimestamp) }}</td>
                    <td class="nowrap"><router-link v-bind:to="{ name: 'appOpenDocument', params: { id: document.id } }">{{ document.title }}</router-link></td>
                    <td class="is-hidden-mobile" v-bind:title="document.description">{{ cutDescription(document.description) }}</td>
                    <td class="has-text-right">{{ document.fileCount }}</td>
                </tr>
            </tbody>
        </table>

        <article class="message is-dark mt-4" v-if="noResultsWarning">
            <div class="message-header">
                <p><i class="fas fa-exclamation-triangle"></i> {{ $t("pages.search.labels.warning") }}</p>
            </div>
            <div class="message-body">
                {{ $t("pages.search.labels.noResultsForConditions") }}
            </div>
        </article>

    </div>
`;

export default {
  name: "homedocs-section-app-advanced-search",
  template: template,
  data: function () {
    return {
      loading: false,
      tab: "conditions",
      titleCondition: null,
      descriptionCondition: null,
      fromTimestampCondition: null,
      toTimestampCondition: null,
      documents: [],
      pager: {
        currentPage: 1,
        previousPage: 1,
        nextPage: 1,
        totalPages: 0,
        resultsPage: initialState.defaultResultsPage,
      },
      sortBy: "createdOnTimestamp",
      sortOrder: "DESC",
      tags: [],
      noResultsWarning: false,
      autoLaunch: false,
    };
  },
  created: function () {
    if (this.$route.params.tag) {
      this.tags = [this.$route.params.tag];
      this.autoLaunch = true;
    } else {
      this.tags = [];
    }
  },
  mounted: function () {
    if (this.autoLaunch) {
      this.onSearch();
    } else {
      this.$nextTick(() => this.$refs.titleCondition.focus());
    }
  },
  watch: {
    tab: function (newValue) {
      if (newValue == "conditions") {
        this.$nextTick(() => this.$refs.titleCondition.focus());
      }
    },
  },
  components: {
    "homedocs-control-input-tags": controlInputTags,
    "homedocs-control-date-selector": controlDateSelector,
    "homedocs-control-pagination": controlPagination,
    "homedocs-table-header-sortable": controlTableHeaderSortable,
  },
  methods: {
    refreshFromPager: function (currentPage, resultsPage) {
      if (this.pager.currentPage != currentPage) {
        this.pager.currentPage = currentPage;
        this.pager.resultsPage = resultsPage;
      } else {
        this.pager.resultsPage = resultsPage;
      }
      this.onSearch();
    },
    toggleSort: function (field) {
      if (!this.loading) {
        if (field == this.sortBy) {
          if (this.sortOrder == "ASC") {
            this.sortOrder = "DESC";
          } else {
            this.sortOrder = "ASC";
          }
        } else {
          this.sortBy = field;
          this.sortOrder = "ASC";
        }
        this.onSearch();
      }
    },
    onSearch: function (resetPager) {
      if (resetPager) {
        this.pager.currentPage = 1;
      }
      this.loading = true;
      this.noResultsWarning = false;
      const params = {
        title: this.titleCondition,
        description: this.descriptionCondition,
        fromTimestampCondition: this.fromTimestampCondition,
        toTimestampCondition: this.toTimestampCondition,
        tags: this.tags,
      };
      this.$api.document
        .search(
          this.pager.currentPage,
          this.pager.resultsPage,
          params,
          this.sortBy,
          this.sortOrder
        )
        .then((success) => {
          this.loading = false;
          this.pager.currentPage = success.data.results.pagination.currentPage;
          this.pager.totalPages = success.data.results.pagination.totalPages;
          this.pager.totalResults =
            success.data.results.pagination.totalResults;
          this.documents = success.data.results.documents;
          if (this.documents.length > 0) {
            this.tab = "results";
          } else {
            this.tab = "conditions";
            this.noResultsWarning = true;
          }
        })
        .catch((error) => {
          this.loading = false;
          switch (error.response.status) {
            case 401:
              this.$router.push({
                name: "signIn",
              });
              break;
            default:
              this.$emit("showAPIError", {
                httpCode: error.response.status,
                data: error.response.getApiErrorData(),
              });
              break;
          }
        });
    },
    cutDescription: function (description) {
      if (description) {
        if (description.length > 64) {
          return description.slice(0, 64) + "...";
        } else {
          return description;
        }
      } else {
        return null;
      }
    },
  },
};
