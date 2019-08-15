import { default as homedocsAPI } from './api.js';
import { default as validator } from './validator.js';
import { default as controlInputTags } from './control-input-tags.js';
import { mixinDateTimes, mixinFiles } from './mixins.js';
import { uuid as uuid } from './utils.js';

const template = `
    <form>
        <h1 class="title is-1" v-if="isAddForm">Add new document</h1>
        <h1 class="title is-1" v-else>Update/view document</h1>
        <button type="button" class="button is-dark is-fullwidth" v-on:click.prevent="onSave" v-bind:disabled="loading"><span class="icon"><i class="fas fa-save"></i></span><span>Save pending changes</span></button>
        <div class="field" v-if="! isAddForm">
            <label class="label">Document created on {{ document.createdOnTimestamp | timestamp2HumanDateTime }}</label>
        </div>
        <div class="field">
            <label class="label">Title</label>
            <div class="control" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('title') }">
                <input class="input" ref="title" type="text" maxlength="128" placeholder="Type document title" v-model.trim="document.title" v-bind:disabled="loading">
                <span class="icon is-small is-right" v-show="validator.hasInvalidField('title')"><i class="fas fa-exclamation-triangle"></i></span>
                <p class="help is-danger" v-show="validator.hasInvalidField('title')">{{ validator.getInvalidFieldMessage('title') }}</p>
            </div>
        </div>
        <div class="field">
            <label class="label">Description</label>
            <div class="control" v-bind:class="{ 'has-icons-right' : validator.hasInvalidField('description') }">
                <textarea class="textarea" ref="description" maxlength="4096" placeholder="Type (optional) document description" v-model.trim="document.description" v-bind:disabled="loading" rows="8"></textarea>
                <span class="icon is-small is-right" v-show="validator.hasInvalidField('description')"><i class="fas fa-exclamation-triangle"></i></span>
                <p class="help is-danger" v-show="validator.hasInvalidField('description')">{{ validator.getInvalidFieldMessage('description') }}</p>
            </div>
        </div>
        <div class="field">
            <label class="label">Tags</label>
            <homedocs-control-input-tags v-bind:tags="document.tags" v-on:update="document.tags = $event.tags" v-bind:disabled="loading"></homedocs-control-input-tags>
        </div>

        <div class="field">
            <label class="label">Files</label>
            <button type="button" class="button is-small is-dark" v-if="pendingUploads == 0" v-on:click.prevent="$refs.file.click()" v-bind:disabled="loading"><span class="icon"><i class="fas fa-file-upload"></i></span><span>Add new</span></button>
            <button type="button" class="button is-small is-dark" disabled v-else><span class="icon"><i class="fas fa-cog fa-spin"></i></span><span>Uploading {{ pendingUploads }} file/s...</span></button>
        </div>
        <div class="notification" v-if="uploadErrors.length > 0">
            <ul>
                <li v-for="uploadError in uploadErrors"><i class="fas fa-exclamation-triangle"></i> {{ uploadError }}</li>
            </ul>
        </div>
        <input type="file" multiple="multiple" ref="file" class="is-hidden" v-on:change="onFileChanged">
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
                <tr v-for="file, idx in document.files" v-bind:key="file.id">
                    <td>{{ file.uploadedOnTimestamp | timestamp2HumanDateTime }}</td>
                    <td><a v-bind:href="'/api2/file/' + file.id">{{ file.name }}</a></td>
                    <td>{{ file.size | humanFileSize }}</td>
                    <td v-if="confirmDeleteFileId == null || confirmDeleteFileId != file.id">
                        <button type="button" v-bind:disabled="! isImage(file.name) || loading" class="button is-light" v-on:click.prevent="showPreview(file.id)"><span class="icon"><i class="fas fa-folder-open"></i></span><span class="is-hidden-mobile">Open/Preview</span></button>
                        <a v-bind:href="'/api2/file/' + file.id" class="button is-light" v-bind:disabled="loading"><span class="icon"><i class="fas fa-download"></i></span><span class="is-hidden-mobile">Download</span></a>
                        <button type="button" class="button is-light" v-on:click.prevent="confirmDeleteFileId = file.id"><span class="icon"><i class="fas fa-trash-alt"></i></span><span class="is-hidden-mobile">Remove</span></button>
                    </td>
                    <td v-else-if="confirmDeleteFileId == file.id">
                        <div class="field has-addons">
                            <p class="control">
                                <a class="button is-dark" v-on:click.prevent="onFileRemove(idx)">
                                    <span class="icon is-small">
                                    <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span>Confirm file remove</span>
                                </a>
                            </p>
                            <p class="control">
                                <a class="button is-light" v-on:click.prevent="confirmDeleteFileId = null">
                                    <span class="icon is-small">
                                    <i class="fas fa-ban"></i>
                                    </span>
                                    <span>Cancel</span>
                                </a>
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="control">
            <button type="button" class="button is-dark is-fullwidth" v-if="! isAddForm" v-bind:disabled="loading || confirmDeleteDocumentId" v-on:click.prevent="confirmDeleteDocumentId = document.id"><span class="icon"><i class="fas fa-trash"></i></span><span>Remove this document</span></button>
        </p>

        <div class="notification" v-if="confirmDeleteDocumentId">
            <h3 class="title"><i class="fas fa-exclamation-triangle"></i> WARNING</h3>
            <h4 class="subtitle">This operation can not be undone. Are you sure you want to remove this document (tags & files included)</h4>

            <p class="buttons">
                <a class="button is-dark is-full-width" v-on:click.prevent="onDocumentRemove(document.id)">
                    <span class="icon is-small">
                    <i class="fas fa-check-circle"></i>
                    </span>
                    <span>Confirm document remove</span>
                </a>
                <a class="button is-light is-full-width" v-on:click.prevent="confirmDeleteDocumentId = null">
                    <span class="icon is-small">
                    <i class="fas fa-ban"></i>
                    </span>
                    <span>Cancel</span>
                </a>
            </p>
        </div>

        <div class="modal is-active" v-if="isPreviewVisible">
            <div class="modal-background"></div>
                <div class="modal-content">
                    <div class="notification" v-if="previewError">
                        <h1 class="title"><i class="fas fa-exclamation-triangle"></i> Error loading preview</h1>
                        <h2 class="sub-title">URL: <strong>{{ '/api2/file/' + previewFileId }}</strong></h2>
                    </div>
                    <p class="image" v-else>
                    <img v-bind:src="'/api2/file/' + previewFileId" alt="image preview" v-on:error="previewError = true;">
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
            validator: validator,
            formMode: null, // 0 = ADD, 1 = UPDATE/VIEW
            file: null,
            document: {
                id: null,
                title: null,
                description: null,
                tags: [],
                files: []
            },
            previewFileId: null,
            previewError: false,
            pendingUploads: 0,
            uploadErrors: [],
            confirmDeleteDocumentId: null,
            confirmDeleteFileId: null
        });
    },
    computed: {
        isPreviewVisible: function () {
            return (this.previewFileId);
        },
        isAddForm: function () {
            return (this.formMode == 0);
        },
        isUpdateViewForm: function () {
            return (this.formMode == 1);
        }
    },
    watch: {
        '$route': function (to, from) {
            if (to.name == "appAddDocument") {
                this.formMode = 0;
                this.document = {
                    id: null,
                    title: null,
                    description: null,
                    tags: [],
                    files: []
                };
            } else {
                this.formMode = 1;
                this.document = {
                    id: this.$route.params.id || null,
                    title: null,
                    description: null,
                    tags: [],
                    files: []
                };
                if (this.document.id) {
                    this.onRefresh();
                }
            }
            this.validator.clear();

        },
        'pendingUploads': function (to, from) {
            if (to > 0) {
                this.loading = true;
            } else {
                this.loading = false;
            }
        }
    },
    mixins: [
        mixinDateTimes, mixinFiles
    ],
    created: function () {
        this.document.id = this.$route.params.id || null;
    },
    mounted: function () {
        if (this.$route.params.id) {
            this.document.id = this.$route.params.id;
            this.formMode = 1;
        } else {
            this.formMode = 0;
        }
        if (this.isUpdateViewForm) {
            this.onRefresh();
        }
    },
    components: {
        'homedocs-control-input-tags': controlInputTags
    },
    methods: {
        isValid: function () {
            let valid = true;
            if (this.document.title && this.document.title.length <= 128) {
                if (this.document.description != null) {
                    if (this.document.description.length <= 4096) {
                    } else {
                        this.validator.setInvalid("description", "Invalid document description (field is not required, max length 4096 chars");
                        this.$nextTick(() => this.$refs.description.focus());
                        valid = false;
                    }
                }
            } else {
                this.validator.setInvalid("title", "Invalid document title (field is required, max length 128 chars");
                this.$nextTick(() => this.$refs.title.focus());
                valid = false;
            }
            return (valid);
        },
        showPreview: function (fileId) {
            this.previewFileId = fileId;
            this.previewError = false;
            window.addEventListener('keydown', this.onKeyPress);
        },
        hidePreview: function (fileId) {
            this.previewFileId = null;
            window.removeEventListener('keydown', this.onKeyPress);
        },
        onKeyPress: function (e) {
            if (e.code == "Escape") {
                this.hidePreview();
            }
        },
        isImage: function (filename) {
            if (filename) {
                return (filename.match(/.(jpg|jpeg|png|gif)$/i));
            } else {
                return (false);
            }
        },
        onFileChanged: function (event) {
            this.uploadErrors = [];
            this.pendingUploads += event.target.files.length;
            for (let i = 0; i < event.target.files.length; i++) {
                if (event.target.files[i].size <= initialState.maxUploadFileSize) {
                    homedocsAPI.document.addFile(uuid(), event.target.files[i], (response) => {
                        this.pendingUploads--;
                        if (response.ok) {
                            this.document.files.push(response.body.data);
                        } else {
                            this.uploadErrors.push("Can not upload local file " + event.target.files[i].name + " (server error)");
                            this.$emit("showAPIError", response.getApiErrorData());
                        }
                    });
                } else {
                    this.pendingUploads--;
                    this.uploadErrors.push("Can not upload local file " + event.target.files[i].name + " (max upload size supported by server: " + initialState.maxUploadFileSize + " bytes, file size: " + event.target.files[i].size + " bytes)");
                }
            };
        },
        onFileRemove: function (fileIndex) {
            if (fileIndex > -1) {
                this.confirmDeleteFileId = null;
                this.document.files.splice(fileIndex, 1);
            }
        },
        onSave: function () {
            if (!this.loading) {                
                if (this.isValid()) {
                    this.loading = true;
                    if (this.isUpdateViewForm) {
                        homedocsAPI.document.update(this.document, (response) => {
                            if (response.ok) {
                                this.loading = false;
                                // clear tag cache
                                initialState.cachedTags = null;
                                this.onRefresh();
                            } else {
                                this.$emit("showAPIError", response.getApiErrorData());
                                this.loading = false;
                            }
                        });
                    } else {
                        this.document.id = uuid();
                        homedocsAPI.document.add(this.document, (response) => {
                            if (response.ok) {
                                if (this.document.tags.length > 0) {
                                    // clear tag cache for refreshing new tags
                                    initialState.cachedTags = null;
                                }
                                this.$router.push({ name: 'appOpenDocument', params: { id: this.document.id } });
                            } else {
                                this.$emit("showAPIError", response.getApiErrorData());
                            }
                            this.loading = false;
                        });
                    }
                }
            }

        },
        onDocumentRemove: function (id) {
            if (!this.loading) {
                this.loading = true;
                homedocsAPI.document.remove(id, (response) => {
                    if (response.ok) {
                        this.loading = false;
                        this.$router.push({ name: 'appDashBoard' });
                    } else {
                        this.$emit("showAPIError", response.getApiErrorData());
                        this.loading = false;
                    }
                });
            }
        },
        onRefresh: function () {
            this.pendingUploads = 0;
            this.uploadErrors = [];
            this.confirmDeleteDocumentId = null;
            this.confirmDeleteFileId = null;
            if (!this.loading) {
                this.loading = true;
                homedocsAPI.document.get(this.$route.params.id, (response) => {
                    if (response.ok) {
                        this.document = response.body.data;
                    } else {
                        this.$emit("showAPIError", response.getApiErrorData());
                    }
                    this.loading = false;
                });
            }
        }
    }
}