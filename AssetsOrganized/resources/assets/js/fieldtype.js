Vue.component('assets_organized-fieldtype', {

    mixins: [Fieldtype],

    template: `
        <div>
            <assets-fieldtype :name="name" :data.sync="data" :config="fieldConfig"></assets-fieldtype>
        </div>
    `,

    computed: {

        fieldConfig() {
            var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];

            if(!publish.isNew) {
                this.config.originalFolder = this.config.folder;
                return Object.assign(this.config, {
                        restrict: true,
                        folder: this.config.originalFolder + publish.formData.fields.slug
                    });
            }
            else {
                return this.config;
            }

        }

    },

    ready: function() {
        var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];
        publish.$on('setFlashSuccess', this.updateAssetPaths );
    },

    methods: {
        updateAssetPaths: function(event = null, args=null) {
            if(event !== 'Saved') return;

            var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];

            if(publish.isNew) return;

            var regex = /^(.*(?=\/[^\/]+\/.*)\/)([^\/]+)(\/.*)$/gm;

            var self = this;
            if(Array.isArray(this.data)) {
                var temp = this.data.map(function callback(val) {
                    return val.replace(regex, AssetsOrganized.containers[self.config.container] + self.fieldConfig.folder + '$3');
                });

                if(!this.arrayIsEqual(temp, this.data)) {
                    this.data = temp;
                }
            }
        },
        arrayIsEqual: function(a1, a2) {
            return a1.length==a2.length && a1.every(function(v,i) { return v === a2[i]});
        }
    }

});
