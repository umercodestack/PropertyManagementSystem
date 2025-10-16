<template>
    <form @submit.prevent="updatePrices">
        <div class="row">
            <!-- Listing Currency -->
            <div class="col-md-6 mb-3">
                <label for="listing_currency">Listing Currency</label>
                <input
                    type="text"
                    id="listing_currency"
                    class="form-control"
                    v-model="form.listing_currency"
                />
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('listing_currency')"
                    v-html="form.errors.get('listing_currency')"
                />
            </div>

            <!-- Default Daily Price -->
            <div class="col-md-6 mb-3">
                <label for="default_daily_price">Default Daily Price</label>
                <input
                    type="number"
                    id="default_daily_price"
                    class="form-control"
                    v-model="form.default_daily_price"
                />
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('default_daily_price')"
                    v-html="form.errors.get('default_daily_price')"
                />
            </div>

            <!-- Pass Through Taxes -->
            <div class="col-md-12 mb-3">
                <h5>Pass Through Taxes</h5>
                <div class="row">
                    <!-- Tax Type -->
                    <div class="col-md-4 mb-3">
                        <label for="tax_type">Tax Type</label>
                        <select
                            class="form-select"
                            v-model="form.pass_through_taxes[0].tax_type"
                        >
                            <option
                                v-for="(title, value) in taxTypes"
                                :key="value"
                                :value="value"
                            >
                                {{ title }}
                            </option>
                        </select>
                        <div
                            class="error-message text-danger"
                            v-if="
                                form.errors.has('pass_through_taxes.0.tax_type')
                            "
                            v-html="
                                form.errors.get('pass_through_taxes.0.tax_type')
                            "
                        />
                    </div>

                    <!-- Amount -->
                    <div class="col-md-4 mb-3">
                        <label for="amount">Amount</label>
                        <input
                            type="number"
                            id="amount"
                            class="form-control"
                            v-model="form.pass_through_taxes[0].amount"
                        />
                        <div
                            class="error-message text-danger"
                            v-if="
                                form.errors.has('pass_through_taxes.0.amount')
                            "
                            v-html="
                                form.errors.get('pass_through_taxes.0.amount')
                            "
                        />
                    </div>

                    <!-- Amount Type -->
                    <div class="col-md-4 mb-3">
                        <label for="amount_type">Amount Type</label>
                        <select
                            class="form-select"
                            v-model="form.pass_through_taxes[0].amount_type"
                        >
                            <option
                                v-for="(title, value) in amountTypes"
                                :key="value"
                                :value="value"
                            >
                                {{ title }}
                            </option>
                        </select>
                        <div
                            class="error-message text-danger"
                            v-if="
                                form.errors.has(
                                    'pass_through_taxes.0.amount_type'
                                )
                            "
                            v-html="
                                form.errors.get(
                                    'pass_through_taxes.0.amount_type'
                                )
                            "
                        />
                    </div>

                    <!-- Taxable Base -->
                    <div class="col-md-4 mb-3">
                        <label for="taxable_base">Taxable Base</label>
                        <select
                            class="form-select"
                            v-model="form.pass_through_taxes[0].taxable_base[0]"
                        >
                            <option
                                v-for="(title, value) in taxableBaseOptions"
                                :key="value"
                                :value="value"
                            >
                                {{ title }}
                            </option>
                        </select>
                        <div
                            class="error-message text-danger"
                            v-if="form.errors.has('taxable_base')"
                            v-html="form.errors.get('taxable_base')"
                        />
                    </div>

                    <!-- Business Tax ID -->
                    <div class="col-md-4 mb-3">
                        <label for="business_tax_id">Business Tax ID</label>
                        <input
                            type="text"
                            id="business_tax_id"
                            class="form-control"
                            v-model="form.pass_through_taxes[0].business_tax_id"
                        />
                        <div
                            class="error-message text-danger"
                            v-if="form.errors.has('business_tax_id')"
                            v-html="form.errors.get('business_tax_id')"
                        />
                    </div>

                    <!-- No Business Tax ID Declaration -->
                    <div class="col-md-4 mb-3">
                        <label for="no_business_tax_id_declaration"
                            >No Business Tax ID Declaration</label
                        >
                        <input
                            type="checkbox"
                            id="no_business_tax_id_declaration"
                            v-model="
                                form.pass_through_taxes[0]
                                    .no_business_tax_id_declaration
                            "
                        />
                    </div>

                    <!-- TOT Registration ID -->
                    <div class="col-md-4 mb-3">
                        <label for="tot_registration_id"
                            >TOT Registration ID</label
                        >
                        <input
                            type="text"
                            id="tot_registration_id"
                            class="form-control"
                            v-model="
                                form.pass_through_taxes[0].tot_registration_id
                            "
                        />
                        <div
                            class="error-message text-danger"
                            v-if="form.errors.has('tot_registration_id')"
                            v-html="form.errors.get('tot_registration_id')"
                        />
                    </div>

                    <!-- No TOT Registration ID Declaration -->
                    <div class="col-md-4 mb-3 d-block">
                        <label for="no_tot_registration_id_declaration"
                            >No TOT Registration ID Declaration</label
                        >
                        <input
                            type="checkbox"
                            id="no_tot_registration_id_declaration"
                            v-model="
                                form.pass_through_taxes[0]
                                    .no_tot_registration_id_declaration
                            "
                        />
                    </div>

                    <!-- Attestation -->
                    <div class="col-md-4 mb-3">
                        <label for="attestation">Attestation</label>
                        <input
                            type="checkbox"
                            id="attestation"
                            v-model="form.pass_through_taxes[0].attestation"
                        />
                    </div>

                    <!-- Long Term Stay Exemption -->
                    <div class="col-md-4 mb-3">
                        <label for="long_term_stay_exemption"
                            >Long Term Stay Exemption</label
                        >
                        <input
                            type="number"
                            id="long_term_stay_exemption"
                            class="form-control"
                            v-model="
                                form.pass_through_taxes[0]
                                    .long_term_stay_exemption
                            "
                        />
                        <div
                            class="error-message text-danger"
                            v-if="form.errors.has('long_term_stay_exemption')"
                            v-html="form.errors.get('long_term_stay_exemption')"
                        />
                    </div>

                    <!-- Only First Nights Exemption -->
                    <div class="col-md-4 mb-3">
                        <label for="only_first_nights_exemption"
                            >Only First Nights Exemption</label
                        >
                        <input
                            type="number"
                            id="only_first_nights_exemption"
                            class="form-control"
                            v-model="
                                form.pass_through_taxes[0]
                                    .only_first_nights_exemption
                            "
                        />
                        <div
                            class="error-message text-danger"
                            v-if="
                                form.errors.has('only_first_nights_exemption')
                            "
                            v-html="
                                form.errors.get('only_first_nights_exemption')
                            "
                        />
                    </div>

                    <!-- Max Cap Per Person Per Night -->
                    <div class="col-md-4 mb-3">
                        <label for="max_cap_per_person_per_night"
                            >Max Cap Per Person Per Night</label
                        >
                        <input
                            type="number"
                            id="max_cap_per_person_per_night"
                            class="form-control"
                            v-model="
                                form.pass_through_taxes[0]
                                    .max_cap_per_person_per_night
                            "
                        />
                        <div
                            class="error-message text-danger"
                            v-if="
                                form.errors.has('max_cap_per_person_per_night')
                            "
                            v-html="
                                form.errors.get('max_cap_per_person_per_night')
                            "
                        />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button
                    type="submit"
                    class="btn btn-primary text-light"
                    :disabled="isLoading"
                >
                    <span
                        v-if="isLoading"
                        class="spinner-border spinner-border-sm"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    <span v-if="!isLoading">Update Prices</span>
                    <span v-else>Updating...</span>
                </button>
            </div>
        </div>
    </form>
</template>

<script>
import axios from "axios";
export default {
    props: ["listingId", "listingName", "prices"],
    data() {
        return {
            isTextareaLocked: false,
            isLoading: false,
            form: new form({
                listing_currency: "SAR",
                default_daily_price: 0,
                pass_through_taxes: [
                    {
                        tax_type: "",
                        amount: 0,
                        amount_type: "",
                        taxable_base: [""],
                        business_tax_id: "",
                        no_business_tax_id_declaration: true,
                        tot_registration_id: "",
                        no_tot_registration_id_declaration: true,
                        attestation: false,
                        long_term_stay_exemption: 0,
                        only_first_nights_exemption: 0,
                        max_cap_per_person_per_night: 0,
                    },
                ],
            }),
            amountTypes: {
                percent_per_reservation: "Percent Per Reservation",
                flat_per_guest: "Flat Per Guest",
                flat_per_guest_per_night: "Flat Per Guest Per Night",
                flat_per_night: "Flat Per Night",
            },
            taxTypes: {
                pass_through_hotel_tax: "Hotel Tax",
                pass_through_lodging_tax: "Lodging Tax",
                pass_through_room_tax: "Room Tax",
                pass_through_tourist_tax: "Tourist Tax",
                pass_through_transient_occupancy_tax: "Transient Occupancy Tax",
                pass_through_sales_tax: "Sales Tax",
                pass_through_vat_gst: "VAT/GST",
                pass_through_tourism_assessment_fee: "Tourism Assessment Fee",
            },
            taxableBaseOptions: {
                base_price: "Base Price",
                pass_through_resort_fee: "Resort Fee",
                pass_through_community_fee: "Community Fee",
                pass_through_management_fee: "Management Fee",
                pass_through_linen_fee: "Linen Fee",
                pass_through_cleaning_fee: "Cleaning Fee",
                pass_through_pet_fee: "Pet Fee",
            },
        };
    },
    mounted() {
        if (this.prices && this.prices?.listing_currency) {
            this.form.fill(this.prices);
        }
        this.form.name = this.listingName || null;
    },
    methods: {
        checkCharacterLimit() {
            if (this.form.summary.length >= 500) {
                this.form.summary = this.form.summary.substring(0, 500);
            }
        },
        updatePrices() {
            this.isLoading = true;

            this.form
                .put("/api/airbnb/listings/pricing/" + this.listingId)
                .then((response) => {
                    this.isLoading = false;
                    this.$showSuccessToast("Prices Updated!");
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
    },
};
</script>
