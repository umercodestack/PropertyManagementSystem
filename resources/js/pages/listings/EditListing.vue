<template>
    <div class="container">
        <h2 v-if="form.name" class="mt-4">Edit ({{ form.name }})</h2>
        <h2 v-else class="mt-4">Edit Listing</h2>
        <div class="card card-bordered">
            <ul style="margin-left: 20px" class="nav nav-tabs">
                <li v-for="(tab, index) in tabs" :key="index" class="nav-item">
                    <a
                        :class="['nav-link', { active: currentTab === tab }]"
                        :href="`#${tab.toLowerCase().replace(/\s+/g, '-')}`"
                        @click="setActiveTab(tab, $event)"
                    >
                        {{ tab }}
                    </a>
                </li>
            </ul>
            <div v-if="currentTab == 'Main Settings'" class="card-inner">
                <form @submit.prevent="updateListing">
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
                        <!-- Property Type Group -->
                        <div class="col-md-3 mb-3">
                            <label for="propertyTypeGroup"
                                >Property Type Group *</label
                            >
                            <select
                                id="propertyTypeGroup"
                                class="form-select"
                                v-model="form.property_type_group"
                            >
                                <option
                                    v-for="group in $propertyTypeGroups"
                                    :key="group.value"
                                    :value="group.value"
                                >
                                    {{ group.label }}
                                </option>
                            </select>

                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('property_type_group')"
                                v-html="form.errors.get('property_type_group')"
                            />
                        </div>
                        <!-- Property Type Category -->
                        <div class="col-md-3 mb-3">
                            <label for="propertyTypeCategory"
                                >Property Type Category *</label
                            >
                            <select
                                id="propertyTypeCategory"
                                class="form-select"
                                v-model="form.property_type_category"
                            >
                                <option
                                    v-for="category in $propertyTypeCategories"
                                    :key="category.value"
                                    :value="category.value"
                                >
                                    {{ category.label }}
                                </option>
                            </select>
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('property_type_category')"
                                v-html="
                                    form.errors.get('property_type_category')
                                "
                            />
                        </div>
                        <!-- Room Type Category -->
                        <div class="col-md-3 mb-3">
                            <label for="roomTypeCategory"
                                >Room Type Category *</label
                            >
                            <select
                                id="roomTypeCategory"
                                class="form-select"
                                v-model="form.room_type_category"
                            >
                                <option value="private_room">
                                    Private Room
                                </option>
                                <option value="shared_room">Shared Room</option>
                                <option value="entire_home">Entire Home</option>
                            </select>

                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('room_type_category')"
                                v-html="form.errors.get('room_type_category')"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Bedrooms -->
                        <div class="col-md-3 mb-3">
                            <label for="bedrooms">Bedrooms *</label>
                            <input
                                type="text"
                                id="bedrooms"
                                class="form-control"
                                v-model="form.bedrooms"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('bedrooms')"
                                v-html="form.errors.get('bedrooms')"
                            />
                        </div>
                        <!-- Bathrooms -->
                        <div class="col-md-3 mb-3">
                            <label for="bathrooms">Bathrooms *</label>
                            <input
                                type="text"
                                id="bathrooms"
                                class="form-control"
                                v-model="form.bathrooms"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('bathrooms')"
                                v-html="form.errors.get('bathrooms')"
                            />
                        </div>
                        <!-- Beds -->
                        <div class="col-md-3 mb-3">
                            <label for="beds">Beds *</label>
                            <input
                                type="text"
                                id="beds"
                                class="form-control"
                                v-model="form.beds"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('beds')"
                                v-html="form.errors.get('beds')"
                            />
                        </div>
                        <!-- Listing Views -->
                        <div class="col-md-3 mb-3">
                            <label for="listingViews">Listing Views</label>
                            <select
                                id="listingViews"
                                class="form-select"
                                v-model="form.listing_views"
                            >
                                <option
                                    v-for="option in $listingViewOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('listing_views')"
                                v-html="form.errors.get('listing_views')"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Check-in Option -->
                        <div class="col-md-3 mb-3">
                            <label for="checkInOption"
                                >Check-in Option Category</label
                            >
                            <select
                                id="checkInOptionCategory"
                                class="form-select"
                                v-model="form.check_in_option.category"
                            >
                                <option
                                    v-for="option in $checkInOptionCategories"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                            <div
                                class="error-message text-danger"
                                v-if="
                                    form.errors.has('check_in_option.category')
                                "
                                v-html="
                                    form.errors.get('check_in_option.category')
                                "
                            />
                        </div>

                        <!-- Check-in Option -->
                        <div class="col-md-3 mb-3">
                            <label for="checkInOption"
                                >Check-in Option Instructions</label
                            >
                            <input
                                type="text"
                                id="checkInOption"
                                class="form-control"
                                v-model="form.check_in_option.instruction"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="
                                    form.errors.has(
                                        'check_in_option.instruction'
                                    )
                                "
                                v-html="
                                    form.errors.get(
                                        'check_in_option.instruction'
                                    )
                                "
                            />
                        </div>

                        <!-- Permit or Tax ID -->
                        <!-- <div class="col-md-3 mb-3">
                            <label for="permitOrTaxId">Permit/Tax ID *</label>
                            <input
                                type="text"
                                id="permitOrTaxId"
                                class="form-control"
                                v-model="form.permit_or_tax_id"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('permit_or_tax_id')"
                                v-html="form.errors.get('permit_or_tax_id')"
                            />
                        </div> -->
                        <!-- Apt -->
                        <div class="col-md-3 mb-3">
                            <label for="apt">Apartment Number</label>
                            <input
                                type="text"
                                id="apt"
                                class="form-control"
                                v-model="form.apt"
                            />
                        </div>
                        <!-- Street -->
                        <div class="col-md-3 mb-3">
                            <label for="street">Street *</label>
                            <input
                                type="text"
                                id="street"
                                class="form-control"
                                v-model="form.street"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('street')"
                                v-html="form.errors.get('street')"
                            />
                        </div>

                        <!-- City -->
                        <div class="col-md-3 mb-3">
                            <label for="city">City *</label>
                            <input
                                type="text"
                                id="city"
                                class="form-control"
                                v-model="form.city"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('city')"
                                v-html="form.errors.get('city')"
                            />
                        </div>
                        <!-- State -->
                        <div class="col-md-3 mb-3">
                            <label for="state">State *</label>
                            <input
                                type="text"
                                id="state"
                                class="form-control"
                                v-model="form.state"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('state')"
                                v-html="form.errors.get('state')"
                            />
                        </div>
                        <!-- Country Code -->
                        <div class="col-md-3 mb-3">
                            <label for="countryCode">Country Code *</label>
                            <select
                                id="countryCode"
                                class="form-select"
                                v-model="form.country_code"
                            >
                                <option
                                    v-for="country in $getCountries"
                                    :key="country.code"
                                    :value="country.code"
                                >
                                    {{ country.name }}
                                </option>
                            </select>
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('country_code')"
                                v-html="form.errors.get('country_code')"
                            />
                        </div>
                        <!-- Zipcode -->
                        <div class="col-md-3 mb-3">
                            <label for="zipcode">Zipcode *</label>
                            <input
                                type="text"
                                id="zipcode"
                                class="form-control"
                                v-model="form.zipcode"
                            />
                            <div
                                class="error-message text-danger"
                                v-if="form.errors.has('zipcode')"
                                v-html="form.errors.get('zipcode')"
                            />
                        </div>

                        <!-- Latitude -->
                        <div class="col-md-3 mb-3">
                            <label for="lat">Latitude</label>
                            <input
                                type="text"
                                id="lat"
                                class="form-control"
                                v-model="form.lat"
                            />
                        </div>
                        <!-- Longitude -->
                        <div class="col-md-3 mb-3">
                            <label for="lng">Longitude</label>
                            <input
                                type="text"
                                id="lng"
                                class="form-control"
                                v-model="form.lng"
                            />
                        </div>

                        <!-- Person Capacity -->
                        <div class="col-md-3 mb-3">
                            <label for="personCapacity"
                                >Person Capacity *</label
                            >
                            <input
                                type="number"
                                id="personCapacity"
                                class="form-control"
                                v-model="form.person_capacity"
                            />
                        </div>
                        <!-- Requested Approval Status -->
                        <!-- <div class="col-md-3 mb-3">
                            <label for="approvalStatus"
                                >Approval Status *</label
                            >
                            <input
                                type="text"
                                id="approvalStatus"
                                class="form-control"
                                v-model="
                                    form.requested_approval_status_category
                                "
                            />
                        </div> -->
                        <!-- Total Inventory Count -->
                        <div class="col-md-3 mb-3">
                            <label for="inventoryCount"
                                >Total Inventory Count</label
                            >
                            <input
                                type="text"
                                id="inventoryCount"
                                class="form-control"
                                v-model="form.total_inventory_count"
                            />
                        </div>
                        <!-- Property External ID -->
                        <div class="col-md-3 mb-3">
                            <label for="externalId"
                                >Property External ID</label
                            >
                            <input
                                type="text"
                                id="externalId"
                                class="form-control"
                                v-model="form.property_external_id"
                            />
                        </div>

                        <!-- Has Availability -->
                        <div class="col-md-3 mb-3">
                            <label for="hasAvailability"
                                >Has Availability *</label
                            >
                            <select
                                id="hasAvailability"
                                class="form-select"
                                v-model="form.has_availability"
                            >
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                        </div>
                        <!-- Deactivation Reason -->
                        <div class="col-md-3 mb-3">
                            <label for="deactivationReason"
                                >Deactivation Reason</label
                            >
                            <input
                                type="text"
                                id="deactivationReason"
                                class="form-control"
                                v-model="form.deactivation_reason"
                            />
                        </div>
                        <!-- Display Exact Location to Guest -->
                        <div class="col-md-3 mb-3">
                            <label for="displayExactLocation"
                                >Display Exact Location to Guest *</label
                            >
                            <select
                                id="displayExactLocation"
                                class="form-select"
                                v-model="form.display_exact_location_to_guest"
                            >
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                        </div>

                        <!-- House Manual -->
                        <div class="col-md-3 mb-3">
                            <label for="houseManual">House Manual</label>
                            <input
                                type="text"
                                id="houseManual"
                                class="form-control"
                                v-model="form.house_manual"
                            />
                        </div>
                        <!-- Wifi Network -->
                        <div class="col-md-3 mb-3">
                            <label for="wifiNetwork">WiFi Network</label>
                            <input
                                type="text"
                                id="wifiNetwork"
                                class="form-control"
                                v-model="form.wifi_network"
                            />
                        </div>
                        <!-- Wifi Password -->
                        <div class="col-md-3 mb-3">
                            <label for="wifiPassword">WiFi Password</label>
                            <input
                                type="text"
                                id="wifiPassword"
                                class="form-control"
                                v-model="form.wifi_password"
                            />
                        </div>
                        <!-- Listing Nickname -->
                        <div class="col-md-3 mb-3">
                            <label for="listingNickname"
                                >Listing Nickname</label
                            >
                            <input
                                type="text"
                                id="listingNickname"
                                class="form-control"
                                v-model="form.listing_nickname"
                            />
                        </div>

                        <!-- Check-out Tasks -->
                        <!-- <div class="col-md-3 mb-3">
                            <label for="checkOutTask1">Check-out Task 1</label>
                            <input
                                type="text"
                                id="checkOutTask1"
                                class="form-control"
                                v-model="
                                    form.check_out_tasks.additionalProp1
                                        .task_detail
                                "
                            />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="checkOutTask2">Check-out Task 2</label>
                            <input
                                type="text"
                                id="checkOutTask2"
                                class="form-control"
                                v-model="
                                    form.check_out_tasks.additionalProp2
                                        .task_detail
                                "
                            />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="checkOutTask3">Check-out Task 3</label>
                            <input
                                type="text"
                                id="checkOutTask3"
                                class="form-control"
                                v-model="
                                    form.check_out_tasks.additionalProp3
                                        .task_detail
                                "
                            />
                        </div> -->
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
                        <span v-if="!isLoading">Update Listing</span>
                        <span v-else>Updating...</span>
                    </button>
                </form>
            </div>

            <div v-if="currentTab == 'Prices & Taxes'" class="card-inner">
                <EditPrices
                    :listingId="listingId"
                    :listingName="form.name"
                    :prices="listingPrices"
                />
            </div>
           
            <div v-if="currentTab == 'Description'" class="card-inner">
                <EditDescription
                    :listingId="listingId"
                    :listingName="form.name"
                    :description="listingDescription"
                />
            </div>
            <div v-show="currentTab == 'Photos'" class="card-inner">
                <EditImages
                    :listingId="listingId"
                    :description="listingDescription"
                />
            </div>
            <div v-show="currentTab == 'Rooms'" class="card-inner">
                <EditRooms :listingId="listingId" />
            </div>
            <!-- <div v-if="currentTab == 'Amenities'" class="card-inner">
                <EditAmenities :propertyId="propertyId" />
            </div>
            <div v-show="currentTab == 'Reviews'" class="card-inner">
                <EditReviews :propertyId="propertyId" />
            </div>
            <div v-show="currentTab == 'Owner Adjustment'" class="card-inner">
                <EditOwnerAdjustment :propertyId="propertyId" />
            </div>
            <div v-if="currentTab == 'Host Management'" class="card-inner">
                <EditHosts
                    :propertyId="propertyId"
                    :listing="propertyData?.listing"
                    :listingHosts="propertyData?.listing?.user_id"
                />
            </div>
            <div v-if="currentTab == 'Discounts'" class="card-inner">
                <EditDiscount
                    :propertyId="propertyId"
                    :discountDetails="discountDetails"
                />
            </div>
            <div v-if="currentTab == 'Owner'" class="card-inner">
                <EditOwner
                    :propertyId="propertyId"
                    :propertyUid="propertyData.hf_property_uid"
                    :ownerUids="propertyData?.details?.owner_uids"
                />
            </div> -->
        </div>
    </div>
</template>

<script>
import axios from "axios";
import EditDescription from "@/components/EditDescription.vue";
import EditImages from "@/components/EditImages.vue";
import EditRooms from "@/components/EditRooms.vue";
import EditPrices from "@/components/EditPrices.vue";
export default {
    data() {
        return {
            isLoading: false,
            listingId: "",
            listingDescription: null,
            listingPrices: null,
            discountDetails: null,
            listingData: {},
            tabs: [
                "Main Settings",
                "Prices & Taxes",
                "Description",
                "Photos",
                "Rooms",
            ],
            currentTab: "Main Settings",
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
                property_external_id: null,
                has_availability: true,
                deactivation_reason: null,
                deactivation_details: null,
                display_exact_location_to_guest: true,
                house_manual: "",
                wifi_network: null,
                wifi_password: null,
                listing_nickname: null,
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
    created() {
        this.fetchListing();
    },
    components: {
        EditDescription,
        EditImages,
        EditRooms,
        EditPrices
    },
    methods: {
        updateListing() {
            this.isLoading = true;
            const id = this.$route.params.id;
            this.form
                .put("/api/airbnb/listings/" + id)
                .then((response) => {
                    console.log("response...", response.data);
                    this.isLoading = false;
                    this.$showSuccessToast("Listing Updated!");
                    // this.$router.push({ name: "Listings" });
                })
                .catch((error) => {
                    console.error(
                        "Error in updating listing:",
                        error?.response?.data?.error
                    );
                    this.$showErrorToast(
                        error?.response?.data?.error || "Something went wrong!"
                    );
                    this.isLoading = false;
                });
        },
        fetchListing() {
            const id = this.$route.params.id;
            this.listingId = id;
            if (id) {
                axios
                    .get(`/api/airbnb/listings/${id}`)
                    .then((response) => {
                        if (response?.data?.data) {
                            this.listingData = response?.data?.data;
                            const description = this.listingData?.description;

                            this.listingDescription = description
                                ? JSON.parse(description)
                                : {};

                            this.listing = this.listingData?.details;
                            if (this.listing) {
                                this.form.fill(this.listing);
                            }
                            this.form.name = this.listingData?.name;

                            this.listingPrices = this.listingData?.prices
                        }
                        if (this.form.airbnbData == null) {
                            this.form.airbnbData = {};
                        }
                        // for (let key in this.form) {
                        //     if (this.form[key] == null) {
                        //         this.form[key] = {};
                        //     }
                        // }
                    })
                    .catch((error) => {
                        console.error("Error fetching listing details:", error);
                    });
            } else {
                console.error(
                    "No listing ID provided in the query parameters."
                );
            }
        },
        setActiveTab(tab) {
            event.preventDefault();
            this.currentTab = tab;
        },
    },
};
</script>
