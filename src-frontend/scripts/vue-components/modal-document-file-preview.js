const template = `
    <div class="modal is-active" v-if="index >= 0">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head has-text-centered">
                <p class="modal-card-title">{{ fileName }} ({{ $utils.humanFileSize(fileSize, true) }})</p>
                <button class="delete" aria-label="close" v-on:click.prevent="onClose"></button>
            </header>
            <section class="modal-card-body" v-if="hasPreviewSupport">
                <div class="notification" v-if="error">
                    <h1 class="title"><i class="fas fa-exclamation-triangle"></i> Error loading preview</h1>
                    <h2 class="sub-title">URL: <strong>{{ 'api2/file/' + fileId }}</strong></h2>
                </div>
                <p class="image" v-else>
                    <img v-bind:src="'api2/file/' + fileId" alt="image preview" v-on:error="error = true;">
                </p>
            </section>
            <section class="modal-card-body" v-else>
                <p class="has-text-centered">Preview not available</p>
            </section>
            <footer class="modal-card-foot">
                <button class="button" v-on:click.prevent="onPrevious" v-bind:disabled="isPreviousButtonDisabled">
                    <span class="icon is-small">
                        <i class="fas fa-caret-left"></i>
                    </span>
                    <span>Previous</span>
                </button>
                <button class="button is-static">File {{ index + 1 }} of {{ totalFiles }}</button>
                <button class="button" v-on:click.prevent="onNext" v-bind:disabled="isNextButtonDisabled">
                    <span class="icon is-small">
                        <i class="fas fa-caret-right"></i>
                    </span>
                    <span>Next</span>
                </button>
            </footer>
        </div>
        <button class="modal-close is-large" aria-label="close" v-on:click.prevent="onClose"></button>
    </div>
`;

export default {
  name: "homedocs-modal-document-file-preview",
  template: template,
  data: function () {
    return {
      error: false,
      index: 0,
    };
  },
  props: ["files", "previewIndex"],
  created: function () {
    window.addEventListener("keydown", this.onKeyPress);
    this.index = this.previewIndex;
  },
  beforeUnmount: function () {
    document.removeEventListener("keydown", this.onKeyPress);
  },
  computed: {
    totalFiles: function () {
      return this.files.length;
    },
    fileId: function () {
      return this.files[this.index].id;
    },
    fileName: function () {
      return this.files[this.index].name;
    },
    fileSize: function () {
      return this.files[this.index].size;
    },
    isPreviousButtonDisabled: function () {
      return this.index < 1;
    },
    isNextButtonDisabled: function () {
      return this.index >= this.files.length - 1;
    },
    hasPreviewSupport: function () {
      return this.$utils.isImage(this.files[this.index].name);
    },
  },
  methods: {
    onPrevious: function () {
      if (this.index > 0) {
        this.index--;
      }
    },
    onNext: function () {
      if (this.index < this.files.length - 1) {
        this.index++;
      }
    },
    onClose: function () {
      window.removeEventListener("keydown", this.onKeyPress);
      this.$emit("onClose");
    },
    onKeyPress: function (e) {
      switch (e.code) {
        case "Escape":
          this.onClose();
          break;
        case "ArrowLeft":
          this.onPrevious();
          break;
        case "ArrowRight":
          this.onNext();
          break;
      }
    },
  },
};
