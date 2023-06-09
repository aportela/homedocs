const template = `
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head has-text-centered">
                <p class="modal-card-title">
                    <slot name="title"></slot>
                </p>
                <button class="delete" aria-label="close" v-on:click.prevent="onCancel"></button>
            </header>
            <section class="modal-card-body">
                <slot name="body"></slot>
                <h5 class="mt-2">{{ $t("components.confirmModal.doYouWishToContinue") }}</h5>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-dark" v-on:click.prevent="onConfirm"><span class="icon"><i class="fas fa-trash"></i></span> <span>{{ $t("components.confirmModal.okButton") }}</span></button>
                <button class="button" v-on:click.prevent="onCancel"><span class="icon"><i class="fas fa-ban"></i></span> <span>{{ $t("components.confirmModal.cancelButton") }}</span></button>
            </footer>
        </div>
    </div>
`;

export default {
  name: "homedocs-modal-confirm",
  template: template,
  created: function () {
    window.addEventListener("keydown", this.onKeyPress);
  },
  beforeUnmount: function () {
    document.removeEventListener("keydown", this.onKeyPress);
  },
  methods: {
    onKeyPress: function (e) {
      switch (e.code) {
        case "Escape":
          this.onCancel();
          break;
      }
    },
    onConfirm: function () {
      this.$emit("onConfirm");
    },
    onCancel: function () {
      this.$emit("onCancel");
    },
  },
};
