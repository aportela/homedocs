const template = `
    <div>
        <div class="field is-grouped is-grouped-multiline">
            <div class="control" v-for="tag in tags">
                <div class="tags has-addons" v-if="allowNavigation">
                    <router-link v-bind:to="{ name: 'appAdvancedSearch', params: { tags: [ tag ], launch: true } }">
                        <div class="tags has-addons">
                            <span class="tag is-medium is-dark">{{ tag }}</span>
                            <a class="tag is-medium is-delete" v-on:click.prevent="onRemove(tag)" v-bind:disabled="loading"></a>
                        </div>
                    </router-link>
                </div>
                <div class="tags has-addons" v-else>
                    <span class="tag is-medium is-dark">{{ tag }}</span>
                    <a class="tag is-medium is-delete" v-on:click.prevent="onRemove(tag)" v-bind:disabled="loading"></a>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="label">Add tag</label>
            <div class="control">
                <div class="field has-addons">
                    <p class="control is-expanded" v-bind:class="{ 'has-icons-right': warningAlreadyExists }">
                        <input v-bind:disabled="loading" class="input" maxlength="32" v-bind:class="{ 'is-danger': warningAlreadyExists }" v-model.trim="newTag" v-on:keydown.enter.prevent="" v-on:keyup.prevent="onKeyUp($event)" type="text" placeholder="Type tag name (confirm with return)">
                        <span class="icon is-small is-right" v-show="warningAlreadyExists">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    </p>
                    <p class="control" v-show="newTag"><button type="button" class="button is-dark cursor-help" v-on:click.prevent="onAdd" title="Click here to add tag"><i class="fas fa-check"></i></button></p>
                </div>
                <p class="help is-danger" v-show="warningAlreadyExists">Tag already exists</p>
                <div class="dropdown is-active" v-if="hasResults">
                    <div class="dropdown-menu">
                        <div class="dropdown-content is-unselectable">
                            <a href="#" class="dropdown-item" v-bind:class="{ 'is-active': selectedMatchTagIndex == index }" v-for="tag, index in matchedTags" v-bind:key="tag" v-on:click.prevent="onSelect(tag)" v-bind:disabled="loading">
                                <span>{{ tag }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
`;

export default {
    name: 'homedocs-control-input-tags',
    template: template,
    data: function () {
        return ({
            newTag: null,
            matchedTags: [],
            selectedMatchTagIndex: -1,
            warningAlreadyExists: false
        });
    },
    props: [
        'loading', 'tags', 'allowNavigation'
    ],
    computed: {
        hasResults: function () {
            return (this.matchedTags.length > 0);
        }
    },
    created: function () {
        if (!Array.isArray(initialState.cachedTags)) {
            this.loadCurrentTagCache();
        }
    },
    methods: {
        loadCurrentTagCache: function () {
            this.$api.tag.search().then(response => {
                initialState.cachedTags = response.data.tags;
            }).catch(error => {
                // TODO
                //this.apiError = response.getApiErrorData();
            });
        },
        onKeyUp: function (event) {
            this.warningAlreadyExists = false;
            switch (event.code) {
                case "Escape":
                    this.newTag = null;
                    this.matchedTags = [];
                    this.selectedMatchTagIndex = -1;
                    break;
                case "Enter":
                    if (this.selectedMatchTagIndex != -1) {
                        this.newTag = this.matchedTags[this.selectedMatchTagIndex];
                    }
                    this.onAdd();
                    break;
                case "Backspace":
                    if (!this.newTag) {
                        /*
                            if (!this.newTag && this.tags.length > 0) {
                                this.$emit("onUpdate", this.tags.slice(0, this.tags.length - 1));
                            }
                        */
                        this.matchedTags = [];
                        this.selectedMatchTagIndex = -1;
                    }
                    break;
                case "ArrowUp":
                    if (this.selectedMatchTagIndex > 0) {
                        this.selectedMatchTagIndex--;
                    }
                    break;
                case "ArrowDown":
                    if (this.selectedMatchTagIndex < this.matchedTags.length - 1) {
                        this.selectedMatchTagIndex++;
                    }
                    break;
                default:
                    if (this.newTag) {
                        if (!this.tags.includes(this.newTag.toLowerCase())) {
                            if (Array.isArray(initialState.cachedTags)) {
                                this.matchedTags = initialState.cachedTags.filter((tag) => tag.indexOf(this.newTag) !== -1);
                            }
                        }
                    } else {
                        this.matchedTags = [];
                    }
                    this.selectedMatchTagIndex = -1;
                    break;
            }
        },
        onAdd: function () {
            if (this.newTag) {
                if (!this.tags.includes(this.newTag.toLowerCase())) {
                    this.$emit("update", { tags: this.tags.concat(Array(this.newTag.toLowerCase())) });
                    this.newTag = null;
                    this.matchedTags = [];
                } else {
                    this.warningAlreadyExists = true;
                }
            }
        },
        onSelect: function (tagName) {
            this.warningAlreadyExists = false;
            this.newTag = tagName;
            this.onAdd();
        },
        onRemove: function (tagName) {
            this.$emit("update", { tags: this.tags.filter(tag => tag !== tagName) });
        }
    }
}
