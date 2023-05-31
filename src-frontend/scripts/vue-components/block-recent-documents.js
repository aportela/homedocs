import { mixinDateTimes } from '../modules/mixins.js';

const template = `
    <article class="message">
        <div class="message-header">
            <p>
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading" title="Loading data..."></i>
                    <i class="fas fa-exclamation-triangle cursor-help" v-else-if="apiError" title="Error loading data"></i>
                    <i class="fas fa-history" v-else></i>
                </span>
                <span>Recent documents</span>
            </p>
            <i class="fas fa-sync-alt cursor-pointer" title="Click here to refresh" v-on:click.prevent="onRefresh"></i>
        </div>
        <div class="message-body has-text-centered" v-if="apiError">
            <h5 class="title is-5"><i class="fas fa-exclamation-triangle"></i> Error loading data</h5>
        </div>
        <div class="message-body has-text-centered" v-else>
            <table class="table is-narrow is-striped is-fullwidth" v-if="documents.length > 0">
                <thead>
                    <tr>
                        <th class="has-text-left">Title</th>
                        <th class="has-text-left">Created on</th>
                        <th class="has-text-right">Files</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="document in documents" v-bind:key="document.id">
                        <td class="has-text-left"><router-link v-bind:to="{ name: 'appOpenDocument', params: { id: document.id } }">{{ document.title }}</router-link></td>
                        <td class="has-text-left">{{ document.createdOnTimestamp | timestamp2HumanDateTime }}</td>
                        <td class="has-text-right">{{ document.fileCount }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="showWarningNoDocuments">
                No document has been created yet
            </div>
        </div>
    </article>
`;

export default {
    name: 'homedocs-block-recent-documents',
    template: template,
    data: function () {
        return ({
            loading: false,
            apiError: null,
            documents: [],
            showWarningNoDocuments: false
        });
    },
    mixins: [
        mixinDateTimes
    ],
    mounted: function () {
        this.onRefresh();
    },
    methods: {
        onRefresh: function () {
            if (!this.loading) {
                this.apiError = null;
                this.loading = true;
                this.showWarningNoDocuments = false;
                this.$api.document.searchRecent(16).then(success => {
                    this.documents = response.body.data;
                    this.showWarningNoDocuments = this.documents.length < 1;
                    this.loading = false;
                }).catch(error => {
                    // TODO
                    this.documents = [];
                    //this.apiError = response.getApiErrorData();
                    //this.$emit("showAPIError", this.apiError);
                    this.loading = false;
                });
            }
        }
    }
}