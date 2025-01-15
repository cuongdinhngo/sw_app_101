(function(){var e={572:function(){let{Component:e}=Shopware;console.log("swag-example-create ...."),e.extend("swag-example-create","swag-example-detail",{methods:{getShop(){console.log("Shop initialized:",this.shop),this.shop||(console.log("[swag-example-create] getShop ...."),this.shop=this.repository.create(Shopware.Context.api))},onClickSave(){this.isLoading=!0,this.repository.save(this.shop,Shopware.Context.api).then(()=>{this.isLoading=!1,this.$router.push({name:"swag.example.detail",params:{id:this.shop.id}})}).catch(e=>{this.isLoading=!1,this.createNotificationError({title:this.$tc("swag-example.detail.errorTitle"),message:e})})}}})}},t={};function s(a){var n=t[a];if(void 0!==n)return n.exports;var o=t[a]={exports:{}};return e[a](o,o.exports,s),o.exports}s.p="bundles/swagshopfinder/",window?.__sw__?.assetPath&&(s.p=window.__sw__.assetPath+"/bundles/swagshopfinder/"),function(){"use strict";let{Component:e}=Shopware,{Criteria:t}=Shopware.Data;e.register("swag-example-list",{template:'{% block swag_bundle_list %}\n    <sw-page class="swag-example-list">\n        {{ dump($tc) }}\n        {% block swag_example_list_smart_bar_action %}\n            <template #smart-bar-actions>\n                <sw-button\n                    variant="primary"\n                    :routerLink="{name: \'swag.example.create\'}"\n                >\n                    {{ $tc(\'swag-example.list.addButtonText\') }}\n                </sw-button>\n            </template>\n        {% endblock %}\n        <template #content>\n            {% block swag_example_list_content %}\n                <sw-entity-listing\n                    v-if="shops"\n                    :items="shops"\n                    :repository="repository"\n                    :showSelection="false"\n                    :columns="columns"\n                    detailRoute="swag.example.detail"\n                >\n                </sw-entity-listing>\n                <p v-else>No shops found</p>\n            {% endblock %}\n        </template>\n    </sw-page>\n{% endblock %}\n',inject:["repositoryFactory"],data(){return{repository:null,shops:[]}},metaInfo(){return{title:this.$createTitle()}},computed:{columns(){return this.getColumns()}},created(){this.createdComponent()},methods:{createdComponent(){this.repository=this.repositoryFactory.create("swag_shop_finder"),this.repository.search(new t,Shopware.Context.api).then(e=>{console.log("search ..."),console.log(e),this.shops=e}).catch(e=>{console.error("Error fetching shops:",e)})},getColumns(){return[{property:"name",label:this.$tc("swag-example.list.columnName"),routerLink:"swag.example.detail",inlineEdit:"string",allowResize:!0,primary:!0},{property:"street",label:this.$tc("swag-example.list.columnStreet"),inlineEdit:"string",allowResize:!0},{property:"city",label:this.$tc("swag-example.list.columnCity"),allowResize:!0}]}}});let{Component:a,Mixin:n}=Shopware,{Criteria:o}=Shopware.Data;a.register("swag-example-detail",{template:'{% block swag_example_detail %}\n    <sw-page class="swag-example-detail">\n        <template #smart-bar-actions>\n            <sw-button :routerLink="{name: \'swag.example.index\'}">\n                {{ $tc(\'swag-example.detail.cancelButtonText\') }}\n            </sw-button>\n\n            <sw-button-process\n                :isLoading="isLoading"\n                :processSuccess="processSuccess"\n                variant="primary"\n                @process-finish="saveFinish"\n                @click="onClickSave">\n                {{ $tc(\'swag-example.detail.saveButtonText\') }}\n            </sw-button-process>\n        </template>\n        <template #content>\n            <sw-card-view>\n                <sw-card v-if="shop" :isLoading="isLoading">\n                    <sw-text-field\n                        v-model="shop.name"\n                        :label="$tc(\'swag-example.detail.nameLabel\')"\n                    >\n                    </sw-text-field>\n                    <p>Debug: {{ shop.name }}</p>\n\n                    <sw-text-field\n                        v-model="shop.street"\n                        :label="$tc(\'swag-example.detail.streetLabel\')"\n                    >\n                    </sw-text-field>\n\n                    <sw-text-field\n                        v-model="shop.postCode"\n                        :label="$tc(\'swag-example.detail.postCodeLabel\')"\n                    >\n                    </sw-text-field>\n\n                    <sw-text-field\n                        v-model="shop.city"\n                        :label="$tc(\'swag-example.detail.cityLabel\')"\n                    >\n                    </sw-text-field>\n\n                    <sw-radio-field\n                        :label="$tc(\'swag-example.detail.statusLabel\')"\n                        v-model="shop.active"\n                        :options="options"\n                    >\n                    </sw-radio-field>\n\n{#                    <sw-entity-many-to-many-select#}\n{#                        v-if="shop.countryId"#}\n{#                        v-model="shop.countryId"#}\n{#                        :localMode="shop.isNew()"#}\n{#                        :label="$tc(\'swag-example.detail.assignCountryLabel\')"#}\n{#                    >#}\n{#                    </sw-entity-many-to-many-select>#}\n\n{#                    <sw-entity-single-select#}\n{#                        v-model="shop.countryId"#}\n{#                        :entity="countryRepository"#}\n{#                        :label="$tc(\'swag-example.detail.assignCountryLabel\')"#}\n{#                        :placeholder="$tc(\'swag-example.detail.selectCountryPlaceholder\')"#}\n{#                    >#}\n{#                    </sw-entity-single-select>#}\n                </sw-card>\n            </sw-card-view>\n        </template>\n    </sw-page>\n{% endblock %}\n',inject:["repositoryFactory"],mixins:[n.getByName("notification")],metaInfo(){return{title:this.$createTitle()}},data(){return{shop:null,isLoading:!1,processSuccess:!1,repository:null,countryRepository:null,countries:null}},computed:{options(){return[{value:"1",name:this.$tc("swag-example.detail.activeText")},{value:"0",name:this.$tc("swag-example.detail.disabledText")}]}},created(){this.createdComponent()},methods:{createdComponent(){this.repository=this.repositoryFactory.create("swag_shop_finder"),console.log("created repository ..."),console.log(this.repository),this.getShop(),this.countryRepository=this.repositoryFactory.create("country"),this.countryRepository.search(new o,Shopware.Context.api).then(e=>{this.countries=e})},getShop(){console.log(Shopware.Context.api),console.log("route ...."),console.log(this.$route),console.log(this.$route.params),this.repository.get(this.$route.params.id,Shopware.Context.api).then(e=>{console.log("get shop by id"),console.log(e),this.shop=e})},onClickSave(){this.isLoading=!0,this.repository.save(this.shop,Shopware.Context.api).then(()=>{this.getShop(),this.isLoading=!1,this.processSuccess=!0}).catch(e=>{this.isLoading=!1,this.createNotificationError({title:this.$tc("swag-example.detail.errorTitle"),message:e})})},saveFinish(){this.processSuccess=!1}}}),s(572);var i=JSON.parse('{"swag-example":{"general":{"mainMenuItemGeneral":"Shop Finder Plugin","descriptionTextModule":"[de-DE] Shop Finder Plugin"},"list":{"addButtonText":"[DE] Add bundle","columnName":"[DE] Name","columnDiscountType":"[DE] Discount Type","columnDiscount":"[DE] Discount"}}}'),l=JSON.parse('{"swag-example":{"general":{"mainMenuItemGeneral":"Shop Finder Plugin","descriptionTextModule":"[en-GB] Shop Finder Plugin"},"list":{"addButtonText":"Add bundle","columnName":"Name","columnStreet":"Street","columnCity":"City"},"detail":{"nameLabel":"Name","streetLabel":"Street","postCodeLabel":"Post code","cityLabel":"City","statusLabel":"Status","saveButtonText":"Save","errorTitle":"Error saving the bundle","activeText":"Active","disabledText":"Disabled","assignCountryLabel":"Assign Country","cancelButtonText":"Cancel","selectCountryPlaceholder":"Please select country"}}}');Shopware.Module.register("swag-example",{type:"core",name:"Shop Finder",title:"swag-example.general.mainMenuItemGeneral",description:"swag-example.general.descriptionTextModule",color:"#ff3d58",snippets:{"de-DE":i,"en-GB":l},routes:{index:{components:{default:"swag-example-list"},path:"index"},detail:{components:{default:"swag-example-detail"},path:"detail/:id",meta:{parentPath:"swag.example.index"}},create:{components:{default:"swag-example-create"},path:"create",meta:{parentPath:"swag.example.index"}}},navigation:[{label:"swag-example.general.mainMenuItemGeneral",color:"#ff3d58",path:"swag.example.index",position:100}]})}()})();