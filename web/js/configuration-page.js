Vue.component('list', {
    props: {
        url: { 
            type: String,
            required: true
        },
        update_url: {
            type: String,
            required: true
        }
    },
    data: function () {
        return {
            actions: null
        }
    },
    created: function () {
        this.fetchData();
    },
    methods: {
        fetchData: function () {
            var self = this;
            var req = new XMLHttpRequest();
            req.open('GET', this.url, true);
            req.setRequestHeader('Content-Type', 'application/json');

            req.onreadystatechange = function () {
                if (req.status >= 200 && req.status < 400 && req.readyState == 4) {
                    self.actions = JSON.parse(req.responseText);
                }
            }

            req.send();
        },
        updateSettings: function () {
            var self = this;
            var req = new XMLHttpRequest();
            req.open('POST', this.update_url, true);
            req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            var formData = '';
            self.actions.map(function(action) {
                formData+= action.id+"="+action.enabled+"&";
            });

            req.send(formData);
        }
    },
    template: '#list-template',
});

Vue.component('action', {
    props: ['action'],
    filters: {},
    methods: {},
    computed: {},
    template: '#actionForm-template',
});

var ConfigurationPage = new Vue({
    el: '#app',
    data: {},
    props: {},
    filters: {},
    methods: {},
});
