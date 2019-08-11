import { default as homedocsAPI } from './api.js';
import { default as modalAPIError } from './modal-api-error.js';
import { default as blockRecentDocuments } from './block-recent-documents.js';
import { default as blockTagCloud } from './block-tag-cloud.js';

const template = `
    <div>
        <div class="columns">
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-plus"></i></span><span>Add</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <router-link v-bind:to="{ name: 'appAddDocument' }">
                            <i class="fas fa-plus fa-10x"></i>
                            <h1 class="title">Click here for add new document</h1>
                        </router-link>
                    </div>
                </article>
            </div>
            <div class="column is-6">
                <article class="message">
                    <div class="message-header">
                        <p><span class="icon"><i class="fas fa-search-plus"></i></span><span>Search</span></p>
                    </div>
                    <div class="message-body has-text-centered">
                        <router-link v-bind:to="{ name: 'appAdvancedSearch' }">
                            <i class="fas fa-search-plus fa-10x"></i>
                            <h1 class="title">Click here for advanced search</h1>
                        </router-link>
                    </div>
                </article>
            </div>
        </div>
        <div class="columns">
            <div class="column is-6">
                <homedocs-block-recent-documents></homedocs-block-recent-documents>
            </div>
            <div class="column is-6">
                <homedocs-block-tag-cloud></homedocs-block-tag-cloud>
            </div>
        </div>
    </div>
`;

export default {
    name: 'homedocs-section-app-dashboard',
    template: template,
    components: {
        'homedocs-modal-api-error': modalAPIError,
        'homedocs-block-recent-documents': blockRecentDocuments,
        'homedocs-block-tag-cloud': blockTagCloud
    }
}