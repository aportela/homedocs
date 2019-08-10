import { default as homedocsAPI } from './api.js';
import { default as validator } from './validator.js';
import { default as modalAPIError } from './modal-api-error.js';

const template = `
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Modal title</p>
                <button class="delete" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <!-- Content ... -->
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success">Save changes</button>
                <button class="button">Cancel</button>
            </footer>
        </div>
    </div>
`;

export default {
    name: 'homedocs-document',
    template: template,
    data: function () {
        return ({
            loading: false,
        });
    },
    created: function() {
        console.log(this.$route.params);
    }
}