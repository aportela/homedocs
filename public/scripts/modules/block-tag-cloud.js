import { default as homedocsAPI } from './api.js';

const template = `
    <article class="message">
        <div class="message-header">
            <p>
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading" title="Loading data..."></i>
                    <i class="fas fa-exclamation-triangle cursor-help" v-else-if="apiError" title="Error loading data"></i>
                    <i class="fas fa-tags" v-else></i>
                </span>
                <span>Browse by tags</span>
            </p>
            <i class="fas fa-sync-alt cursor-pointer" title="Click here to refresh" v-on:click.prevent="onRefresh"></i>
        </div>
        <div class="message-body has-text-centered">
            <div class="field is-grouped is-grouped-multiline">
                <div class="control" v-for="item in items" v-bind:key="item.tag">
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
`;

export default {
    name: 'homedocs-block-tag-cloud',
    template: template,
    data: function () {
        return ({
            loading: false,
            apiError: null,
            items: []
        });
    },
    mounted: function () {
        this.onRefresh();
    },
    methods: {
        onRefresh: function () {
            if (!this.loading) {
                this.apiError = null;
                this.loading = true;
                homedocsAPI.tag.search((response) => {
                    if (response.ok) {
                        this.items = response.body.data;
                    } else {
                        this.apiError = response.getApiErrorData();
                    }
                    this.loading = false;
                });
            }
        }
    }
}