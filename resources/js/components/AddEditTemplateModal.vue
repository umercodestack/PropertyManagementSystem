<template>
    <div class="pa-4 text-center">
        <v-dialog v-model="$props.showModal" max-width="600">
            <v-card v-if="!listingView">
                <v-card-title
                    class="d-flex justify-space-between align-center"
                    style="
                        position: sticky;
                        top: 0;
                        background: white;
                        z-index: 1;
                    "
                >
                    <div class="text-h5 ps-2">Create a message template</div>
                    <v-btn
                        icon="mdi-close"
                        variant="text"
                        @click="$emit('setAddEditModal', false)"
                    ></v-btn>
                </v-card-title>

                <v-card-text style="max-height: 400px; overflow-y: auto">
                    <v-row dense>
                        <v-col cols="12" md="12" sm="12">
                            <v-text-field
                                v-model="form.name"
                                label="Template Name*"
                                variant="outlined"
                            ></v-text-field>
                            <div
                                class="error-message text-danger"
                                style="margin-top: -15px; margin-bottom: 5px"
                                v-if="form.errors.has('name')"
                                v-html="form.errors.get('name')"
                            />
                        </v-col>

                        <div class="row justify-content-between" style="width: 100%">
                            <div class="col-md-6">
                                <v-btn
                                    color="primary"
                                    variant="tonal"
                                    size="small"
                                    @click="selectAllListing()"
                                >
                                    Select All
                                </v-btn>
                            </div>
                            <div class="col-md-6 text-right pr-0">
                                <v-btn
                                    color="primary"
                                    variant="tonal"
                                    size="small"
                                    @click="form.listings = []"
                                >
                                    Deselect All
                                </v-btn>
                            </div>
                        </div>

                        <v-col class="mt-2" cols="12" sm="12">
                            <v-autocomplete
                                v-model="form.listings"
                                :items="listings"
                                label="Listings*"
                                auto-select-first
                                multiple
                                variant="outlined"
                                item-title="name"
                                item-value="listing_id"
                            ></v-autocomplete>
                            <div
                                class="error-message text-danger"
                                style="margin-top: -15px; margin-bottom: 5px"
                                v-if="form.errors.has('listings')"
                                v-html="form.errors.get('listings')"
                            />
                        </v-col>

                        <!-- <v-col cols="12" sm="12">
                            <div class="d-flex gap-4 mb-5">
                                <div style="flex: 1">
                                    <v-select
                                        v-model="form.standard_check_in_time"
                                        :items="times"
                                        item-title="label"
                                        item-value="value"
                                        label="Standard Check-in*"
                                        variant="outlined"
                                        class="mb-4"
                                        hide-details
                                    ></v-select>
                                </div>
                                <div style="flex: 1">
                                    <v-select
                                        v-model="form.standard_check_out_time"
                                        :items="times"
                                        item-title="label"
                                        item-value="value"
                                        label="Standard Check-out*"
                                        variant="outlined"
                                        class="mb-4"
                                        hide-details
                                    ></v-select>
                                </div>
                            </div>
                        </v-col> -->
                        <v-col cols="12" sm="12">
                            <v-card-title
                                class="ml-0 pl-0 text-h6 font-weight-bold pb-2"
                                >Scheduling</v-card-title
                            >
                            <v-card-text
                                class="ml-0 pl-0 text-body-2 text-grey-darken-1 pb-4"
                            >
                                Choose an action that'll trigger your message
                                and how long before or after the action to send.
                            </v-card-text>

                            <v-select
                                v-model="form.action"
                                :items="actions"
                                label="Action*"
                                variant="outlined"
                                class="mb-4"
                                item-title="label"
                                item-value="value"
                                hide-details
                            ></v-select>
                            <div
                                class="error-message text-danger"
                                style="margin-top: -15px; margin-bottom: 5px"
                                v-if="form.errors.has('action')"
                                v-html="form.errors.get('action')"
                            />

                            <div
                                v-if="
                                    form.action === 'check_in' ||
                                    form.action === 'check_out'
                                "
                                class="d-flex gap-4 mb-4"
                            >
                                <div style="flex: 1">
                                    <v-select
                                        v-model="form.day"
                                        :items="days"
                                        item-title="label"
                                        item-value="value"
                                        label="Day*"
                                        variant="outlined"
                                        class="mb-4"
                                        hide-details
                                    ></v-select>
                                </div>
                                <div style="flex: 1">
                                    <v-select
                                        v-model="form.time"
                                        :items="times"
                                        item-title="label"
                                        item-value="value"
                                        label="Time*"
                                        variant="outlined"
                                        class="mb-4"
                                        hide-details
                                    ></v-select>
                                </div>
                            </div>

                            <div v-else>
                                <v-select
                                    v-model="form.when_to_send"
                                    :items="timings"
                                    item-title="label"
                                    item-value="value"
                                    label="When to send"
                                    variant="outlined"
                                    hide-details
                                ></v-select>
                            </div>
                        </v-col>

                        <v-col cols="12" md="12" sm="12">
                            <div class="shortcode-buttons mb-3 text-right mt-3">
                                <v-menu open-on-hover>
                                    <template v-slot:activator="{ props }">
                                        <v-btn color="primary" v-bind="props">
                                            Insert Shortcode
                                        </v-btn>
                                    </template>

                                    <v-list>
                                        <v-list-item
                                            v-for="(
                                                shortcode, index
                                            ) in form.action ===
                                            'booking_inquiry'
                                                ? inquiryShortcodes
                                                : shortcodes"
                                            :key="index"
                                        >
                                            <v-list-item-title
                                                class="cursor-pointer"
                                                @click="
                                                    insertShortcode(shortcode)
                                                "
                                                >{{
                                                    shortcode
                                                }}</v-list-item-title
                                            >
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                            </div>

                            <!-- Textarea with Message -->
                            <v-textarea
                                ref="textarea"
                                v-model="form.message"
                                label="Message*"
                                variant="outlined"
                            ></v-textarea>

                            <div
                                class="error-message text-danger"
                                style="margin-top: -15px; margin-bottom: 5px"
                                v-if="form.errors.has('message')"
                                v-html="form.errors.get('message')"
                            />
                        </v-col>

                        <v-col
                            cols="12"
                            sm="12"
                            :class="
                                form.action === 'check_in' ||
                                form.action === 'check_out'
                                    ? 'mt-4'
                                    : 'mt-0'
                            "
                        >
                            <v-text-caption class="text-grey-darken-1">
                                Only use them for info that's already stored.
                                Messages with empty shortcodes won't be sent.
                            </v-text-caption>
                        </v-col>
                    </v-row>
                </v-card-text>

                <v-card-actions
                    style="
                        position: sticky;
                        bottom: 0;
                        background: white;
                        z-index: 1;
                    "
                >
                    <v-btn
                        text="Close"
                        variant="plain"
                        @click="$emit('setAddEditModal', false)"
                    ></v-btn>
                    <v-btn
                        color="primary"
                        :text="editData ? 'Update' : 'Save'"
                        variant="tonal"
                        @click="editData ? updateTemplate() : saveTemplate()"
                    ></v-btn>
                </v-card-actions>
            </v-card>

            <v-card v-else>
                <v-card-title
                    class="d-flex"
                    style="
                        position: sticky;
                        top: 0;
                        background: white;
                        z-index: 1;
                    "
                >
                    <v-btn
                        @click="listingView = false"
                        icon="mdi-arrow-left"
                        size="x-small"
                    ></v-btn>
                    <div class="text-h5 ps-2">Select Listings</div>
                </v-card-title>

                <v-divider></v-divider>

                <v-card-actions
                    style="
                        position: sticky;
                        bottom: 0;
                        background: white;
                        z-index: 1;
                    "
                >
                    <v-spacer></v-spacer>
                    <v-btn
                        text="Close"
                        variant="plain"
                        @click="$emit('setAddEditModal', false)"
                    ></v-btn>
                    <v-btn
                        color="primary"
                        text="Save"
                        variant="tonal"
                        @click="dialog = false"
                    ></v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ["showModal", "editData"],
    data() {
        return {
            shortcodes: [
                "[LISTING_NAME]",
                "[GUEST_NAME]",
                "[CHECK_IN_DATE]",
                "[CHECK_OUT_DATE]",
                "[CHECK_IN_TIME]",
                "[CHECK_OUT_TIME]",
                "[TOTAL_AMOUNT]",
                "[ADDRESS]",
                // "[DIRECTIONS]",
                "[WIFI_NAME]",
                "[WIFI_PASSWORD]",
            ],
            inquiryShortcodes: [
                "[LISTING_NAME]",
                "[GUEST_NAME]",
                "[CHECK_IN_DATE]",
                "[CHECK_OUT_DATE]",
                "[WIFI_NAME]",
                "[WIFI_PASSWORD]",
            ],
            selectedShortcode: null,
            listingView: false,
            listings: [],
            form: new form({
                id: "",
                name: "",
                message: "",
                short_codes: "",
                action: "",
                day: "",
                time: "",
                when_to_send: "",
                listings: ["1283344995171469009"],
                standard_check_in_time: "16",
                standard_check_out_time: "16",
            }),
            actions: [
                { label: "Booking Inquiry", value: "booking_inquiry" },
                { label: "Booking confirmed", value: "booking_confirmed" },
                { label: "Check-in", value: "check_in" },
                { label: "Check-out", value: "check_out" },
            ],
            timings: [
                { value: "immediately_after", label: "Immediately after" },
                { value: "5_minutes_after", label: "5 minutes after" },
                { value: "10_minutes_after", label: "10 minutes after" },
                { value: "15_minutes_after", label: "15 minutes after" },
                { value: "30_minutes_after", label: "30 minutes after" },
                { value: "1_hours_after", label: "1 hour after" },
                { value: "2_hours_after", label: "2 hours after" },
                { value: "4_hours_after", label: "4 hours after" },
                { value: "8_hours_after", label: "8 hours after" },
                { value: "16_hours_after", label: "16 hours after" },
                { value: "24_hours_after", label: "24 hours after" },
            ],
            days: [
                { value: "14_days_before", label: "14 days before" },
                { value: "13_days_before", label: "13 days before" },
                { value: "12_days_before", label: "12 days before" },
                { value: "11_days_before", label: "11 days before" },
                { value: "10_days_before", label: "10 days before" },
                { value: "9_days_before", label: "9 days before" },
                { value: "8_days_before", label: "8 days before" },
                { value: "7_days_before", label: "7 days before" },
                { value: "6_days_before", label: "6 days before" },
                { value: "5_days_before", label: "5 days before" },
                { value: "4_days_before", label: "4 days before" },
                { value: "3_days_before", label: "3 days before" },
                { value: "2_days_before", label: "2 days before" },
                { value: "1_days_before", label: "1 day before" },
                { value: "day_of", label: "Day of" },
                { value: "1_days_after", label: "1 day after" },
                { value: "2_days_after", label: "2 days after" },
                { value: "3_days_after", label: "3 days after" },
                { value: "4_days_after", label: "4 days after" },
                { value: "5_days_after", label: "5 days after" },
                { value: "6_days_after", label: "6 days after" },
                { value: "7_days_after", label: "7 days after" },
                { value: "8_days_after", label: "8 days after" },
                { value: "9_days_after", label: "9 days after" },
                { value: "10_days_after", label: "10 days after" },
                { value: "11_days_after", label: "11 days after" },
                { value: "12_days_after", label: "12 days after" },
                { value: "13_days_after", label: "13 days after" },
                { value: "14_days_after", label: "14 days after" },
            ],
            times: [
                { value: "0", label: "12:00 AM" },
                { value: "1", label: "1:00 AM" },
                { value: "2", label: "2:00 AM" },
                { value: "3", label: "3:00 AM" },
                { value: "4", label: "4:00 AM" },
                { value: "5", label: "5:00 AM" },
                { value: "6", label: "6:00 AM" },
                { value: "7", label: "7:00 AM" },
                { value: "8", label: "8:00 AM" },
                { value: "9", label: "9:00 AM" },
                { value: "10", label: "10:00 AM" },
                { value: "11", label: "11:00 AM" },
                { value: "12", label: "12:00 PM" },
                { value: "13", label: "1:00 PM" },
                { value: "14", label: "2:00 PM" },
                { value: "15", label: "3:00 PM" },
                { value: "16", label: "4:00 PM" },
                { value: "17", label: "5:00 PM" },
                { value: "18", label: "6:00 PM" },
                { value: "19", label: "7:00 PM" },
                { value: "20", label: "8:00 PM" },
                { value: "21", label: "9:00 PM" },
                { value: "22", label: "10:00 PM" },
                { value: "23", label: "11:00 PM" },
            ],
        };
    },
    methods: {
        selectAllListing() {
            this.form.listings = this.listings.map((i) => i.listing_id);
        },
        insertShortcode(shortcode) {
            const textarea = this.$refs.textarea.$el.querySelector("textarea");
            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;

            this.form.message =
                this.form.message.substring(0, startPos) +
                shortcode +
                this.form.message.substring(endPos);

            this.$nextTick(() => {
                textarea.setSelectionRange(
                    startPos + shortcode.length,
                    startPos + shortcode.length
                );
                textarea.focus();
            });
        },
        saveTemplate() {
            this.form.post("/api/templates").then((response) => {
                this.$emit("fetchTemplates");
                this.$emit("setAddEditModal", false);
            });
        },
        updateTemplate() {
            this.form.put("/api/templates/" + this.form.id).then((response) => {
                this.$emit("fetchTemplates");
                this.$emit("setAddEditModal", false);
            });
        },
    },
    mounted() {
        axios.get("/api/listings/all").then((response) => {
            this.listings = response?.data;
        });
    },
    watch: {
        editData(newValue) {
            if (newValue && newValue.id) {
                const listings =
                    newValue?.listings.map(
                        (item) => JSON.parse(item.listing_json)?.id
                    ) || [];
                console.log("newValue...", newValue);
                this.form.fill(newValue);
                this.form.listings = listings;
            } else {
                this.form.reset();
            }
        },
        // "form.action"(newValue) {
        //     if (newValue === "check_in" || newValue === "check_out") {
        //         this.form.when_to_send = "";
        //     } else {
        //         this.form.day = "";
        //         this.form.time = "";
        //     }
        // },
    },
};
</script>

<style scoped>
.v-input__details {
    display: none !important;
}
</style>
