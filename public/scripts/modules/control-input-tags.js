const template = `
    <div class="field is-grouped is-grouped-multiline">
        <div class="control" v-for="tag in tags">
            <div class="tags has-addons">
                <span class="tag is-medium is-dark">{{ tag }} </span>
                <a class="tag is-medium is-delete" v-on:click.prevent="onRemove(tag)"></a>
            </div>
        </div>
        <div class="control">
            <input :disabled="loading" class="input" maxlength="32" v-model.trim="newTag" v-on:keyup.prevent="onKeyUp($event)" type="text" placeholder="type tag name (confirm with return)">
            <div class="dropdown is-active" v-if="hasResults">
                <div class="dropdown-menu">
                    <div class="dropdown-content is-unselectable">
                        <a href="#" class="dropdown-item" v-bind:class="{ 'is-active': selectedMatchTagIndex == index }" v-for="tag, index in matchedTags" v-bind:key="tag" v-on:click.prevent="onSelect(tag)">
                            <span>{{ tag }}</span>
                        </a>
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
            initialStateTags: []
        });
    },
    props: [
        'loading', 'tags'
    ],
    computed: {
        hasResults: function () {
            return (this.matchedTags.length > 0);
        }
    },
    methods: {
        onKeyUp: function (event) {
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
                            this.matchedTags = this.initialStateTags.filter(tag => tag.indexOf(this.newTag) !== -1);
                        }
                    } else {
                        this.matchedTags = [];
                    }
                    this.selectedMatchTagIndex = -1;
                    break;
            }
        },
        onAdd: function () {
            if (this.newTag && !this.tags.includes(this.newTag.toLowerCase())) {
                this.$emit("update", {tags: this.tags.concat(Array(this.newTag.toLowerCase())) });
                this.newTag = null;
                this.matchedTags = [];
            }
        },
        onSelect: function (tagName) {
            this.newTag = tagName;
            this.onAdd();
        },
        onRemove: function (tagName) {
            this.$emit("update", { tags: this.tags.filter(tag => tag !== tagName) });
        }
    }
}
