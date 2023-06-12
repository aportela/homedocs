import dayjs from "dayjs";

const template = `
    <article class="message">
        <div class="message-header">
            <p>
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading" :title="$t('pages.dashBoard.labels.loadingData')"></i>
                    <i class="fas fa-exclamation-triangle cursor-help" v-else-if="apiError" :title="$t('pages.dashBoard.labels.errorloadingData')"></i>
                    <i class="fas fa-history" v-else></i>
                </span>
                <span>{{ $t("pages.dashBoard.labels.recentDocuments") }}</span>
            </p>
            <i class="fas fa-sync-alt cursor-pointer" :title="$t('pages.dashBoard.labels.clickHereToRefresh')" v-on:click.prevent="onRefresh"></i>
        </div>
        <div class="message-body has-text-centered" v-if="apiError">
            <h5 class="title is-5"><i class="fas fa-exclamation-triangle"></i> {{ $t("pages.dashBoard.labels.errorloadingData") }}</h5>
        </div>
        <div class="message-body has-text-centered" v-else>
            <table class="table is-narrow is-striped is-fullwidth" v-if="documents.length > 0">
                <thead>
                    <tr>
                        <th class="has-text-left">{{ $t("pages.dashBoard.labels.recentDocumentsHeaderTitle") }}</th>
                        <th class="has-text-left">{{ $t("pages.dashBoard.labels.recentDocumentsHeaderCreated") }}</th>
                        <th class="has-text-right">{{ $t("pages.dashBoard.labels.recentDocumentsHeaderFiles") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="document in documents" v-bind:key="document.id">
                        <td class="has-text-left"><router-link v-bind:to="{ name: 'appOpenDocument', params: { id: document.id } }">{{ document.title }}</router-link></td>
                        <td class="has-text-left">{{ document.created }}</td>
                        <td class="has-text-right">{{ document.fileCount }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="showWarningNoDocuments">{{ $t("pages.dashBoard.labels.recentDocumentsShowWarningNoDocuments") }}</p>
        </div>
    </article>
`;

export default {
  name: "homedocs-block-recent-documents",
  template: template,
  data: function () {
    return {
      loading: false,
      apiError: false,
      documents: [],
      showWarningNoDocuments: false,
    };
  },
  mounted: function () {
    this.onRefresh();
  },
  methods: {
    onRefresh: function () {
      if (!this.loading) {
        this.documents = [];
        this.apiError = false;
        this.loading = true;
        this.showWarningNoDocuments = false;
        this.$api.document
          .searchRecent(16)
          .then((response) => {
            this.documents = response.data.recentDocuments.map((document) => {
              document.created = dayjs
                .unix(document.createdOnTimestamp)
                .format("YYYY-MM-DD HH:mm:ss");
              return document;
            });
            this.showWarningNoDocuments = this.documents.length < 1;
            this.loading = false;
          })
          .catch((error) => {
            this.loading = false;
            this.apiError = true;
            this.$emit("showAPIError", {
              httpCode: error.response.status,
              data: error.response.getApiErrorData(),
            });
          });
      }
    },
  },
};
