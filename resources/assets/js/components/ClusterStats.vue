<template>
    <div v-if="statsLoaded" class="box stats">
        <div class="content">
            <h3>
                <span :class="iconClass">
                    <i class="fa fa-circle" aria-hidden="true"></i>
                </span>
                {{ stats.cluster_name }}
            </h3>
            <ul>
                <li>Version: {{ version }}</li>
                <li>Free space: {{ free }}</li>
                <li>Available space: {{ available }}</li>
                <li>Total space: {{ total }}</li>
            </ul>
        </div>
    </div>
    <div v-else>
        <div class="box stats">
            <div class="content">
                <h3>
                <span class="icon red animated infinite pulse">
                    <i class="fa fa-circle" aria-hidden="true"></i>
                </span>
                    {{ error }}
                </h3>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.getStats();
        },
        data () {
            return {
                stats: {},
                statsLoaded: false,
                error: ''
            }
        },
        computed: {
            iconClass () {
                if (this.stats.status == 'green') {
                    return 'icon green ';
                }
                if (this.stats.status == 'yellow') {
                    return 'icon yellow';
                }
                if (this.stats.status == 'red') {
                    return 'icon red animated infinite pulse';
                }
            },
            available () {
                return this.byteCalculator(this.stats.nodes['fs']['available_in_bytes'], 1);
            },
            free () {
                return this.byteCalculator(this.stats.nodes['fs']['free_in_bytes'], 1);
            },
            total () {
                return this.byteCalculator(this.stats.nodes['fs']['total_in_bytes'], 1);
            },
            version () {
                return this.stats.nodes['versions'][0];
            }
        },
        methods: {
            getStats () {
                this.$http.get('/elasticshield/stats')
                    .then(response => {
                        this.stats = response.data;
                        this.statsLoaded = true;
                    }).catch(response => {
                        this.error = 'Unable to contact Elasticsearch.'
                });
            },
            byteCalculator (bytes, decimals) {
                if (bytes == 0) return '0 Bytes';

                let k = 1000,
                    dm = decimals + 1 || 3,
                    sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                    i = Math.floor(Math.log(bytes) / Math.log(k));

                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }
        }
    }
</script>
