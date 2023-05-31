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
        <div class="message-body has-text-centered" v-if="apiError">
            <h5 class="title is-5"><i class="fas fa-exclamation-triangle"></i> Error loading data</h5>
        </div>
        <div class="message-body has-text-centered" v-else>
            <div class="field is-grouped is-grouped-multiline" v-if="items.length > 0">
                <div class="control" v-for="item in items" v-bind:key="item.tag">
                    <router-link v-bind:to="{ name: 'appAdvancedSearchByTag', params: { tag: item.tag } }">
                        <div class="tags has-addons">
                            <span class="tag is-dark">{{ item.tag }}</span>
                            <span class="tag is-white">{{ item.total }}</span>
                        </div>
                    </router-link>
                </div>
            </div>
            <div v-if="showWarningNoTags">
                No tag has been created yet
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
            items: [],
            showWarningNoTags: false
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
                this.showWarningNoTags = false;
                this.$api.tag.getCloud().then(response => {
                    this.items = response.data.tags;
                    this.showWarningNoTags = this.items.length < 1;
                    this.loading = false;
                }).catch(error => {
                    // TODO
                    //this.apiError = response.getApiErrorData();
                    //this.$emit("showAPIError", this.apiError);
                    this.loading = false;
                });
            }
        }
    }
}