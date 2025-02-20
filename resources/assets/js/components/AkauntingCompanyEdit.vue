<template>
    <div class="">
        <div class="flex items-start">
            <table>
                <tbody>
                    <tr>
                        <th class="text-left p-0">
                            <span class="font-medium text-left text-sm p-0">{{ company.name }}</span>
                        </th>
                    </tr>
                    <tr v-if="company.address">
                        <th class="font-normal text-sm text-left p-0">
                            {{ company.address }}
                        </th>
                    </tr>
                    <tr v-if="company.location">
                        <th class="font-normal text-sm text-left p-0">
                            {{ company.location }}
                        </th>
                    </tr>
                    <tr v-if="company.tax_number">
                        <th class="font-normal text-sm text-left p-0">
                            {{ taxNumberText }}: {{ company.tax_number }}
                        </th>
                    </tr>
                    <tr v-if="company.phone">
                        <th class="font-normal text-sm text-left p-0">
                            {{ company.phone }}
                        </th>
                    </tr>
                    <tr>
                        <th class="font-normal text-sm text-left p-0">
                            {{ company.email }}
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="absolute right-0 top-0 group">
            <div class="w-6 h-7 flex items-center justify-center rounded-lg p-0 group-hover:bg-gray-100">
                <span class="material-icons-outlined text-lg opacity-70 group-hover:text-gray-500 cursor-pointer" @click="onEditCompany">edit</span>
            </div>
        </div>

        <component v-bind:is="company_html" @submit="onSubmit" @cancel="onCancel"></component>
    </div>
</template>

<script>
import Vue from 'vue';

import { Select, Option, OptionGroup, ColorPicker } from 'element-ui';

import AkauntingModalAddNew from './AkauntingModalAddNew.vue';
import AkauntingModal from './AkauntingModal.vue';
import AkauntingMoney from './AkauntingMoney.vue';
import AkauntingRadioGroup from './AkauntingRadioGroup.vue';
import AkauntingSelect from './AkauntingSelect.vue';
import AkauntingDate from './AkauntingDate.vue';

import Form from './../plugins/form';

export default {
    name: 'akaunting-company-edit',

    components: {
        [Select.name]: Select,
        [Option.name]: Option,
        [OptionGroup.name]: OptionGroup,
        [ColorPicker.name]: ColorPicker,
        AkauntingModalAddNew,
        AkauntingModal,
        AkauntingMoney,
        AkauntingRadioGroup,
        AkauntingSelect,
        AkauntingDate,
    },

    props: {
        taxNumberText: {
            type: String,
            default: 'Tax Number',
            description: 'Input placeholder'
        },
        companyId: {
            type: String,
            default: '',
            description: 'Contact search route'
        },
        company: {
            type: Object,
            default: {},
            description: 'Company object'
        },
        companyForm: {
            type: Object,
            default: function () {
                return {
                    text: 'Add New Item',
                    show: false,
                    buttons: {}
                };
            },
            description: "Selectbox Add New Item Feature"
        },
    },

    data() {
        return {
            form: {},
            company_form: {
                text: this.companyForm.text,
                show: false,
                path: url + '/modals/companies/' + this.companyId + '/edit',
                buttons: this.companyForm.buttons,
            },
            company_html: '',
        };
    },

    methods: {
        // Edit Company information
        onEditCompany() {
            let company_form = this.company_form;

            window.axios.get(company_form.path)
            .then(response => {
                company_form.show = true;
                company_form.html = response.data.html;

                this.company_html = Vue.component('add-new-component', function (resolve, reject) {
                    resolve({
                        template: '<div><akaunting-modal-add-new :show="company_form.show" @submit="onSubmit" @cancel="onCancel" :buttons="company_form.buttons" :title="company_form.text" :is_component=true :message="company_form.html"></akaunting-modal-add-new></div>',

                        components: {
                            AkauntingModalAddNew,
                        },

                        data: function () {
                            return {
                                company_form: company_form,
                            }
                        },

                        methods: {
                        onSubmit(event) {
                            this.$emit('submit', event);
                        },

                        onCancel(event) {
                            this.$emit('cancel', event);
                        }
                        }
                    })
                }, this);
            })
            .catch(e => {
                console.log(e);
            })
            .finally(function () {
                // always executed
            });
        },

        onSubmit(event) {
            this.form = event;

            this.loading = true;

            let data = this.form.data();

            FormData.prototype.appendRecursive = function(data, wrapper = null) {
                for(var name in data) {
                    if (wrapper) {
                        if ((typeof data[name] == 'object' || data[name].constructor === Array) && ((data[name] instanceof File != true ) && (data[name] instanceof Blob != true))) {
                            this.appendRecursive(data[name], wrapper + '[' + name + ']');
                        } else {
                            this.append(wrapper + '[' + name + ']', data[name]);
                        }
                    } else {
                        if ((typeof data[name] == 'object' || data[name].constructor === Array) && ((data[name] instanceof File != true ) && (data[name] instanceof Blob != true))) {
                            this.appendRecursive(data[name], name);
                        } else {
                            this.append(name, data[name]);
                        }
                    }
                }
            };

            let form_data = new FormData();
            form_data.appendRecursive(data);

            window.axios({
                method: this.form.method,
                url: this.form.action,
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                this.form.loading = false;

                if (response.data.success) {
                    let data = this.form.data();

                    this.company.name = data.name;
                    this.company.email = data.email;
                    this.company.tax_number = data.tax_number;
                    this.company.phone = data.phone;
                    this.company.address = data.address;

                    this.company_form.show = false;

                    this.company_form.html = '';
                    this.company_html = null;

                    this.$emit('changed', data);

                    let documentClasses = document.body.classList;

                    documentClasses.remove('overflow-y-hidden', 'overflow-overlay');
                }
            })
            .catch(error => {
                this.form.loading = false;
                console.log(error);
                this.company_html = error.message;
            });
        },

        onCancel() {
            this.company_form.show = false;
            this.company_form.html = null;
            this.company_html = null;

            let documentClasses = document.body.classList;

            documentClasses.remove('overflow-y-hidden', 'overflow-overlay');
        },
    },
};
</script>
