import { default as homedocsAPI } from './api.js';
import { default as controlInputTags } from './control-input-tags.js';
import { default as controlPagination } from './control-pagination.js';
import { default as controlTableHeaderSortable } from './control-table-header-sortable.js';

import { mixinDateTimes } from './mixins.js';

const template = `
    <div>
        <h1 class="title">Advanced search</h1>
        <div class="tabs">
            <ul>
                <li v-bind:class="{ 'is-active': tab == 'conditions' }">
                    <a v-on:click.prevent="tab = 'conditions'">
                        <span class="icon is-small"><i class="far fa-keyboard" aria-hidden="true"></i></span>
                        <span>Conditions</span>
                    </a>
                </li>
                <li v-bind:class="{ 'is-active': tab == 'results' }">
                <a v-on:click.prevent="tab = 'results'">
                        <span class="icon is-small"><i class="far fa-list-alt" aria-hidden="true"></i></span>
                        <span>Results</span>
                    </a>
                </li>
            </ul>
        </div>
        <form v-show="tab == 'conditions'" v-on:submit.prevent="">
            <div class="field">
                <label class="label">Search on title</label>
                <div class="control">
                    <input class="input" type="text" ref="titleCondition" placeholder="Title condition search" v-model.trim="titleCondition">
                </div>
            </div>
            <div class="field">
                <label class="label">Search on description</label>
                <div class="control">
                    <input class="input" type="text" placeholder="Description condition search" v-model.trim="descriptionCondition">
                </div>
            </div>
            <div class="field">
                <label class="label">Published on</label>
                <div class="select">
                    <select>
                        <option value="0">Any date</option>
                        <option value="0">Today</option>
                        <option value="0">Yesterday</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label class="label">Tags</label>
                <homedocs-control-input-tags v-bind:tags="tags" v-on:update="tags = $event.tags"></homedocs-control-input-tags>
            </div>
            <button type="submit" class="button is-primary is-fullwidth" v-on:click.prevent="onSearch">Search</button>

        </form>

        <homedocs-control-pagination v-show="tab == 'results'" v-bind:data="pager" v-bind:disabled="loading" v-on:change="refreshFromPager($event.currentPage, $event.resultsPage)"></homedocs-control-pagination>
        <table class="table is-narrow is-striped is-fullwidth" v-show="tab == 'results'">
            <thead>
                <tr>
                    <homedocs-table-header-sortable v-bind:name="'On'" v-bind:isSorted="sortBy == 'createdOnTimestamp'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('createdOnTimestamp');"></homedocs-table-header-sortable>
                    <homedocs-table-header-sortable v-bind:name="'Title'" v-bind:isSorted="sortBy == 'title'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('title');"></homedocs-table-header-sortable>
                    <homedocs-table-header-sortable v-bind:name="'Description'" v-bind:className="'is-hidden-mobile'" v-bind:isSorted="sortBy == 'description'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('description');"></homedocs-table-header-sortable>
                    <homedocs-table-header-sortable v-bind:name="'Files'" v-bind:className="'has-text-right'" v-bind:isSorted="sortBy == 'fileCount'" v-bind:sortOrder="sortOrder" v-on:sortClicked="toggleSort('fileCount');"></homedocs-table-header-sortable>
                </tr>
            </thead>
            <tbody>
                <tr v-for="document in documents" v-bind:key="document.id">
                    <td class="nowrap">{{ document.createdOnTimestamp | timestamp2HumanDateTime }}</td>
                    <td class="nowrap"><router-link v-bind:to="{ name: 'appOpenDocument', params: { id: document.id } }">{{ document.title }}</router-link></td>
                    <td class="is-hidden-mobile" v-bind:title="document.description">{{ document.description | cutDescription }}</td>
                    <td class="has-text-right">{{ document.fileCount }}</td>
                </tr>
            </tbody>
        </table>

        <article class="message is-warning" v-if="noResultsWarning">
            <div class="message-header">
                <p>Warning</p>
            </div>
            <div class="message-body">
                No results found for current search conditions
            </div>
        </article>

    </div>
`;

export default {
    name: 'homedocs-section-app-advanced-search',
    template: template,
    data: function () {
        return ({
            loading: false,
            tab: "conditions",
            titleCondition: null,
            descriptionCondition: null,
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
            noResultsWarning: false
        });
    },
    created: function() {
        this.tags = this.$route.params.tags || [];
    },
    mounted: function() {
        if (this.$route.params.launch) {
            this.onSearch();
        } else {
            this.$nextTick(() => this.$refs.titleCondition.focus());
        }
    },
    mixins: [
        mixinDateTimes
    ],
    components: {
        'homedocs-control-input-tags': controlInputTags,
        'homedocs-control-pagination': controlPagination,
        'homedocs-table-header-sortable': controlTableHeaderSortable
    },
    filters: {
        cutDescription: function(description) {
            if (description) {
                if (description.length > 64) {
                    return(description.slice(0, 64) + "...");
                } else {
                    return(description);
                }
            } else {
                return(null);
            }
        }
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
        onSearch: function () {
            this.loading = true;
            this.noResultsWarning = false;
            const params = {
                title: this.titleCondition,
                description: this.descriptionCondition,
                tags: this.tags
            };
            homedocsAPI.document.search(this.pager.currentPage, this.pager.resultsPage, params, this.sortBy, this.sortOrder, (response) => {
                if (response.ok) {
                    this.pager.currentPage = response.body.data.pagination.currentPage;
                    this.pager.totalPages = response.body.data.pagination.totalPages;
                    this.pager.totalResults = response.body.data.pagination.totalResults;
                    this.documents = response.body.data.results;
                    if (this.documents.length > 0) {
                        this.tab = "results";
                    } else {
                        this.tab = "conditions";
                        this.noResultsWarning = true;
                    }
                } else {
                    this.$emit("showAPIError", response.getApiErrorData());
                }
                this.loading = false;
            });
        },

    }
}