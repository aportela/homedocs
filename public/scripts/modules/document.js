import { default as homedocsAPI } from './api.js';
import { default as validator } from './validator.js';
import { default as controlInputTags } from './control-input-tags.js';

const template = `
    <form>
        <div class="field">
            <label class="label">Title</label>
            <div class="control">
                <input class="input" type="text" placeholder="Document title" v-model.trim="document.title">
            </div>
        </div>
        <div class="field">
            <label class="label">Description</label>
            <div class="control">
                <textarea class="textarea" placeholder="Document description" v-model.trim="document.description"></textarea>
            </div>
        </div>
        <div class="field">
            <label class="label">Tags</label>
            <homedocs-control-input-tags v-bind:tags="document.tags" v-on:update="document.tags = $event.tags"></homedocs-control-input-tags>
        </div>

        <div class="field">
            <label class="label">Files</label>
        </div>

        <table class="table is-narrow is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>On</th>
                    <th>Name</th>
                    <th>Size</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="file in document.files" v-bind:key="file.id">
                    <td>{{ file.uploadedTimestamp }}</td>
                    <td>{{ file.name }}</td>
                    <td>{{ file.size }}</td>
                </tr>
            </tbody>
        </table>
    </form>
`;

export default {
    name: 'homedocs-document',
    template: template,
    data: function () {
        return ({
            loading: false,
            apiError: null,
            document: {
                id: null,
                title: null,
                description: null,
                tags: [],
                files: []
            }
        });
    },
    created: function() {
        this.document.id = this.$route.params.id || null;
    },
    mounted: function() {
        if (this.document.id) {
            this.onRefresh();
        }
    },
    components: {
        'homedocs-control-input-tags': controlInputTags
    },
    methods: {
        onRefresh: function() {
            if (! this.loading) {
                this.loading = true;
                homedocsAPI.document.get(this.$route.params.id, (response) => {
                    if (response.ok) {
                        this.document = response.body.data;
                    } else {
                        this.apiError = response.getApiErrorData();
                    }
                    this.loading = false;
                });
            }
        }
    }
}