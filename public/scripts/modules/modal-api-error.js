
const template = `
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Fatal error</p>
                <button class="delete" aria-label="close" v-on:click.prevent="onClose"></button>
            </header>
            <section class="modal-card-body">
                <div class="content">
                    <h1 class="has-text-centered">“I'm sorry Dave. I'm afraid I can't do that”</h1>
                    <h2 v-if="serverReturnError">Uh oh! ...the server sent a <strong>invalid response</strong> ({{ error.response.status }} - {{ error.response.statusText }})</h2>
                    <h2 v-else>Uh oh! ...can't connect, server unreachable</h2>
                    <p v-show="! visibleDetails"><a v-on:click.prevent="toggleDetails();">Follow</a> for  the rabbit.</p>
                    <div v-show="visibleDetails">
                        <hr>
                        <div class="tabs is-medium is-toggle">
                            <ul>
                                <li class="is-marginless" v-bind:class="{ 'is-active' : isTabActive('request') }">
                                    <a class="no-text-decoration" v-on:click.prevent="changeTab('request');">
                                        <span class="icon is-small"><i class="fas fa-upload"></i></span>
                                        <span>Request</span>
                                    </a>
                                </li>
                                <li class="is-marginless" v-bind:class="{ 'is-active' : isTabActive('response') }">
                                    <a class="no-text-decoration" v-on:click.prevent="changeTab('response');">
                                        <span class="icon is-small"><i class="fas fa-download"></i></span>
                                        <span>Response</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="panel" v-show="isTabActive('request')">
                            <h2>Api request method & url:</h2>
                            <pre>{{ error.request.method }} {{ error.request.url }}</pre>
                            <div class="tabs is-small is-toggle">
                                <ul>
                                    <li class="is-marginless" v-bind:class="{ 'is-active' : isRequestTabActive('body') }">
                                        <a class="no-text-decoration" v-on:click.prevent="changeRequestTab('body');">
                                            <span class="icon is-small"><i class="fas fa-database"></i></span>
                                            <span>Body</span>
                                        </a>
                                    </li>
                                    <li class="is-marginless" v-bind:class="{ 'is-active' : isRequestTabActive('headers') }">
                                        <a class="no-text-decoration" v-on:click.prevent="changeRequestTab('headers');">
                                            <span class="icon is-small"><i class="fas fa-list"></i></span>
                                            <span>Headers</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div v-show="isRequestTabActive('body')">
                                <h2>Api request body:</h2>
                                <pre>{{ error.request.body }}</pre>
                            </div>
                            <div v-show="isRequestTabActive('headers')">
                                <h2>Api request headers:</h2>
                                <pre>{{ error.request.headers }}</pre>
                            </div>
                        </div>
                        <div class="panel" v-show="isTabActive('response')">
                            <div class="tabs is-small is-toggle">
                                <ul>
                                    <li class="is-marginless" v-bind:class="{ 'is-active' : isResponseTabActive('text') }">
                                        <a class="no-text-decoration" v-on:click.prevent="changeResponseTab('text');">
                                        <span class="icon is-small"><i class="fas fa-database"></i></span>
                                            <span>Body</span>
                                        </a>
                                    </li>
                                    <li class="is-marginless" v-bind:class="{ 'is-active' : isResponseTabActive('headers') }">
                                        <a class="no-text-decoration" v-on:click.prevent="changeResponseTab('headers');">
                                            <span class="icon is-small"><i class="fas fa-list"></i></span>
                                            <span>Headers</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div v-show="isResponseTabActive('text')">
                                <h2>Api response text:</h2>
                                <pre>{{ error.response.text }}</pre>
                            </div>
                            <div v-show="isResponseTabActive('headers')">
                                <h2>Api response headers:</h2>
                                <pre>{{ error.response.headers }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-link" v-on:click.prevent="onClose">Close</button>
            </footer>
        </div>
    </div>
`;

export default {
    name: 'homedocs-modal-api-error',
    template: template,
    data: function () {
        return ({
            visibleDetails: false,
            activeTab: "request",
            activeRequestTab: "body",
            activeResponseTab: "text"
        });
    },
    props: [
        'error'
    ],
    computed: {
        serverReturnError: function () {
            return (this.error && this.error.response && this.error.response.status != 0);
        }
    },
    methods: {
        toggleDetails() {
            this.visibleDetails = !this.visibleDetails;
        },
        changeTab(tab) {
            if (tab && tab != this.activeTab) {
                this.activeTab = tab;
            }
        },
        changeRequestTab(tab) {
            if (tab && tab != this.activeRequestTab) {
                this.activeRequestTab = tab;
            }
        },
        changeResponseTab(tab) {
            if (tab && tab != this.activeResponseTab) {
                this.activeResponseTab = tab;
            }
        },
        isTabActive(tab) {
            return (this.activeTab == tab);
        },
        isRequestTabActive: function (tab) {
            return (this.activeRequestTab == tab);
        },
        isResponseTabActive: function (tab) {
            return (this.activeResponseTab == tab);
        },
        onClose: function() {
            this.$emit('close');
        }
    }
}