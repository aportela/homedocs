import dayjs from "dayjs";
import { default as controlInputTags } from "../vue-components/control-input-tags.js";
import { default as modalDocumentFilePreview } from "../vue-components/modal-document-file-preview.js";
import { default as modalConfirm } from "../vue-components/modal-confirm.js";
import { default as apiErrorNotification } from "../vue-components/notification-api-error.js";

const template = `
    <form>
        <h1 class="title is-1" v-if="isAddForm">{{ $t("pages.document.labels.headerAddNew") }}</h1>
        <h1 class="title is-1" v-else>{{ $t("pages.document.labels.headerUpdate") }}</h1>
        <div class="field" v-if="! isAddForm">
            <label class="label">{{ $t("pages.document.labels.headerCreatedOn") }} {{ document.created }}</label>
        </div>
        <div class="field">
            <label class="label">{{ $t("pages.document.labels.title") }}</label>
            <div class="control" v-bind:class="{ 'has-icons-right' : $validator.hasInvalidField('title') }">
                <input class="input" ref="title" type="text" maxlength="128" :placeholder="$t('pages.document.labels.title')" v-model.trim="document.title" v-bind:disabled="loading">
                <span class="icon is-small is-right" v-show="$validator.hasInvalidField('title')"><i class="fas fa-exclamation-triangle"></i></span>
                <p class="help is-danger" v-show="$validator.hasInvalidField('title')">{{ $validator.getInvalidFieldMessage('title') }}</p>
            </div>
        </div>
        <div class="field">
            <label class="label">{{ $t("pages.document.labels.description") }}</label>
            <div class="control" v-bind:class="{ 'has-icons-right' : $validator.hasInvalidField('description') }">
                <textarea class="textarea" ref="description" maxlength="4096" :placeholder="$t('pages.document.labels.descriptionInputPlaceholder')" v-model.trim="document.description" v-bind:disabled="loading" rows="8"></textarea>
                <span class="icon is-small is-right" v-show="$validator.hasInvalidField('description')"><i class="fas fa-exclamation-triangle"></i></span>
                <p class="help is-danger" v-show="$validator.hasInvalidField('description')">{{ $validator.getInvalidFieldMessage('description') }}</p>
            </div>
        </div>
        <div class="field">
            <label class="label">{{ $t("pages.document.labels.tags") }}</label>
            <homedocs-control-input-tags v-bind:allowNavigation="true" v-bind:tags="document.tags" v-on:update="document.tags = $event.tags;" v-bind:disabled="loading"></homedocs-control-input-tags>
        </div>

        <div class="field">
            <label class="label">{{ $t("pages.document.labels.attachments") }}</label>
            <button type="button" class="button is-dark" v-if="pendingUploads == 0" v-on:click.prevent="$refs.file.click()" v-bind:disabled="loading"><span class="icon"><i class="fas fa-file-upload"></i></span><span>{{ $t("pages.document.labels.addNewAttachment") }}</span></button>
            <button type="button" class="button is-dark" disabled v-else><span class="icon"><i class="fas fa-cog fa-spin"></i></span><span>{{ $t("pages.document.labels.uploading") }} {{ pendingUploads }} {{ $t("pages.document.labels.files") }}</span></button>
        </div>
        <div class="notification" v-if="uploadErrors.length > 0">
            <ul>
                <li v-for="uploadError in uploadErrors"><i class="fas fa-exclamation-triangle"></i> {{ uploadError }}</li>
            </ul>
        </div>
        <input type="file" id="file" multiple="multiple" ref="file" class="is-hidden" v-on:change="onFileChanged">
        <table class="table is-narrow is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>{{ $t("pages.document.labels.fileUploadedOn") }}</th>
                    <th>{{ $t("pages.document.labels.fileName") }}</th>
                    <th class="has-text-right">{{ $t("pages.document.labels.fileSize") }}</th>
                    <th class="has-text-centered">{{ $t("pages.document.labels.fileActions") }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="file, idx in document.files" v-bind:key="file.id">
                    <td>{{ $utils.timestamp2HumanDateTime(file.uploadedOnTimestamp) }}</td>
                    <td><a v-bind:href="'api2/file/' + file.id">{{ file.name }}</a></td>
                    <td class="has-text-right">{{ $utils.humanFileSize(file.size, true) }}</td>
                    <td class="has-text-centered">
                        <div class="buttons has-addons is-centered">
                            <p class="control">
                                <button type="button" v-bind:disabled="! $utils.isImage(file.name) || loading" class="button is-light" v-on:click.prevent="showPreview(idx)"><span class="icon"><i class="fas fa-folder-open"></i></span><span class="is-hidden-mobile">{{ $t("pages.document.labels.buttonFileOpenPreview") }}</span></button>
                            </p>
                            <p class="control">
                                <a v-bind:href="'api2/file/' + file.id" class="button is-light"><span class="icon"><i class="fas fa-download"></i></span><span class="is-hidden-mobile">{{ $t("pages.document.labels.buttonFileDownload") }}</span></a>
                            </p>
                            <p class="control">
                                <button type="button" class="button is-light" v-on:click.prevent="confirmDeleteFileIndex = idx;" v-bind:disabled="loading"><span class="icon"><i class="fas fa-trash-alt"></i></span><span class="is-hidden-mobile">{{ $t("pages.document.labels.buttonFileRemove") }}</span></button>
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="button is-dark is-fullwidth" v-if="isAddForm" v-on:click.prevent="onSave" v-bind:disabled="loading || ! enableSave"><span class="icon"><i class="fas fa-save"></i></span><span>{{ $t("pages.document.labels.buttonSaveDocument") }}</span></button>
        <div class="columns" v-else>
            <div class="column is-half">
                <button type="button" class="button is-dark is-fullwidth" v-on:click.prevent="onSave" v-bind:disabled="loading || ! enableSave"><span class="icon"><i class="fas fa-save"></i></span><span>{{ $t("pages.document.labels.buttonSaveDocument") }}</span></button>
            </div>
            <div class="column is-half">
                <button type="button" class="button is-dark is-fullwidth" v-if="! isAddForm" v-bind:disabled="loading || confirmDeleteDocumentId" v-on:click.prevent="confirmDeleteDocumentId = document.id"><span class="icon"><i class="fas fa-trash"></i></span><span>{{ $t("pages.document.labels.buttonDeleteDocument") }}</span></button>
            </div>
        </div>

        <homedocs-modal-document-file-preview v-if="! loading && isPreviewVisible" v-bind:files="document.files" v-bind:previewIndex="previewFileIndex" v-on:onClose="hidePreview"></homedocs-modal-document-file-preview>

        <homedocs-modal-confirm v-if="removeDocumentFileModalVisible" v-on:onCancel="confirmDeleteFileIndex = -1" v-on:onConfirm="onFileRemove(confirmDeleteFileIndex)">
            <template v-slot:title>
            {{ $t("pages.document.labels.modalRemoveDocumentFileHeader") }}
            </template>
            <template v-slot:body>
                <h4><i class="fas fa-exclamation-triangle"></i> <strong>{{ $t("pages.document.labels.warning") }}</strong></h4>
                <h5 class="mt-2">{{ $t("pages.document.labels.removeDocumentFileModalMessage") }}</h5>
            </template>
        </homedocs-modal-confirm>

        <homedocs-modal-confirm v-if="removeDocumentModalVisible" v-on:onCancel="confirmDeleteDocumentId = null" v-on:onConfirm="onDocumentRemove(confirmDeleteDocumentId)">
            <template v-slot:title>
            {{ $t("pages.document.labels.modalDeleteDocumentHeader") }}
            </template>
            <template v-slot:body>
                <h4><i class="fas fa-exclamation-triangle"></i> <strong>{{ $t("pages.document.labels.warning") }}</strong></h4>
                <h5 class="mt-2">{{ $t("pages.document.labels.deleteDocumentModalMessage") }}</h5>
            </template>
        </homedocs-modal-confirm>

    </form>

`;

export default {
  name: "homedocs-document",
  template: template,
  data: function () {
    return {
      loading: false,
      formMode: null, // 0 = ADD, 1 = UPDATE/VIEW
      file: null,
      document: {
        id: null,
        title: null,
        description: null,
        tags: [],
        files: [],
        createdOnTimestamp: null,
        created: null,
      },
      previewFileIndex: -1,
      isPreviewVisible: false,
      previewFileId: null,
      previewError: false,
      pendingUploads: 0,
      uploadErrors: [],
      confirmDeleteDocumentId: null,
      confirmDeleteFileIndex: -1,
      apiError: null,
    };
  },
  computed: {
    isAddForm: function () {
      return this.formMode == 0;
    },
    isUpdateViewForm: function () {
      return this.formMode == 1;
    },
    removeDocumentFileModalVisible: function () {
      return this.confirmDeleteFileIndex >= 0;
    },
    removeDocumentModalVisible: function () {
      return this.confirmDeleteDocumentId;
    },
    enableSave: function () {
      return this.document.title != null;
    },
  },
  watch: {
    $route: function (to, from) {
      if (to.name == "appAddDocument") {
        this.formMode = 0;
        this.document = {
          id: null,
          title: null,
          description: null,
          tags: [],
          files: [],
        };
      } else {
        this.formMode = 1;
        this.document = {
          id: this.$route.params.id || null,
          title: null,
          description: null,
          tags: [],
          files: [],
        };
        if (this.document.id) {
          this.onRefresh();
        }
      }
      this.$validator.clear();
    },
    pendingUploads: function (to, from) {
      if (to > 0) {
        this.loading = true;
      } else {
        this.loading = false;
      }
    },
  },
  created: function () {
    this.document.id = this.$route.params.id || null;
    document.addEventListener("dragover", (e) => {
      e.preventDefault();
    });
    document.addEventListener("drop", this.onFileDropped);
  },
  beforeUnmount: function () {
    document.removeEventListener("dragover", (e) => {
      e.preventDefault();
    });
    document.removeEventListener("drop", this.onFileDropped);
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
    } else {
      this.$nextTick(() => this.$refs.title.focus());
    }
  },
  components: {
    "homedocs-control-input-tags": controlInputTags,
    "homedocs-modal-document-file-preview": modalDocumentFilePreview,
    "homedocs-modal-confirm": modalConfirm,
    "homedocs-notification-api-error": apiErrorNotification,
  },
  methods: {
    isValid: function () {
      let valid = true;
      if (this.document.title && this.document.title.length <= 128) {
        if (this.document.description != null) {
          if (this.document.description.length <= 4096) {
          } else {
            this.$validator.setInvalid(
              "description",
              this.$t("pages.document.errorMessages.invalidDocumentDescription")
            );
            this.$nextTick(() => this.$refs.description.focus());
            valid = false;
          }
        }
      } else {
        this.$validator.setInvalid(
          "title",
          this.$t("pages.document.errorMessages.invalidDocumentTitle")
        );
        this.$nextTick(() => this.$refs.title.focus());
        valid = false;
      }
      return valid;
    },
    showPreview: function (fileIndex) {
      this.isPreviewVisible = true;
      this.previewFileIndex = fileIndex;
    },
    hidePreview: function () {
      this.isPreviewVisible = false;
      this.previewFileIndex = -1;
    },
    onFileChanged: function (event) {
      this.uploadErrors = [];
      this.pendingUploads += event.target.files.length;
      for (let i = 0; i < event.target.files.length; i++) {
        if (event.target.files[i].size <= initialState.maxUploadFileSize) {
          this.$api.document
            .addFile(this.$utils.uuid(), event.target.files[i])
            .then((response) => {
              this.pendingUploads--;
              this.document.files.push(response.data.data);
            })
            .catch((error) => {
              this.uploadErrors.push(
                this.$t("pages.document.errorMessages.errorUploadingFile") +
                  event.target.files[i].name +
                  " (server error)"
              );
              this.pendingUploads--;
              //this.apiError = error.response.getApiErrorData();
              this.loading = false;
            });
        } else {
          this.pendingUploads--;
          this.uploadErrors.push(
            this.$t("pages.document.errorMessages.errorUploadingFile") +
              event.target.files[i].name +
              this.$t(
                "pages.document.errorMessages.errorUploadingFileMaxUploadFileSize"
              ) +
              initialState.maxUploadFileSize +
              this.$t(
                "pages.document.errorMessages.errorUploadingFileBytesFileSize"
              ) +
              event.target.files[i].size +
              this.$t("pages.document.errorMessages.errorUploadingFileBytes")
          );
        }
      }
    },
    onFileDropped: function (event) {
      event.preventDefault();
      this.uploadErrors = [];
      this.pendingUploads += event.dataTransfer.files.length;
      for (let i = 0; i < event.dataTransfer.files.length; i++) {
        if (
          event.dataTransfer.files[i].size <= initialState.maxUploadFileSize
        ) {
          this.$api.document
            .addFile(this.$utils.uuid(), event.dataTransfer.files[i])
            .then((response) => {
              this.pendingUploads--;
              this.document.files.push(response.data.data);
            })
            .catch((error) => {
              this.uploadErrors.push(
                this.$t("pages.document.errorMessages.errorUploadingFile") +
                  event.dataTransfer.files[i].name +
                  this.$t(
                    "pages.document.errorMessages.errorUploadingFileServerError"
                  )
              );
              this.pendingUploads--;
              //this.apiError = error.response.getApiErrorData();
              this.loading = false;
            });
        } else {
          this.pendingUploads--;
          this.uploadErrors.push(
            this.$t("pages.document.errorMessages.errorUploadingFile") +
              event.dataTransfer.files[i].name +
              this.$t(
                "pages.document.errorMessages.errorUploadingFileMaxUploadFileSize"
              ) +
              initialState.maxUploadFileSize +
              this.$t(
                "pages.document.errorMessages.errorUploadingFileBytesFileSize"
              ) +
              event.dataTransfer.files[i].size +
              this.$t("pages.document.errorMessages.errorUploadingFileBytes")
          );
        }
      }
    },
    onFileRemove: function (fileIndex) {
      if (fileIndex > -1) {
        this.document.files.splice(fileIndex, 1);
        this.confirmDeleteFileIndex = -1;
      }
    },
    onSave: function () {
      if (!this.loading) {
        if (this.isValid()) {
          this.loading = true;
          if (this.isUpdateViewForm) {
            this.$api.document
              .update(this.document)
              .then((response) => {
                this.loading = false;
                // clear tag cache
                initialState.cachedTags = null;
                this.onRefresh();
              })
              .catch((error) => {
                this.loading = false;
                this.$emit("showAPIError", {
                  data: error.response.getApiErrorData(),
                });
              });
          } else {
            this.document.id = this.$utils.uuid();
            this.$api.document
              .add(this.document)
              .then((response) => {
                this.loading = false;
                if (this.document.tags.length > 0) {
                  // clear tag cache for refreshing new tags
                  initialState.cachedTags = null;
                }
                this.$router.push({
                  name: "appOpenDocument",
                  params: { id: this.document.id },
                });
              })
              .catch((error) => {
                this.loading = false;
                this.$emit("showAPIError", {
                  data: error.response.getApiErrorData(),
                });
              });
          }
        }
      }
    },
    onDocumentRemove: function (id) {
      if (!this.loading) {
        this.loading = true;
        this.$api.document
          .remove(id)
          .then((response) => {
            this.loading = false;
            this.$router.push({ name: "appDashBoard" });
          })
          .catch((error) => {
            this.loading = false;
            this.$emit("showAPIError", {
              data: error.response.getApiErrorData(),
            });
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
        this.$api.document
          .get(this.$route.params.id)
          .then((response) => {
            this.document = response.data.data;
            this.document.created = dayjs
              .unix(this.document.createdOnTimestamp)
              .format("YYYY-MM-DD HH:mm:ss");
            this.loading = false;
            this.$nextTick(() => this.$refs.title.focus());
          })
          .catch((error) => {
            this.loading = false;
            this.$emit("showAPIError", {
              data: error.response.getApiErrorData(),
            });
          });
      }
    },
  },
};
