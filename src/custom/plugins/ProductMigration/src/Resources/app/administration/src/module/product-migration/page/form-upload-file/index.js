import template from './form-upload-file.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('form-upload-file', {
    template,
    inject:[
        'repositoryFactory',
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
    data() {
        return {
            file: null,
            isLoading: false,
            uploadSuccess: false,
            uploadError: false,
            processingState: false,
            responseMessage: null
        };
    },
    computed: {
    },
    created() {
    },
    methods: {
        onFileChange(event) {
            this.file = event;
            console.log('File was added ...');
            console.log(Shopware.Service('loginService').getToken());
            console.log('File selected:', this.file);
            console.log('File name:', this.file?.name);
            console.log('File size:', this.file?.size);
        },

        async uploadFile() {
            console.log('uploadFile ...');

            if (!this.file) {
                this.uploadError = true;
                this.createNotificationError({
                    title: this.$tc('product-migration.detail.errorTitle'),
                    message: this.$tc('product-migration.detail.errorMessage'),
                });
                return;
            }

            this.isLoading = true;
            this.uploadError = false;
            this.uploadSuccess = false;

            const formData = new FormData();
            formData.append('file', this.file);

            // Log FormData contents (note: FormData logging can be tricky)
            // for (let [key, value] of formData.entries()) {
            //     console.log(`${key}:`, value);
            // }

            try {
                console.log('API calling ....');
                this.processingState = true;
                const httpClient = Shopware.Application.getContainer('init').httpClient;
                const response = await httpClient.post(
                    'product-migration/upload',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                            'Authorization': `Bearer ${Shopware.Service('loginService').getToken()}`
                        },
                    }
                );

                console.log('Got Response', response);
                console.log(response.data)
                this.processingState = false;

                if (response.status === 200 && !response.data.includes('error')) {
                    console.log(response);
                    console.log(response.data)
                    this.uploadSuccess = true;
                    this.responseMessage = response.data.message;
                } else {
                    console.log(response);
                    console.log(response.data);
                    this.uploadError = true;
                    this.responseMessage = response.data.message;
                }
            } catch (error) {
                this.uploadError = true;
                console.error('File upload failed:', error);
            } finally {
                this.isLoading = false;
            }
        }
    }
});
