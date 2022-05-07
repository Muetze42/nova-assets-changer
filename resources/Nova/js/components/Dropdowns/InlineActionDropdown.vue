<template>
    <div v-if="hasDropdownItems">
        <Dropdown v-if="actions.length > 0 || resource.authorizedToReplicate || (currentUser.canImpersonate && resource.authorizedToImpersonate)">
            <span class="sr-only">{{ __('Resource Row Dropdown') }}</span>
            <DropdownTrigger
                :dusk="`${resource.id.value}-control-selector`"
                :show-arrow="false"
            >
                <span class="py-0.5 px-2 rounded">
                    <Icon :solid="true" type="dots-horizontal" />
                </span>
            </DropdownTrigger>

            <template #menu>
                <DropdownMenu width="190" class="px-1">
                    <ScrollWrap
                        :height="250"
                        class="divide-y divide-gray-100 dark:divide-gray-800 divide-solid"
                    >
                        <div class="py-1" v-if="canModifyResource">
                            <!-- Preview Resource Link -->
<!--                            <DropdownMenuItem-->
<!--                                v-if="resource.authorizedToView"-->
<!--                                :data-testid="`${resource.id.value}-preview-button`"-->
<!--                                :dusk="`${resource.id.value}-preview-button`"-->
<!--                                as="button"-->
<!--                                @click.prevent="openPreviewModal"-->
<!--                                :title="__('Preview')"-->
<!--                            >-->
<!--                                {{ __('Preview') }}-->
<!--                            </DropdownMenuItem>-->

                            <!-- Replicate Resource Link -->
                            <DropdownMenuItem
                                v-if="resource.authorizedToReplicate"
                                :dusk="`${resource.id.value}-replicate-button`"
                                :href="
                  $url(
                    `/resources/${resourceName}/${resource.id.value}/replicate`,
                    { viaResource, viaResourceId, viaRelationship }
                  )
                "
                                :title="__('Replicate')"
                            >
                                {{ __('Replicate') }}
                            </DropdownMenuItem>

                            <!-- Impersonate Resource Button -->
                            <DropdownMenuItem
                                as="button"
                                v-if="canBeImpersonated"
                                :dusk="`${resource.id.value}-impersonate-button`"
                                @click.prevent="
                  startImpersonating({
                    resource: resourceName,
                    resourceId: resource.id.value,
                  })
                "
                                :title="__('Impersonate')"
                            >
                                {{ __('Impersonate') }}
                            </DropdownMenuItem>
                        </div>
                        <div
                            v-if="actions.length > 0"
                            :dusk="`${resource.id.value}-inline-actions`"
                            class="py-1"
                        >
                            <!-- User Actions -->
                            <DropdownMenuItem
                                as="button"
                                v-for="action in actions"
                                :key="action.uriKey"
                                :dusk="`${resource.id.value}-inline-action-${action.uriKey}`"
                                @click="() => handleActionClick(action.uriKey)"
                                :title="action.name"
                            >
                                {{ action.name }}
                            </DropdownMenuItem>
                        </div>
                    </ScrollWrap>
                </DropdownMenu>
            </template>
        </Dropdown>

        <!-- Action Confirmation Modal -->
        <component
            v-if="confirmActionModalOpened"
            :show="confirmActionModalOpened"
            :is="selectedAction.component"
            :working="working"
            :selected-resources="selectedResources"
            :resource-name="resourceName"
            :action="selectedAction"
            :endpoint="endpoint"
            :errors="errors"
            @confirm="executeAction"
            @close="closeConfirmationModal"
        />

        <!-- Action Response Modal -->
        <component
            v-if="selectedAction"
            :is="actionResponseData.modal"
            @close="closeActionResponseModal"
            :show="showActionResponseModal"
            :data="actionResponseData"
        />

        <PreviewResourceModal
            v-if="previewModalOpen"
            :resource-id="resource.id.value"
            :resource-name="resourceName"
            :show="previewModalOpen"
            @close="closePreviewModal"
            @confirm="closePreviewModal"
        />
    </div>
</template>

<script>
import HandlesActions from '@/mixins/HandlesActions'
import { mapGetters, mapActions } from 'vuex'

export default {
    mixins: [HandlesActions],

    props: {
        resource: { type: Object },
        resourceName: String,
        actions: { type: Array },
        viaManyToMany: { type: Boolean },
        viaResource: { type: String, default: '' },
        viaResourceId: { type: String, default: '' },
        viaRelationship: { type: String, default: '' },
    },

    data: () => ({
        showActionResponseModal: false,
        actionResponseData: {},
        previewModalOpen: false,
    }),

    methods: {
        ...mapActions(['startImpersonating']),

        openPreviewModal() {
            this.previewModalOpen = true
        },

        closePreviewModal() {
            this.previewModalOpen = false
        },
    },

    computed: {
        ...mapGetters(['currentUser']),

        currentSearch() {
            return ''
        },

        encodedFilters() {
            return ''
        },

        currentTrashed() {
            return ''
        },

        hasDropdownItems() {
            return this.actions.length > 0 || this.canModifyResource
        },

        canModifyResource() {
            return (
                this.resource.authorizedToView ||
                this.resource.authorizedToReplicate ||
                this.canBeImpersonated
            )
        },

        canBeImpersonated() {
            return (
                this.currentUser.canImpersonate && this.resource.authorizedToImpersonate
            )
        },

        selectedResources() {
            return [this.resource.id.value]
        },
    },
}
</script>
