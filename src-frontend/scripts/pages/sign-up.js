import { default as apiErrorNotification } from "../vue-components/notification-api-error.js";

const template = `
    <!-- template credits: daniel (https://github.com/dansup) -->
    <section class="hero is-fullheight is-light is-bold is-unselectable">
        <div class="hero-body">
            <div class="container">
              <homedocs-notification-api-error v-if="apiError" :message="apiError"></homedocs-notification-api-error>
                <div class="columns is-vcentered">
                    <div class="column is-4 is-offset-4">
                        <h1 class="title has-text-centered"><span class="icon is-medium"><i class="fas fa-book-reader"></i></span> HOMEDOCS <span class="icon is-medium"><i class="fas fa-book-reader"></i></span></h1>
                        <h2 class="subtitle is-6 has-text-centered">I ASSURE YOU; WE'RE OPEN</h2>
                        <hr>
                        <h1 class="title is-3 has-text-centered"><i class="fas fa-user-plus"></i> {{ $t("pages.signUp.labels.headerField") }}</h1>
                        <form v-on:submit.prevent="onSubmit" v-if="! success">
                            <div class="box">
                                <div class="field">
                                    <label class="label">{{ $t("pages.signUp.labels.emailField") }}</label>
                                    <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : $validator.hasInvalidField('email') }">
                                        <input class="input" type="email" name="email" maxlength="255" ref="email" required v-bind:class="{ 'is-danger': $validator.hasInvalidField('email') }" v-bind:disabled="loading" v-model.trim="email">
                                        <span class="icon is-small is-left"><i class="fa fa-envelope"></i></span>
                                        <span class="icon is-small is-right" v-show="$validator.hasInvalidField('email')"><i class="fas fa-exclamation-triangle"></i></span>
                                        <p class="help is-danger" v-show="$validator.hasInvalidField('email')">{{ $validator.getInvalidFieldMessage('email') }}</p>
                                    </p>
                                </div>
                                <div class="field">
                                    <label class="label">{{ $t("pages.signUp.labels.passwordField") }}</label>
                                    <p class="control has-icons-left" v-bind:class="{ 'has-icons-right' : $validator.hasInvalidField('password') }">
                                        <input class="input" type="password" name="password" required ref="password" v-bind:class="{ 'is-danger': $validator.hasInvalidField('password') }" v-bind:disabled="loading" v-model="password">
                                        <span class="icon is-small is-left"><i class="fa fa-key"></i></span>
                                        <span class="icon is-small is-right" v-show="$validator.hasInvalidField('password')"><i class="fas fa-exclamation-triangle"></i></span>
                                        <p class="help is-danger" v-show="$validator.hasInvalidField('password')">{{ $validator.getInvalidFieldMessage('password') }}</p>
                                    </p>
                                </div>
                                <p class="control">
                                    <button type="submit" class="button is-link is-fullwidth" v-bind:class="{ 'is-loading': loading }" v-bind:disabled="disableSubmit">
                                        <span class="icon"><i class="fa fa-plus-circle"></i></span>
                                        <span>{{ $t("pages.signUp.labels.submitButton") }}</span>
                                    </button>
                                </p>
                            </div>
                            <p class="has-text-centered has-text-weight-bold">{{ $t("pages.signUp.labels.alreadyHaveAnAccount") }}<br><router-link v-bind:to="{ name: 'signIn' }">{{ $t("pages.signUp.labels.signInWithAccount") }}</router-link></p>
                        </form>
                        <div class="box" v-else>
                            <p class="has-text-centered has-text-weight-bold"><span class="icon"><i class="fas fa-check-circle"></i></span> {{ $t("pages.signUp.labels.accountCreated") }}<br><router-link v-bind:to="{ name: 'signIn' }">{{ $t("pages.signUp.labels.signInWithAccount") }}</router-link></p>
                        </div>
                        <p class="has-text-centered mt-2">
                            <a href="https://github.com/aportela/homedocs" target="_blank"><span class="icon is-small"><i class="fab fa-github"></i></span> <span>{{ $t("pages.common.labels.projectPage") }}</span></a> | <a href="mailto:766f6964+github@gmail.com">{{ $t("pages.common.labels.byAuthor") }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
`;

export default {
  name: "homedocs-section-signup",
  template: template,
  data: function () {
    return {
      loading: false,
      email: null,
      password: null,
      success: false,
      apiError: null,
    };
  },
  computed: {
    allowSignUp: function () {
      return initialState.allowSignUp;
    },
    disableSubmit: function () {
      return this.loading || !(this.email && this.password);
    },
  },
  created: function () {
    this.$validator.clear();
  },
  mounted: function () {
    this.$nextTick(() => this.$refs.email.focus());
  },
  components: {
    "homedocs-notification-api-error": apiErrorNotification,
  },
  methods: {
    onSubmit: function () {
      this.$validator.clear();
      this.loading = true;
      this.apiError = false;
      this.$api.user
        .signUp(this.$utils.uuid(), this.email, this.password)
        .then((success) => {
          this.loading = false;
          this.success = true;
        })
        .catch((error) => {
          this.loading = false;
          switch (error.response.status) {
            case 400:
              if (
                error.response.data.invalidOrMissingParams.find(function (e) {
                  return e === "email";
                })
              ) {
                this.$validator.setInvalid(
                  "email",
                  this.$t("pages.signUp.errorMessages.APIMissingEmail")
                );
                this.$nextTick(() => this.$refs.email.focus());
              } else if (
                error.response.data.invalidOrMissingParams.find(function (e) {
                  return e === "password";
                })
              ) {
                this.$validator.setInvalid(
                  "password",
                  this.$t("pages.signUp.errorMessages.APIMissingPassword")
                );
                this.$nextTick(() => this.$refs.password.focus());
              } else {
                this.apiError = error.response.getApiErrorData();
              }
              break;
            case 409:
              if (
                error.response.data.invalidOrMissingParams.find(function (e) {
                  return e === "email";
                })
              ) {
                this.$validator.setInvalid(
                  "email",
                  this.$t("pages.signUp.errorMessages.emailAlreadyInUse")
                );
                this.$nextTick(() => this.$refs.email.focus());
              } else {
                this.apiError = error.response.getApiErrorData();
              }
              break;
            default:
              this.apiError = error.response.getApiErrorData();
              break;
          }
        });
    },
  },
};
