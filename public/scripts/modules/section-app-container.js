import { default as homedocsAPI } from './api.js';
import { default as modalAPIError } from './modal-api-error.js';

const template = `
    <section class="section">
        <div class="container is-fluid">
            <homedocs-modal-api-error v-if="apiError" v-bind:error="apiError" v-on:close="apiError = null"></homedocs-modal-api-error>
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
                            <span>Home</span>
                        </router-link>
                        <router-link v-bind:to="{ name: 'appAddDocument' }" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'appAddDocument' }">
                            <span class="icon">
                                <i class="fas fa-folder-plus"></i>
                            </span>
                            <span>Add</span>
                        </router-link>
                        <router-link v-bind:to="{ name: 'appAdvancedSearch' }" class="navbar-item" v-bind:class="{ 'is-active': $route.name == 'appAdvancedSearch' }">
                            <span class="icon">
                            <i class="fas fa-search"></i>
                            </span>
                            <span>Search</span>
                        </router-link>
                    </div>
                    <div class="navbar-end">
                        <a class="navbar-item" v-on:click.prevent="onSignOut">
                            <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span>Sign out</span>
                        </a>
                    </div>
                </div>
            </nav>
            <router-view v-on:showAPIError="apiError = $event;"></router-view>
            <homedocs-modal-api-error v-if="apiError" v-bind:error="apiError" v-on:close="apiError = null"></homedocs-modal-api-error>
        </div>
    </section>
`;

export default {
    name: 'homedocs-section-app-container',
    template: template,
    data: function () {
        return ({
            loading: false,
            apiError: null,
            searchQuery: null,
            showMobileMenu: false
        });
    },
    watch: {
        '$route': function (to, from) {
            this.showMobileMenu = false;
        }
    },
    components: {
        'homedocs-modal-api-error': modalAPIError
    },
    methods: {
        onSignOut: function() {
            this.loading = true;
            homedocsAPI.user.signOut((response) => {
                if (response.ok) {
                    this.$router.push({ name: 'signIn' });
                } else {
                    this.apiError = response.getApiErrorData();
                }
                this.loading = false;
            });
        }
    }
}