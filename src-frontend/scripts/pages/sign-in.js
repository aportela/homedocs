import { default as validator } from '../modules/validator.js';
import { default as modalAPIError } from '../vue-components/modal-api-error.js';

const template = `
    <!-- template credits: daniel (https://github.com/dansup) -->
    <section class="hero is-fullheight is-light is-bold is-unselectable">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-4 is-offset-4">
                        <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-book-reader"></i></span> HOMEDOCS <span class="icon is-medium"><i class="fas fa-book-reader"></i></span></h1>
                        <h2 class="subtitle is-6 has-text-centered">I ASSURE YOU; WE'RE OPEN</h2>
                        <hr>
                        <h1 class="title is-3 has-text-centered"><i class="fa fa-user"></i> Sign in</h1>
                        <form v-on:submit.prevent="onSubmit">
                            <div class="box">
                                <div class="field">
                                    <label class="label">Email</label>
                                    <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('email') }">
                                        <input class="input" type="email" name="email" maxlength="255" ref="email" required v-bind:class="{ 'is-danger': validator.hasInvalidField('email') }" v-bind:disabled="loading" v-model.trim="email">
                                        <span class="icon is-small is-left"><i class="fa fa-envelope"></i></span>
                                        <span class="icon is-small is-right" v-show="validator.hasInvalidField('email')"><i class="fas fa-exclamation-triangle"></i></span>
                                        <p class="help is-danger" v-show="validator.hasInvalidField('email')">{{ validator.getInvalidFieldMessage('email') }}</p>
                                    </p>
                                </div>
                                <div class="field">
                                    <label class="label">Password</label>
                                    <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('password') }">
                                        <input class="input" type="password" name="password" required ref="password" v-bind:class="{ 'is-danger': validator.hasInvalidField('password') }" v-bind:disabled="loading" v-model="password">
                                        <span class="icon is-small is-left"><i class="fa fa-key"></i></span>
                                        <span class="icon is-small is-right" v-show="validator.hasInvalidField('password')"><i class="fas fa-exclamation-triangle"></i></span>
                                        <p class="help is-danger" v-show="validator.hasInvalidField('password')">{{ validator.getInvalidFieldMessage('password') }}</p>
                                    </p>
                                </div>
                                <p class="control">
                                    <button type="submit" class="button is-link is-fullwidth" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="disableSubmit">
                                        <span class="icon"><i class="fa fa-lock"></i></span>
                                        <span>Sign in</span>
                                    </button>
                                </p>
                            </div>
                            <p class="has-text-centered has-text-weight-bold" v-if="allowSignUp">Don't have an account ?<br><router-link v-bind:to="{ name: 'signUp' }">Create one</router-link></p>
                        </form>
                        <p class="has-text-centered mt-2">
                            <a href="https://github.com/aportela/homedocs" target="_blank"><span class="icon is-small"><i class="fab fa-github"></i></span> <span>Project page</span></a> | <a href="mailto:766f6964+github@gmail.com">by alex</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <homedocs-modal-api-error v-if="apiError" v-bind:error="apiError" v-on:close="apiError = null"></homedocs-modal-api-error>
    </section>
`;

export default {
    name: 'homedocs-section-signin',
    template: template,
    data: function () {
        return ({
            loading: false,
            validator: validator,
            email: null,
            password: null,
            apiError: null
        });
    },
    computed: {
        allowSignUp: function () {
            return (initialState.allowSignUp);
        },
        disableSubmit: function () {
            return (this.loading || !(this.email && this.password));
        }
    },
    created: function () {
        this.validator.clear();
    },
    mounted: function () {
        this.$nextTick(() => this.$refs.email.focus());
    },
    components: {
        'homedocs-modal-api-error': modalAPIError
    },
    methods: {
        onSubmit: function () {
            this.validator.clear();
            this.loading = true;
            this.apiError = false;
            this.$api.user.signIn(this.email, this.password).then(success => {
                this.loading = false;
                initialState.logged = true;
                this.$router.push({ name: 'appDashBoard' });
            }).catch(error => {
                switch (error.response.status) {
                    case 400:
                        if (error.response.data.invalidOrMissingParams.find(function (e) { return (e === "email"); })) {
                            this.validator.setInvalid("email", "API ERROR: Invalid email parameter");
                            this.$nextTick(() => this.$refs.email.focus());
                        } else if (error.response.data.invalidOrMissingParams.find(function (e) { return (e === "password"); })) {
                            this.validator.setInvalid("password", "API ERROR: Invalid password parameter");
                            this.$nextTick(() => this.$refs.password.focus());
                        } else {
                            this.apiError = response.getApiErrorData();
                        }
                        break;
                    case 404:
                        this.validator.setInvalid("email", "Email not found");
                        this.$nextTick(() => this.$refs.email.focus());
                        break;
                    case 401:
                        this.validator.setInvalid("password", "Incorrect password");
                        this.$nextTick(() => this.$refs.password.focus());
                        break;
                    default:
                        this.apiError = response.getApiErrorData();
                        break;
                }
                this.loading = false;
            });
        }
    }
}