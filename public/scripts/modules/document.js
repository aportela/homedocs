import { default as homedocsAPI } from './api.js';
import { default as validator } from './validator.js';
import { default as controlInputTags } from './control-input-tags.js';
import { mixinDateTimes, mixinFiles } from './mixins.js';

const template = `
    <form>
        <div class="field">
            <label class="label">Document created on {{ document.createdOnTimestamp | timestamp2HumanDateTime }}</label>
        </div>
        <div class="field">
            <label class="label">Title</label>
            <div class="control">
                <input class="input" type="text" placeholder="Document title" v-model.trim="document.title">
            </div>
        </div>
        <div class="field">
            <label class="label">Description</label>
            <div class="control">
                <textarea class="textarea" placeholder="Document description" v-model.trim="document.description" rows="8"></textarea>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="file in document.files" v-bind:key="file.id">
                    <td>{{ file.uploadedOnTimestamp | timestamp2HumanDateTime }}</td>
                    <td><a v-bind:href="'/api2/file/' + file.id">{{ file.name }}</a></td>
                    <td>{{ file.size | humanFileSize }}</td>
                    <td>
                        <button type="button" v-bind:disabled="! isImage(file.name)" class="button is-light" v-on:click.prevent="showPreview(file.id)"><span class="icon"><i class="fas fa-folder-open"></i></span><span>Open/Preview</span></button>
                        <a v-bind:href="'/api2/file/' + file.id" class="button is-light"><span class="icon"><i class="fas fa-download"></i></span><span>Download</span></a>
                        <button type="button" class="button is-light" disabled><span class="icon"><i class="fas fa-trash-alt"></i></span><span>Remove</span></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="modal is-active" v-if="isPreviewVisible">
            <div class="modal-background"></div>
                <div class="modal-content">
                    <p class="image">
                    <img v-bind:src="'/api2/file/' + previewFileId" alt="image preview">
                </p>
            </div>
            <button class="modal-close is-large" aria-label="close" v-on:click.prevent="hidePreview"></button>
        </div>
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
            },
            previewFileId: null
        });
    },
    computed: {
        isPreviewVisible: function() {
            return(this.previewFileId);
        }
    },
    mixins: [
        mixinDateTimes, mixinFiles
    ],
    created: function () {
        this.document.id = this.$route.params.id || null;
    },
    mounted: function () {
        if (this.document.id) {
            this.onRefresh();
        }
    },
    components: {
        'homedocs-control-input-tags': controlInputTags
    },
    methods: {
        showPreview: function(fileId) {
            this.previewFileId = fileId;
        },
        hidePreview: function(fileId) {
            this.previewFileId = null;
        },
        isImage: function(filename) {
            if (filename) {
                return(filename.match(/.(jpg|jpeg|png|gif)$/i));
            } else {
                return(false);
            }
        },
        onRefresh: function () {
            if (!this.loading) {
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