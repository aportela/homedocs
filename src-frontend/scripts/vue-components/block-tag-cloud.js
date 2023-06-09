const template = `
    <article class="message">
        <div class="message-header">
            <p>
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading" :title="$t('pages.dashBoard.labels.loadingData')"></i>
                    <i class="fas fa-exclamation-triangle cursor-help" v-else-if="apiError" :title="$t('pages.dashBoard.labels.errorloadingData')"></i>
                    <i class="fas fa-tags" v-else></i>
                </span>
                <span>{{ $t("pages.dashBoard.labels.browseByTags") }}</span>
            </p>
            <i class="fas fa-sync-alt cursor-pointer" :title="$t('pages.dashBoard.labels.clickHereToRefresh')" v-on:click.prevent="onRefresh"></i>
        </div>
        <div class="message-body has-text-centered" v-if="apiError">
            <h5 class="title is-5"><i class="fas fa-exclamation-triangle"></i> {{ $t("pages.dashBoard.labels.errorloadingData") }}</h5>
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
            <p v-if="showWarningNoTags">{{ $t("pages.dashBoard.labels.browseByTagsShowWarningNoTags") }}</p>
        </div>
    </article>
`;

export default {
  name: "homedocs-block-tag-cloud",
  template: template,
  data: function () {
    return {
      loading: false,
      apiError: false,
      items: [],
      showWarningNoTags: false,
    };
  },
  mounted: function () {
    this.onRefresh();
  },
  methods: {
    onRefresh: function () {
      if (!this.loading) {
        this.items = [];
        this.apiError = false;
        this.loading = true;
        this.showWarningNoTags = false;
        this.$api.tag
          .getCloud()
          .then((response) => {
            this.items = response.data.tags;
            this.showWarningNoTags = this.items.length < 1;
            this.loading = false;
          })
          .catch((error) => {
            this.loading = false;
            this.apiError = true;
            this.$emit("showAPIError", {
              data: error.response.getApiErrorData(),
            });
          });
      }
    },
  },
};
