
const template = `
    <div class="modal is-active">
        <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head has-text-centered">
                    <p class="modal-card-title">Delete / remove item</span></p>
                    <button class="delete" aria-label="close" v-on:click.prevent="onClose"></button>
                </header>
                <section class="modal-card-body">
                    <h4><i class="fas fa-exclamation-triangle"></i> <strong>WARNING:</strong>
                    <h4>You are about to delete an item, this operation cannot be undone.</h5>
                    <h5>Do you wish to continue ?</h5>
                </section>
                <footer class="modal-card-foot">
                    <button class="button is-dark" v-on:click.prevent="onConfirm"><span class="icon"><i class="fas fa-trash"></i></span> <span>Ok</span></button>
                    <button class="button" v-on:click.prevent="onCancel"><span class="icon"><i class="fas fa-ban"></i></span> <span>Cancel</span></button>
                </footer>
            </div>
        </div>
    </div>
`;

export default {
    name: 'homedocs-modal-confirm-delete',
    template: template,
    methods: {
        onClose: function() {
            this.$emit("onClose");
        },
        onConfirm: function() {
            this.$emit("onConfirm");
        },
        onCancel: function() {
            this.$emit("onCancel");
        }
    }
}
