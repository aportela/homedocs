import { default as apiErrorNotification } from "../vue-components/notification-api-error.js";

const template = `
    <section class="section">
        <div class="container is-fluid">
            <nav class="navbar" role="navigation" aria-label="main navigation">
                <div class="navbar-brand">
                    <a class="navbar-item is-uppercase has-text-weight-bold" href="https://github.com/aportela/homedocs" target="_blank">
                        <span class="icon"><i class="fab fa-github"></i></span> <span>homedocs</span></a>

                    <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" v-bind:class="{ 'is-active': showMobileMenu }" v-on:click.prevent="showMobileMenu = !showMobileMenu">
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </a>
                </div>
                <div class="navbar-menu" v-bind:class="{ 'is-active': showMobileMenu }">
                    <div class="navbar-start">
                        <router-link v-bind:to="{ name: 'appDashBoard' }" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'appDashBoard' }">
                            <span class="icon">
                                <i class="fas fa-home"></i>
                            </span>
                            <span>{{ $t("pages.appMenu.labels.home") }}</span>
                        </router-link>
                        <router-link v-bind:to="{ name: 'appAddDocument' }" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'appAddDocument' }">
                            <span class="icon">
                                <i class="fas fa-folder-plus"></i>
                            </span>
                            <span>{{ $t("pages.appMenu.labels.add") }}</span>
                        </router-link>
                        <router-link v-bind:to="{ name: 'appAdvancedSearch' }" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'appAdvancedSearch' }">
                            <span class="icon">
                            <i class="fas fa-search"></i>
                            </span>
                            <span>{{ $t("pages.appMenu.labels.search") }}</span>
                        </router-link>
                    </div>
                    <div class="navbar-end">
                        <a class="navbar-item" v-on:click.prevent="onSignOut">
                            <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span>{{ $t("pages.appMenu.labels.signOut") }}</span>
                        </a>
                    </div>
                </div>
            </nav>
            <homedocs-notification-api-error v-if="apiError" :message="apiError"></homedocs-notification-api-error>
            <router-view v-on:showAPIError="apiError = $event;"></router-view>
        </div>
    </section>
`;

export default {
  name: "homedocs-section-app-container",
  template: template,
  data: function () {
    return {
      loading: false,
      apiError: null,
      searchQuery: null,
      showMobileMenu: false,
    };
  },
  watch: {
    $route: function (to, from) {
      this.showMobileMenu = false;
    },
  },
  methods: {
    onSignOut: function () {
      this.apiError = null;
      this.loading = true;
      this.$api.user
        .signOut()
        .then((success) => {
          this.loading = false;
          this.$localStorage.remove("jwt");
          this.$router.push({ name: "signIn" });
        })
        .catch((error) => {
          this.loading = false;
          this.apiError = error.response.getApiErrorData();
        });
    },
  },
};
