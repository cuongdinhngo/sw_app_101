(function(){var e={855:function(){let{Component:e}=Shopware;e.extend("bundle-create","bundle-detail",{data(){return{bundle:{name:"",discount:null,discountType:"",products:[]}}},methods:{getBundle(){this.bundle=this.repository.create(Shopware.Context.api)},onClickSave(){this.isLoading=!0,console.log(this.bundle),this.repository.save(this.bundle,Shopware.Context.api).then(()=>{this.isLoading=!1,this.$router.push({name:"bundle.products.detail",params:{id:this.bundle.id}})}).catch(e=>{this.createNotificationError({title:this.$tc("bundle.detail.errorTitle"),message:e}),this.isLoading=!1})}}})}},t={};function n(i){var s=t[i];if(void 0!==s)return s.exports;var o=t[i]={exports:{}};return e[i](o,o.exports,n),o.exports}n.p="bundles/bundleproducts/",window?.__sw__?.assetPath&&(n.p=window.__sw__.assetPath+"/bundles/bundleproducts/"),function(){"use strict";var e=JSON.parse('{"bundle-products":{"general":{"mainMenuItemGeneral":"Bundles","descriptionTextModule":"Manage products bundles"},"list":{"addButtonText":"Add Bundle","columnName":"Name","columnDiscountType":"Discount Type","columnDiscount":"Discount"},"detail":{"nameLabel":"Name","discountTypeLabel":"Discount Type","discountLabel":"Discount","assignProductsLabel":"Assigned Products","cancelButtonText":"Cancel","saveButtonText":"Save","errorTitle":"Error saving the bundle","absoluteText":"Absolute","percentageText":"Percentage"}}}'),t=JSON.parse('{"bundle":{"general":{"mainMenuItemGeneral":"Products Bundle","descriptionTextModule":"Manage products bundles"},"list":{"addButtonText":"Add Bundle","columnName":"Name","columnDiscountType":"Discount Type","columnDiscount":"Discount"},"detail":{"nameLabel":"Name","discountTypeLabel":"Discount Type","discountLabel":"Discount","assignProductsLabel":"Assigned Products","cancelButtonText":"Cancel","saveButtonText":"Save","errorTitle":"Error saving the bundle","absoluteText":"Absolute","percentageText":"Percentage"}}}');let{Component:i}=Shopware,{Criteria:s}=Shopware.Data;i.register("bundle-list",{template:'{% block bundle_list %}\n    <sw-page class="bundle-list">\n        {% block bundle_list_smart_bar_actions %}\n        <template #smart-bar-actions>\n            <sw-button variant="primary" :routerLink="{ name: \'bundle.products.create\' }">\n                {{ $tc(\'bundle.list.addButtonText\') }}\n            </sw-button>\n        </template>\n        {% endblock %}\n        <template #content>\n            {% block bundle_list_content %}\n            <sw-entity-listing\n                v-if="bundles"\n                :items="bundles"\n                :repository="repository"\n                :showSelection="false"\n                :columns="columns"\n                detailRoute="bundle.products.detail"\n            >\n            </sw-entity-listing>\n            {% endblock %}\n        </template>\n    </sw-page>\n{% endblock %}',inject:["repositoryFactory"],data(){return{repository:null,bundles:null}},metaInfo(){return{title:this.$createTitle()}},computed:{columns(){return this.getColumns()}},created(){this.createdComponent()},methods:{createdComponent(){this.repository=this.repositoryFactory.create("bundle"),this.repository.search(new s,Shopware.Context.api).then(e=>{console.log(e),this.bundles=e})},getColumns(){return[{property:"name",label:this.$tc("bundle.list.columnName"),routerLink:"bundle.products.detail",inlineEdit:"string",allowResize:!0,primary:!0},{property:"discount",label:this.$tc("bundle.list.columnDiscount"),inlineEdit:"number",allowResize:!0},{property:"discountType",label:this.$tc("bundle.list.columnDiscountType"),allowResize:!0}]}}}),n(855);let{Component:o,Mixin:a}=Shopware,{Criteria:l}=Shopware.Data;o.register("bundle-detail",{template:'{% block bundle_detail %}\n    <sw-page class="bundle-detail">\n        {% block bundle_list_smart_bar_actions %}\n            <template #smart-bar-actions>\n                <sw-button variant="primary" :routerLink="{ name: \'bundle.products.index\' }">\n                    {{ $tc(\'bundle.detail.cancelButtonText\') }}\n                </sw-button>\n\n                <sw-button-process\n                    :isLoading="isLoading"\n                    :processSuccess="processSuccess"\n                    variant="primary"\n                    @process-finish="saveFinish"\n                    @click="onClickSave"\n                >\n                    {{ $tc(\'bundle.detail.saveButtonText\') }}\n                </sw-button-process>\n            </template>\n        {% endblock %}\n        <template #content>\n            {% block bundle_detail_content %}\n                <sw-card-view>\n                    <sw-card v-if="bundle" :isLoading="isLoading">\n                        <sw-text-field v-model:value="bundle.name" :label="$tc(\'bundle.detail.nameLabel\')"></sw-text-field>\n                        <sw-number-field v-model:value="bundle.discount" :label="$tc(\'bundle.detail.discountLabel\')"></sw-number-field>\n                        <sw-radio-field\n                            v-model:value="bundle.discountType"\n                            :label="$tc(\'bundle.detail.discountTypeLabel\')"\n                            :options="options"\n                        >\n                        </sw-radio-field>\n                        <sw-entity-many-to-many-select\n                                :localMode="bundle.isNew()"\n                                :label="$t(\'bundle.detail.assignProductsLabel\')"\n                                :entityCollection="bundle.products"\n                                @update:entity-collection="bundle.products = $event">\n                        </sw-entity-many-to-many-select>\n                    </sw-card>\n                </sw-card-view>\n            {% endblock %}\n        </template>\n    </sw-page>\n{% endblock %}',inject:["repositoryFactory"],mixins:[a.getByName("notification")],metaInfo(){return{title:this.$createTitle()}},data(){return{bundle:null,isLoading:!1,processSuccess:!1,repository:null,productCriteria:this.createProductCriteria()}},computed:{options(){return[{value:"absolute",name:this.$tc("bundle.detail.absoluteText")},{value:"percentage",name:this.$tc("bundle.detail.percentageText")}]}},created(){this.createdComponent()},methods:{createdComponent(){this.repository=this.repositoryFactory.create("bundle"),this.getBundle()},getBundle(){this.repository.get(this.$route.params.id,Shopware.Context.api).then(e=>{this.bundle=e})},onClickSave(){this.isLoading=!0,this.repository.save(this.bundle,Shopware.Context.api).then(()=>{this.getBundle(),this.isLoading=!1,this.processSuccess=!0}).catch(e=>{this.createNotificationError({title:this.$tc("bundle.detail.errorTitle"),message:e}),this.isLoading=!1})},saveFinish(){this.processSuccess=!1},createProductCriteria(){let e=new l;return e.addFilter(l.equals("active",!0)),e}}}),Shopware.Module.register("bundle-products",{type:"core",name:"bundle-products",title:"bundle.general.mainMenuItemGeneral",description:"bundle.general.descriptionTextModule",color:"#ff3d58",icon:"regular-3d",snippets:{"de-DE":e,"en-GB":t},routes:{index:{component:"bundle-list",path:"index"},create:{component:"bundle-create",path:"create",meta:{parentPath:"bundle.products.index"}},detail:{component:"bundle-detail",path:"detail/:id",meta:{parentPath:"bundle.products.index"}}},navigation:[{label:"bundle.general.mainMenuItemGeneral",color:"#ff3d58",path:"bundle.products.index",icon:"regular-3d",position:100}]})}()})();