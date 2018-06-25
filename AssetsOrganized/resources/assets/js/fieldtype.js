Vue.component('assets_organized-fieldtype', {

    mixins: [ Fieldtype ],

    template: `
        <div>
            <assets-fieldtype v-if="isReady" :slug="getSlug" :assetContainer="getAssetContainer" :name="name" :data.sync="data.assets" :config="config"></assets-fieldtype>
        </div>
    `,

    data: function () {
        return {
            isReady: false
        }
    },

    computed: {
        getSlug: function() {
            var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];
            this.data.slug = publish.formData.fields.slug;
            return publish.formData.fields.slug;
        },
        getAssetContainer: function() {
            this.data.asset_container = this.config.container;
            return this.config.container;
        }
    },

    methods: {
        updateOldSlug: function(event) {
            this.updateDataAssetsPath();
            this.data.oldSlug = this.data.slug;
        },
        updateDataAssetsPath: function() {
            if(this.data.oldSlug !== this.data.slug) {

                var self = this;

                this.data.assets.forEach(function(element, index, array) {
                    var splitElem = element.split('/');
                    var oldSlug = splitElem[splitElem.length - 2];
                    splitElem[splitElem.length - 2] = self.data.slug;
                    var ret = splitElem.join('/');
                    array[index] = ret;
                });


            }
        }
    },


    ready: function() {
        var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];

        if(!this.data[0]) {
            this.data = {};
            this.data.assets = [];
        } else {
            var assetsData = this.data;
            this.data = {};
            this.data.assets = assetsData;
        }


        this.data.initialFolder = this.config.folder;
        this.data.slug = '';
        this.data.asset_container = this.config.container;

        this.data.oldSlug = this.data.slug;
        this.updateDataAssetsPath();

        if(this.data.slug !== null && this.data.slug.length > 0) {
            this.config.folder = this.config.folder + '/' + this.data.slug;
        }

        publish.$on('setFlashSuccess', this.updateOldSlug );

        this.isReady = true;
    }

});
