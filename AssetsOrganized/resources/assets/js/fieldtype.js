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
                return Object.assign(this.config, {
                        restrict: true,
                        folder: publish.extra.collection +'/' + publish.formData.fields.slug
                    });
            }
            else {
                return this.config;
            }

        }

    },

    ready: function() {
        var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];
        publish.$on('changesMade', this.updateAssetPaths );
    },

    methods: {
        updateAssetPaths: function() {
            var publish = this.$root.$children.filter(function(filter) { return filter.$options.name === 'publish'; })[0];

            if(publish.isNew) return;

            var regex = /^(.*(?=\/[^\/]+\/.*)\/)([^\/]+)(\/.*)$/gm;

            if(Array.isArray(this.data)) {
                var temp = this.data.map(function callback(val) {
                    return val.replace(regex, '$1' + publish.formData.fields.slug + '$3');
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
