import { default as homedocsAPI } from './api.js';
import { default as modalAPIError } from './modal-api-error.js';

const template = `
    <div>
        <div class="columns">
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-plus"></i></span><span>Add</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <i class="fas fa-plus fa-10x"></i>
                        <h1 class="title">Click here for add new document</h1>
                    </div>
                </article>
            </div>
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-search-plus"></i></span><span>Search</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <i class="fas fa-search-plus fa-10x"></i>
                        <h1 class="title">Click here for advanced search</h1>
                    </div>
                </article>
            </div>
        </div>
        <div class="columns">
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-history"></i></span><span>Recent documents</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <table class="table is-narrow is-striped is-fullwidth">
                            <thead>
                                <tr>
                                    <th>On</th>
                                    <th>Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="document in recentDocuments" v-bind:key="document.id">
                                    <td>{{ document.uploadedOn }}</td>
                                    <td><router-link v-bind:to="{ name: 'openDocument', params: { id: document.id } }">{{ document.title }}</router-link></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-plus"></i></span><span>Browse by tags</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <div class="field is-grouped is-grouped-multiline">
                            <div class="control" v-for="item in cloudTags" v-bind:key="item.tag">
                                <div class="tags has-addons">
                                    <span class="tag is-dark">{{ item.tag }}</span>
                                    <span class="tag is-white">{{ item.total }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
`;

export default {
    name: 'homedocs-section-app-dashboard',
    template: template,
    data: function () {
        return ({
            loading: false,
            recentDocuments: [],
            cloudTags: []
        });
    },
    mounted: function () {
        this.onSearchRecentDocuments();
        this.onSearchTags();
    },
    components: {
        'homedocs-modal-api-error': modalAPIError
    },
    methods: {
        onSearchRecentDocuments: function () {
            const self = this;
            self.loading = true;
            homedocsAPI.document.search(function (response) {
                if (response.ok) {
                    self.recentDocuments = response.body.data;
                } else {
                    self.apiError = response.getApiErrorData();
                }
                self.loading = false;
            });
        },
        onSearchTags: function () {
            const self = this;
            self.loading = true;
            homedocsAPI.tag.search(function (response) {
                if (response.ok) {
                    self.cloudTags = response.body.data;
                } else {
                    self.apiError = response.getApiErrorData();
                }
                self.loading = false;
            });
        }
    }
}