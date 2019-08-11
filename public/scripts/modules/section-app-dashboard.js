import { default as homedocsAPI } from './api.js';
import { default as modalAPIError } from './modal-api-error.js';
import { default as blockRecentDocuments } from './block-recent-documents.js';

const template = `
    <div>
        <div class="columns">
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-plus"></i></span><span>Add</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <router-link v-bind:to="{ name: 'appAddDocument' }">
                            <i class="fas fa-plus fa-10x"></i>
                            <h1 class="title">Click here for add new document</h1>
                        </router-link>
                    </div>
                </article>
            </div>
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-search-plus"></i></span><span>Search</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <router-link v-bind:to="{ name: 'appAdvancedSearch' }">
                            <i class="fas fa-search-plus fa-10x"></i>
                            <h1 class="title">Click here for advanced search</h1>
                        </router-link>
                    </div>
                </article>
            </div>
        </div>
        <div class="columns">
            <div class="column is-6">
                <homedocs-block-recent-documents></homedocs-block-recent-documents>
            </div>
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-plus"></i></span><span>Browse by tags</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <div class="field is-grouped is-grouped-multiline">
                            <div class="control" v-for="item in cloudTags" v-bind:key="item.tag">
                                <router-link v-bind:to="{ name: 'appAdvancedSearch', params: { tags: [ item.tag ], launch: true } }">
                                    <div class="tags has-addons">
                                        <span class="tag is-dark">{{ item.tag }}</span>
                                        <span class="tag is-white">{{ item.total }}</span>
                                    </div>
                                </router-link>
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
            cloudTags: []
        });
    },
    mounted: function () {
        this.onSearchTags();
    },
    components: {
        'homedocs-modal-api-error': modalAPIError,
        'homedocs-block-recent-documents': blockRecentDocuments
    },
    methods: {
        onSearchTags: function () {
            if (!this.loading) {
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
}