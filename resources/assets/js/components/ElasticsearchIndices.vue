<template>
    <div>
        <article class="message">
            <div class="message-header">
                <p>Elasticsearch Indices</p>
                <button @click="toggleCreateIndexModal" class="button is-primary is-hidden-mobile">Create a new index</button>
            </div>
            <div class="message-body">
                <!-- No indices notice -->
                <p v-if="indices.length === 0">You have not created any indices.</p>
                <br>
                <!-- Display indices -->
                <div style="overflow-x:auto;">
                    <table v-if="indices.length > 0" class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th class="is-hidden-mobile">Updated at</th>
                            <th class="is-hidden-mobile">Created at</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="index in indices">
                            <td class="is-icon">
                                <i class="fa fa-database" aria-hidden="true"></i>
                            </td>
                            <td>{{ index.name }}</td>
                            <td class="is-hidden-mobile">{{ index.updated_at }}</td>
                            <td class="is-hidden-mobile">{{ index.created_at }}</td>
                            <td class="is-icon">
                                <i @click="toggleDeleteIndexModal(index)" class="fa fa-trash-o" aria-hidden="true"></i>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <button @click="toggleCreateIndexModal" class="button is-primary is-hidden-desktop is-hidden-tablet is-fullwidth">Create a new index</button>
            </div>
        </article>
        <!-- Create new Elasticsearch Index modal -->
        <div class="modal" :class="{'is-active': showCreateIndexModal}">
            <div @click="toggleCreateIndexModal" class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Create new Elasticsearch index</p>
                    <button @click="toggleCreateIndexModal" class="delete"></button>
                </header>
                <section class="modal-card-body">
                    <!-- Content ... -->
                    <p class="control">
                        <input v-model="form.name" class="input is-primary" type="text" placeholder="Elasticsearch index name">
                        <!-- Display any errors -->
                        <span v-if="form.errors.length > 0" v-for="error in form.errors" class="help is-danger">{{ error }}</span>
                    </p>
                </section>
                <footer class="modal-card-foot">
                    <a @click="store" class="button is-primary">Save</a>
                    <a @click="toggleCreateIndexModal" class="button">Cancel</a>
                </footer>
            </div>
        </div>

        <!-- Create new Elasticsearch Index modal -->
        <div class="modal" :class="{'is-active': showDeleteIndexModal}">
            <div @click="toggleCreateIndexModal" class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Delete Elasticsearch index</p>
                    <button @click="toggleDeleteIndexModal" class="delete"></button>
                </header>
                <section class="modal-card-body">
                    <!-- Content ... -->
                    <p>
                        Are you sure?
                    </p>
                </section>
                <footer class="modal-card-foot">
                    <a @click="deleteElasticIndex" class="button is-danger">Delete</a>
                    <a @click="toggleDeleteIndexModal" class="button">Cancel</a>
                </footer>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data () {
            return {
                indices: [],

                showCreateIndexModal: false,
                showDeleteIndexModal: false,

                form: {
                    name: '',
                    errors: []
                },

                index: {} // index to be deleted.
            }
        },
        mounted() {
            /**
             * Get the authenticated user's Elasticsearch indices when this component loads.
             */
            this.getIndices();
        },
        methods: {
            /**
             * Retrieve the authenticated user's Elasticsearch indices.
             */
            getIndices () {
                this.$http.get('/oauthshield/indices')
                    .then(response => {
                        this.indices = response.data;
                    });
            },
            /**
             * Store new Elasticsearch index.
             */
            store() {
                this.form.errors = [];

                this.$http.post('/oauthshield/indices', this.form)
                    .then(response => {
                        this.form.name = '';
                        this.form.errors = [];

                        this.toggleCreateIndexModal();
                        this.indices.push(response.data);

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
             * Show form/modal for creating a new Elasticsearch index.
             */
            toggleCreateIndexModal () {
                this.showCreateIndexModal = !this.showCreateIndexModal;
            },
            /**
             * Show confirmation modal for deleting an Elasticsearch index.
             */
            toggleDeleteIndexModal (index) {
                this.index = index;
                this.showDeleteIndexModal = !this.showDeleteIndexModal;
            },

            /**
             * Delete an Elasticsearch index.
             */
            deleteElasticIndex() {

                this.$http.delete('/oauthshield/indices/' + this.index.id)
                    .then(response => {
                        this.showDeleteIndexModal = false;
                        this.getIndices();
                    });
            }
        }
    }
</script>