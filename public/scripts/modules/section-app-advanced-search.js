import { default as homedocsAPI } from './api.js';
import { default as modalAPIError } from './modal-api-error.js';
import { default as controlInputTags } from './control-input-tags.js';

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
                    <input class="input" type="text" placeholder="Title condition search">
                </div>
            </div>
            <div class="field">
                <label class="label">Search on description</label>
                <div class="control">
                    <input class="input" type="text" placeholder="Description condition search">
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

        <table class="table is-narrow is-striped is-fullwidth" v-show="tab == 'results'">
            <thead>
                <tr>
                    <th>On</th>
                    <th>File count</th>
                    <th>Title</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="document in documents" v-bind:key="document.id">
                    <td>{{ document.uploadedOn }}</td>
                    <td>{{ document.fileCount }}</td>
                    <td><router-link v-bind:to="{ name: 'appOpenDocument', params: { id: document.id } }">{{ document.title }}</router-link></td>
                    <td style="white-space: pre;">{{ document.description | nl2br }}</td>
                </tr>
            </tbody>
        </table>

        <article class="message is-warning" v-if="noResultsWarning">
            <div class="message-header">
                <p>Warning</p>
                <button class="delete" aria-label="delete"></button>
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
            documents: [],
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
        }
    },
    components: {
        'homedocs-control-input-tags': controlInputTags,
        'homedocs-modal-api-error': modalAPIError
    },
    filters: {
        nl2br: function(str) {
            if (str) {
                return(str.replace(/\r\n/, "<br>"));
            } else {
                return(null);
            }
        }
    },
    methods: {
        onSearch: function () {
            const self = this;
            self.loading = true;
            self.noResultsWarning = false;
            const params = {
                tags: this.tags
            };
            homedocsAPI.document.search(params, function (response) {
                if (response.ok) {
                    self.documents = response.body.data;
                    if (self.documents.length > 0) {
                        self.tab = "results";
                    } else {
                        self.tab = "conditions";
                        self.noResultsWarning = true;
                    }
                } else {
                    self.apiError = response.getApiErrorData();
                }
                self.loading = false;
            });
        },

    }
}