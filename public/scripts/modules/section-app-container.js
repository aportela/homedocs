import { default as homedocsAPI } from './api.js';
import { default as modalAPIError } from './modal-api-error.js';

const template = `
    <div class="container">
        <homedocs-modal-api-error v-if="apiError" v-bind:error="apiError" v-on:close="apiError = null"></homedocs-modal-api-error>
        <nav class="navbar" role="navigation" aria-label="main navigation">
            <div class="navbar-brand">

                <a class="navbar-item is-uppercase has-text-weight-bold" href="https://github.com/aportela/homedocs" target="_blank">
                    <span class="icon"><i class="fab fa-github"></i></span> <span>homedocs</span></a>

                <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false"
                    data-target="navbarBasicExample">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>
            <div class="navbar-menu">
                <div class="navbar-start">
                    <router-link v-bind:to="{ name: 'appDashBoard' }" class="navbar-item">
                        <span class="icon">
                            <i class="fas fa-home"></i>
                        </span>
                        <span>Home</span>
                    </router-link>
                    <a class="navbar-item">
                        <span class="icon">
                            <i class="fas fa-folder-plus"></i>
                        </span>
                        <span>Add</span>
                    </a>
                </div>
                <div class="navbar-end">
                    <div class="navbar-item">
                        <div class="field">
                            <p class="control has-icons-left">
                                <input class="input is-rounded" type="text" placeholder="Search">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-search"></i>
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="navbar-item">
                        <div class="buttons">
                            <button class="button" v-on:click.prevent="onSignOut">
                                <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                                <span>Sign out</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <router-view></router-view>
    </div>
`;

export default {
    name: 'homedocs-section-app-container',
    template: template,
    data: function () {
        return ({
            loading: false,
            apiError: null
        });
    },
    components: {
        'homedocs-modal-api-error': modalAPIError
    },
    methods: {
        onSignOut: function() {
            const self = this;
            self.loading = true;
            homedocsAPI.user.signOut(function(response) {
                if (response.ok) {
                    self.$router.push({ name: 'signIn' });
                } else {
                    self.apiError = response.getApiErrorData();
                }
                self.loading = false;
            });
        }
    }
}