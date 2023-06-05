//import { default as modalAPIError } from '../vue-components/modal-api-error.js';
import { default as blockRecentDocuments } from '../vue-components/block-recent-documents.js';
import { default as blockTagCloud } from '../vue-components/block-tag-cloud.js';

const template = `
        <div>
            <div class="columns">
                <div class="column is-6">
                    <article class="message">
                        <div class="message-header">
                            <p><span class="icon"><i class="fas fa-plus"></i></span><span>{{ $t("pages.dashBoard.labels.add") }}</span></p>
                        </div>
                        <div class="message-body has-text-centered">
                            <router-link v-bind:to="{ name: 'appAddDocument' }">
                                <i class="fas fa-plus fa-10x"></i>
                                <h1 class="title">{{ $t("pages.dashBoard.labels.addHint") }}</h1>
                            </router-link>
                        </div>
                    </article>
                </div>
                <div class="column is-6">
                    <article class="message">
                        <div class="message-header">
                            <p><span class="icon"><i class="fas fa-search-plus"></i></span><span>{{ $t("pages.dashBoard.labels.search") }}</span></p>
                        </div>
                        <div class="message-body has-text-centered">
                            <router-link v-bind:to="{ name: 'appAdvancedSearch' }">
                                <i class="fas fa-search-plus fa-10x"></i>
                                <h1 class="title">{{ $t("pages.dashBoard.labels.searchHint") }}</h1>
                            </router-link>
                        </div>
                    </article>
                </div>
            </div>
            <div class="columns">
                <div class="column is-6">
                    <homedocs-block-recent-documents v-on:showAPIError="raiseAPIError($event)"></homedocs-block-recent-documents>
                </div>
                <div class="column is-6">
                    <homedocs-block-tag-cloud v-on:showAPIError="raiseAPIError($event)"></homedocs-block-tag-cloud>
                </div>
            </div>
        </div>
`;

export default {
    name: 'homedocs-section-app-dashboard',
    template: template,
    components: {
        //'homedocs-modal-api-error': modalAPIError,
        'homedocs-block-recent-documents': blockRecentDocuments,
        'homedocs-block-tag-cloud': blockTagCloud
    }, methods: {
        raiseAPIError: function (error) {
            //this.$emit("showAPIError", error);
        }
    }
}