<template>
    <div>
        <article class="message">
            <div class="message-header">
                <p>Personal Access Tokens</p>
                <button @click="toggleShowCreateAccessTokenModal" v-if="tokens.length > 0" class="button is-primary is-hidden-mobile">Create a new token</button>
                <button @click="createInitialTokens" v-if="tokens.length === 0" class="button is-primary is-hidden-mobile">Get started</button>
            </div>
            <div class="message-body">
                <!-- No indices notice -->
                <p v-if="tokens.length === 0">
                    You have not created any tokens.
                </p>
                <!-- Display tokens -->
                <div style="overflow-x:auto;">
                    <table v-if="tokens.length > 0" class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Scopes</th>
                            <th class="is-hidden-mobile">Created at</th>
                            <th class="is-hidden-mobile">Expires at</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="token in tokens">
                            <td class="is-icon">
                                <i class="fa fa-key" aria-hidden="true"></i>

                            </td>
                            <td>{{ token.name }}</td>
                            <td><code v-for="scope in token.scopes">{{ scope }}</code></td>
                            <td class="is-hidden-mobile"> {{ token.created_at }}</td>
                            <td class="is-hidden-mobile">{{ token.expires_at }}</td>
                            <td class="is-icon">
                                <i @click="revoke(token)" class="fa fa-trash-o" aria-hidden="true"></i>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <button @click="createInitialTokens" v-if="tokens.length === 0" class="button is-primary is-hidden-tablet is-hidden-desktop is-fullwidth">Get started</button>
                <button @click="toggleShowCreateAccessTokenModal" v-if="tokens.length > 0" class="button is-primary is-hidden-tablet is-hidden-desktop is-fullwidth">Create a new token</button>
            </div>
        </article>
        <!-- Show Access Tokens modal -->
        <div class="modal" :class="{'is-active': showAccessTokensModal}">
            <div @click="toggleShowAccessTokensModal" class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Personal Access Token(s)</p>
                    <button @click="toggleShowAccessTokensModal" class="delete"></button>
                </header>
                <section class="modal-card-body">
                    <!-- Content ... -->
                    <p>This is the only time that these token(s) will be shown so please save a copy.</p>
                    <br>
                    <div v-if="accessTokens.length > 0" v-for="token in accessTokens">
                        <pre><code>{{ token.name}}={{ token.token }}</code></pre>
                        <br>
                    </div>
                </section>
                <footer class="modal-card-foot">
                    <a @click="toggleShowAccessTokensModal" class="button">Done</a>
                </footer>
            </div>
        </div>
        <!-- Show create new Access Token Form -->
        <div class="modal" :class="{'is-active': showCreateAccessTokenModal}">
            <div @click="toggleShowCreateAccessTokenModal" class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Create new token</p>
                    <button @click="toggleShowCreateAccessTokenModal" class="delete"></button>
                </header>
                <section class="modal-card-body">
                    <!-- Token name input -->
                    <p class="control">
                        <input v-model="form.name" class="input" type="text" placeholder="Token name">
                        <!-- Display any errors -->
                        <span v-if="form.errors.length > 0" v-for="error in form.errors" class="help is-danger">{{ error }}</span>
                    </p>

                    <!-- Scopes -->
                    <label v-for="scope in scopes" class="label">
                        <p class="control">
                            <input type="checkbox"
                                   @click="toggleScope(scope.id)"
                                   :checked="scopeIsAssigned(scope.id)"
                            >
                            {{ scope.id }}
                        </p>
                    </label>
                </section>
                <footer class="modal-card-foot">
                    <a @click="store" class="button is-primary">Save</a>
                    <a @click="toggleShowCreateAccessTokenModal" class="button">Close</a>
                </footer>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        /**
         * The component's data.
         */
        data() {
            return {
                accessTokens: [],

                tokens: [],
                scopes: [],

                form: {
                    name: '',
                    scopes: [],
                    errors: []
                },

                showAccessTokensModal: false,
                showCreateAccessTokenModal: false
            };
        },

        /**
         * Get the users tokens.
         * Get the scopes defined for the Oauth server.
         */
        mounted() {
            this.getTokens();
            this.getScopes();
        },

        methods: {
            /**
             * Get all of the personal access tokens for the user.
             */
            getTokens() {
                this.$http.get('/oauth/personal-access-tokens')
                    .then(response => {
                        this.tokens = response.data;
                    });
            },

            /**
             * Get all of the available scopes.
             */
            getScopes() {
                this.$http.get('/oauth/scopes')
                    .then(response => {
                        this.scopes = response.data;
                    });
            },
            /**
             * Create a new personal access token using the API.
             */
            store() {
                this.accessTokens = [];
                this.form.errors = [];

                this.$http.post('/oauth/personal-access-tokens', this.form)
                    .then(response => {
                        this.tokens.push(response.data.token);
                        this.accessTokens.push({
                            name: response.data.token.name,
                            token: response.data.accessToken
                        });
                        this.form.name = '';
                        this.form.scopes = [];
                        this.form.errors = [];

                        this.showCreateAccessTokenModal = false;
                        this.showAccessTokensModal = true;
                    })
                    .catch(response => {
                        if (typeof response.data === 'object') {
                            this.form.errors = _.flatten(_.toArray(response.data));
                        } else {
                            this.form.errors = ['Something went wrong. Please try again.'];
                        }
                    });
            },
            /**
             * Create a RW token.
             */
            createRwToken () {
                this.form.name = 'ELASTIC_API_RW_USER';
                this.form.scopes = ['get', 'post', 'put', 'delete'];
                this.store();
            },
            /**
             * Create a Read only token.
             */
            createRoToken () {
                this.form.name = 'ELASTIC_API_RO_USER';
                this.form.scopes = ['get'];
                this.store();
            },
            /**
             * Create initial tokens.
             */
            createInitialTokens() {
                this.createRwToken();
                this.createRoToken();
            },
            /**
             * Toggle the given scope in the list of assigned scopes.
             */
            toggleScope(scope) {
                if (this.scopeIsAssigned(scope)) {
                    this.form.scopes = _.reject(this.form.scopes, s => s == scope);
                } else {
                    this.form.scopes.push(scope);
                }
            },
            /**
             * Toggle show access tokens modal.
             */
            toggleShowAccessTokensModal () {
                this.showAccessTokensModal = !this.showAccessTokensModal;
            },
            /**
             * Toggle create new token modal/form.
             */
            toggleShowCreateAccessTokenModal () {
                this.showCreateAccessTokenModal = !this.showCreateAccessTokenModal
            },

            /**
             * Determine if the given scope has been assigned to the token.
             */
            scopeIsAssigned(scope) {
                return _.indexOf(this.form.scopes, scope) >= 0;
            },
            /**
             * Revoke the given token.
             */
            revoke(token) {
                this.$http.delete('/oauth/personal-access-tokens/' + token.id)
                    .then(response => {
                        this.getTokens();
                    });
            }
        }
    }
</script>
