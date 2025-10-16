<template>
    <div class="container">
        <h2 class="mt-4">Add Listing</h2>
        <div class="card card-bordered">
            <div class="card-inner">
                <form @submit.prevent="saveListing">
                    <div class="row">
                        <!-- Property Name -->
                        <div class="col-md-3 mb-3">
                            <label for="name">Property Name *</label>
                            <input
                                type="text"
                                id="name"
                                class="form-control"
                                v-model="form.name"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('name')"
                                v-html="form.errors.get('name')"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="text-white btn btn-primary"
                        :disabled="isLoading"
                    >
                        <span
                            v-if="isLoading"
                            class="spinner-border spinner-border-sm"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        <span v-if="!isLoading">Save Listing</span>
                        <span v-else>Saving...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isLoading: false,
            listingId: "",
            form: new form({
                channel_id: "",
                name: "",
                property_type_group: "",
                property_type_category: "",
                room_type_category: "",
                bedrooms: 0,
                bathrooms: 0,
                beds: 0,
                // "amenities": {
                //     "additionalProp1": {
                //         "instruction": "Currently not implemented",
                //         "is_present": true,
                //         "metadata": {}
                //     }
                // }
                listing_views: [],
                check_in_option: {
                    category: "",
                    instruction: "",
                },
                // "permit_or_tax_id": "",
                apt: "",
                street: "",
                city: "",
                state: "",
                zipcode: "",
                country_code: "",
                // "lat": 0,
                // "lng": 0
                directions: "",
                person_capacity: 0,
                // "requested_approval_status_category": "new",
                total_inventory_count: 0,
                property_external_id: "",
                has_availability: false,
                deactivation_reason: "",
                deactivation_details: "",
                display_exact_location_to_guest: true,
                house_manual: "",
                wifi_network: null,
                wifi_password: null,
                listing_nickname: "",
                // "check_out_tasks": {
                //     "additionalProp1": {
                //         "task_detail": "test",
                //         "is_present": true
                //     }
                // },
                // "reactivate_listing": true,
                listing_fields_to_clear: ["PERMIT_OR_TAX_ID"],
            }),
        };
    },
    methods: {
        saveListing() {
            this.form.channel_id = this.$route?.query?.channel_id;
            if (!this.form.channel_id) {
                this.$showErrorToast("Channel ID not found!");
                return false;
            }
            this.isLoading = true;

            this.form
                .post("/api/airbnb/listings")
                .then((response) => {
                    if (response?.data?.id) {
                        this.$router.push("/admin/listing/edit/"+response?.data?.id)
                    }
                    this.isLoading = false;
                    this.$showSuccessToast("Listing Created!");
                    // this.$router.push({ name: "Properties" });
                })
                .catch((error) => {
                    console.error(
                        "Error in creating Listing:",
                        error?.response?.data?.error
                    );
                    this.$showErrorToast(
                        error?.response?.data?.error || "Something went wrong!"
                    );
                    this.isLoading = false;
                });
        },
    },
};
</script>
