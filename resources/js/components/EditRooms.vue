<template>
    <div>
        <!-- Room Form -->
        <form
            @submit.prevent="form.id ? updateRoom() : saveRoom()"
            class="mb-4"
        >
            <!-- Room Number -->
            <div class="form-group">
                <label for="roomNumber">Room Number *</label>
                <input
                    type="number"
                    id="roomNumber"
                    class="form-control"
                    v-model="form.room_number"
                />
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('room_number')"
                    v-html="form.errors.get('room_number')"
                />
            </div>

            <!-- Beds -->
            <div class="form-group">
                <label for="beds">Beds *</label>
                <div
                    v-for="(bed, index) in form.beds"
                    :key="index"
                    class="d-flex align-items-center mb-2"
                >
                    <select class="form-select mr-2" v-model="bed.type">
                        <option value="king_bed">King Bed</option>
                        <option value="queen_bed">Queen Bed</option>
                        <option value="single_bed">Single Bed</option>
                    </select>
                    <input
                        type="number"
                        class="form-control mr-2"
                        v-model="bed.quantity"
                        placeholder="Quantity"
                    />
                    <button
                        type="button"
                        class="text-light btn btn-danger"
                        @click="removeBed(index)"
                    >
                        Delete
                    </button>
                </div>
                <button
                    type="button"
                    class="text-light btn btn-secondary"
                    @click="addBed"
                >
                    Add Bed
                </button>
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('beds')"
                    v-html="form.errors.get('beds')"
                />
            </div>

            <!-- Room Amenities -->
            <div class="form-group">
                <label for="roomAmenities">Room Amenities *</label>
                <div
                    v-for="(amenity, index) in form.room_amenities"
                    :key="index"
                    class="d-flex align-items-center mb-2"
                >
                    <select
                        id="roomType"
                        class="form-select"
                        v-model="amenity.type"
                    >
                        <option
                            v-for="amenity in amenities"
                            :key="amenity.value"
                            :value="amenity.value"
                        >
                            {{ amenity.label }}
                        </option>
                    </select>
                    <input
                        type="number"
                        class="form-control mr-2"
                        v-model="amenity.quantity"
                        placeholder="Quantity"
                    />
                    <button
                        type="button"
                        class="btn btn-danger text-light"
                        @click="removeAmenity(index)"
                    >
                        Delete
                    </button>
                </div>
                <button
                    type="button"
                    class="text-light btn btn-secondary"
                    @click="addAmenity"
                >
                    Add Amenity
                </button>
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('room_amenities')"
                    v-html="form.errors.get('room_amenities')"
                />
            </div>

            <!-- Room Type -->
            <div class="form-group">
                <label for="roomType">Room Type *</label>
                <select
                    id="roomType"
                    class="form-select"
                    v-model="form.room_type"
                >
                    <option
                        v-for="room in roomTypes"
                        :key="room.value"
                        :value="room.value"
                    >
                        {{ room.label }}
                    </option>
                </select>

                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('room_type')"
                    v-html="form.errors.get('room_type')"
                />
            </div>

            <!-- Is Private -->
            <div class="form-group">
                <label for="isPrivate">Private Room *</label>
                <input
                    type="checkbox"
                    id="isPrivate"
                    class="form-check-input"
                    v-model="form.is_private"
                />
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('is_private')"
                    v-html="form.errors.get('is_private')"
                />
            </div>

            <!-- Metadata -->
            <!-- <div class="form-group">
                <label for="metadata">Metadata</label>
                <textarea
                    id="metadata"
                    class="form-control"
                    v-model="form.metadata"
                    placeholder="Optional metadata in JSON format"
                ></textarea>
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('metadata')"
                    v-html="form.errors.get('metadata')"
                />
            </div> -->
            <button
            v-if="form.id"
                type="button"
                @click="form.reset()"
                class="btn btn-warning mr-2 text-dark"
            >
                <span>Add New</span>
            </button>
            <button
                type="submit"
                class="btn btn-primary text-light"
                :disabled="isLoading"
            >
                <span
                    v-if="isLoading"
                    class="spinner-border spinner-border-sm"
                    role="status"
                ></span>
                <span v-if="!isLoading">{{
                    form.id ? "Update Room Details" : "Save Room Details"
                }}</span>
                <span v-else>Saving...</span>
            </button>
        </form>

        <hr />
        <!-- Saved Rooms Table -->
        <div class="mt-4">
            <h4>Saved Rooms</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>AirBnB Room ID</th>
                        <th>Room Number</th>
                        <th>Beds</th>
                        <th>Room Amenities</th>
                        <th>Room Type</th>
                        <th>Is Private</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(room, index) in savedRooms" :key="room.id">
                        <!-- Room Number -->
                        <td>{{ room.airbnb_room_id }}</td>

                        <!-- Room Number -->
                        <td>{{ room.room_number }}</td>

                        <!-- Beds (Display as type: quantity) -->
                        <td>
                            <ul>
                                <li
                                    v-for="(bed, bedIndex) in room.beds"
                                    :key="bedIndex"
                                >
                                    {{ bed.type }}: {{ bed.quantity }}
                                </li>
                            </ul>
                        </td>

                        <!-- Room Amenities (Display as type: quantity) -->
                        <td>
                            <ul>
                                <li
                                    v-for="(
                                        amenity, amenityIndex
                                    ) in room.room_amenities"
                                    :key="amenityIndex"
                                >
                                    {{ amenity.type }}: {{ amenity.quantity }}
                                </li>
                            </ul>
                        </td>

                        <!-- Room Type -->
                        <td>{{ room.room_type }}</td>

                        <!-- Is Private -->
                        <td>{{ room.is_private ? "Yes" : "No" }}</td>

                        <!-- Actions -->
                        <td>
                            <button
                                class="btn btn-secondary"
                                @click="editRoom(room)"
                            >
                                <span v-if="!editingRoom[index]">Edit</span>
                            </button>

                            <button
                                :disabled="deletingRoom[index]"
                                class="btn btn-danger ml-2"
                                @click="deleteRoom(index)"
                            >
                                <span
                                    v-if="deletingRoom[index]"
                                    class="text-light spinner-border spinner-border-sm"
                                    role="status"
                                    aria-hidden="true"
                                ></span>
                                <span v-if="!deletingRoom[index]">Delete</span>
                                <span v-else>Deleting...</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-if="savedRooms.length === 0" class="mt-2 text-center">
                No rooms added yet.
            </p>
        </div>
    </div>
</template>

<script>
export default {
    props: ["listingId"],
    data() {
        return {
            roomTypes: [
                { label: "Backyard", value: "backyard" },
                { label: "Basement", value: "basement" },
                { label: "Bedroom", value: "bedroom" },
                { label: "Common Space", value: "common_space" },
                { label: "Common Spaces", value: "common_spaces" },
                { label: "Dining Room", value: "dining_room" },
                { label: "Entrance to Home", value: "entrance_to_home" },
                { label: "Entry", value: "entry" },
                { label: "Exterior", value: "exterior" },
                { label: "Front Yard", value: "front_yard" },
                { label: "Family Room", value: "family_room" },
                { label: "Full Bathroom", value: "full_bathroom" },
                { label: "Half Bathroom", value: "half_bathroom" },
                { label: "Hot Tub", value: "hot_tub" },
                { label: "Garage", value: "garage" },
                { label: "Gym", value: "gym" },
                { label: "Kitchen", value: "kitchen" },
                { label: "Kitchenette", value: "kitchenette" },
                { label: "Laundry Room", value: "laundry_room" },
                { label: "Living Room", value: "living_room" },
                { label: "Office", value: "office" },
                { label: "Outdoor Common Area", value: "outdoor_common_area" },
                { label: "Outdoor Space", value: "outdoor_space" },
                { label: "Patio", value: "patio" },
                { label: "Pool", value: "pool" },
                { label: "Recreation Area", value: "recreation_area" },
                { label: "Study", value: "study" },
                { label: "Studio", value: "studio" },
            ],
            amenities: [
                { label: "Ceiling Fan", value: "ceiling_fan" },
                {
                    label: "Central Air Conditioning",
                    value: "central_air_conditioning",
                },
                { label: "Heating", value: "heating" },
                { label: "Radiant Heating", value: "radiant_heating" },
                {
                    label: "Portable Air Conditioning",
                    value: "portable_air_conditioning",
                },
                { label: "Heated Floors", value: "heated_floors" },
                { label: "Kitchenette", value: "kitchenette" },
                {
                    label: "Wheelchair Accessible",
                    value: "wheelchair_accessible",
                },
                { label: "Mountain View", value: "mountain_view" },
                { label: "Beach View", value: "beach_view" },
                { label: "Beach Chairs", value: "beach_chairs" },
                { label: "Outdoor Kitchen", value: "outdoor_kitchen" },
                { label: "Sound System", value: "sound_system" },
                { label: "Computer", value: "computer" },
                { label: "Walk-in Closet", value: "walk_in_closet" },
                { label: "Mini Fridge", value: "mini_fridge" },
                { label: "Lounge Area", value: "lounge_area" },
                { label: "Dressing Area", value: "dressing_area" },
                { label: "Desk", value: "desk" },
                { label: "Reading Nook", value: "reading_nook" },
                { label: "Fireplace", value: "fireplace" },
                { label: "TV", value: "tv" },
                { label: "Smart TV", value: "smart_tv" },
                { label: "DVD Player", value: "dvd_player" },
                { label: "Wireless Internet", value: "wireless_internet" },
                { label: "Hammock", value: "hammock" },
                { label: "Triple Vanity", value: "triple_vanity" },
                { label: "Dual Vanity", value: "dual_vanity" },
                { label: "Balcony", value: "balcony" },
                { label: "Terrace", value: "terrace" },
                { label: "En Suite Bathroom", value: "en_suite_bathroom" },
                { label: "Bidet", value: "bidet" },
                { label: "Rain Shower", value: "rain_shower" },
                {
                    label: "Shower Bathtub Combo",
                    value: "shower_bathtub_combo",
                },
                { label: "Stand Alone Bathtub", value: "stand_alone_bathtub" },
                {
                    label: "Stand Alone Jetted Bathtub",
                    value: "stand_alone_jetted_bathtub",
                },
                { label: "Stand Alone Shower", value: "stand_alone_shower" },
                {
                    label: "Stand Alone Steam Shower",
                    value: "stand_alone_steam_shower",
                },
                {
                    label: "Stand Alone Rain Shower",
                    value: "stand_alone_rain_shower",
                },
                { label: "Jetted Tub", value: "jetted_tub" },
                { label: "Heated Towel Rack", value: "heated_towel_rack" },
                { label: "Step Free Access", value: "step_free_access" },
                {
                    label: "Accessible Height Bed",
                    value: "accessible_height_bed",
                },
                {
                    label: "Accessible Height Toilet",
                    value: "accessible_height_toilet",
                },
                { label: "Ceiling Hoist", value: "ceiling_hoist" },
                {
                    label: "Disabled Parking Spot",
                    value: "disabled_parking_spot",
                },
                {
                    label: "Electric Profiling Bed",
                    value: "electric_profiling_bed",
                },
                {
                    label: "Grab Rails in Shower",
                    value: "grab_rails_in_shower",
                },
                {
                    label: "Grab Rails in Toilet",
                    value: "grab_rails_in_toilet",
                },
                {
                    label: "Handheld Shower Head",
                    value: "handheld_shower_head",
                },
                { label: "Mobile Hoist", value: "mobile_hoist" },
                { label: "Pool Hoist", value: "pool_hoist" },
                { label: "Roll-in Shower", value: "rollin_shower" },
                { label: "Shower Chair", value: "shower_chair" },
                {
                    label: "Tub with Shower Bench",
                    value: "tub_with_shower_bench",
                },
                {
                    label: "Wide Clearance to Bed",
                    value: "wide_clearance_to_bed",
                },
                {
                    label: "Wide Clearance to Shower",
                    value: "wide_clearance_to_shower",
                },
                {
                    label: "Wide Clearance to Toilet",
                    value: "wide_clearance_to_toilet",
                },
                { label: "Wide Doorway", value: "wide_doorway" },
            ],
            form: new form({
                id: null,
                room_number: "",
                beds: [
                    {
                        type: "",
                        quantity: 0,
                    },
                ],
                room_amenities: [
                    {
                        type: "",
                        quantity: 0,
                    },
                ],
                room_type: "",
                is_private: false,
                metadata: null,
            }),
            isLoading: false,
            savedRooms: [],
            deletingRoom: [],
            editingRoom: [],
            scopes: [
                { label: "Per Night", value: "PER_NIGHT" },
                { label: "Per Stay", value: "PER_STAY" },
                { label: "Per Guest Per Night", value: "PER_GUEST_PER_NIGHT" },
                { label: "Per Guest", value: "PER_GUEST" },
            ],
            airBnbTypes: [
                {
                    label: "Pass Through Resort Room",
                    value: "PASS_THROUGH_RESORT_FEE",
                },
                {
                    label: "Pass Through Management Room",
                    value: "PASS_THROUGH_MANAGEMENT_FEE",
                },
                {
                    label: "Pass Through Community Room",
                    value: "PASS_THROUGH_COMMUNITY_FEE",
                },
                {
                    label: "Pass Through Linen Room",
                    value: "PASS_THROUGH_LINEN_FEE",
                },
                {
                    label: "Pass Through Electricity Room",
                    value: "PASS_THROUGH_ELECTRICITY_FEE",
                },
                {
                    label: "Pass Through Water Room",
                    value: "PASS_THROUGH_WATER_FEE",
                },
                {
                    label: "Pass Through Heating Room",
                    value: "PASS_THROUGH_HEATING_FEE",
                },
                {
                    label: "Pass Through Air Conditioning Room",
                    value: "PASS_THROUGH_AIR_CONDITIONING_FEE",
                },
                {
                    label: "Pass Through Utility Room",
                    value: "PASS_THROUGH_UTILITY_FEE",
                },
                {
                    label: "Pass Through Pet Room",
                    value: "PASS_THROUGH_PET_FEE",
                },
                {
                    label: "Pass Through Cleaning Room",
                    value: "PASS_THROUGH_CLEANING_FEE",
                },
                {
                    label: "Pass Through Short Term Cleaning Room",
                    value: "PASS_THROUGH_SHORT_TERM_CLEANING_FEE",
                },
                {
                    label: "Pass Through Security Deposit",
                    value: "PASS_THROUGH_SECURITY_DEPOSIT",
                },
                {
                    label: "Pass Through Hotel Tax",
                    value: "PASS_THROUGH_HOTEL_TAX",
                },
                {
                    label: "Pass Through Lodging Tax",
                    value: "PASS_THROUGH_LODGING_TAX",
                },
                {
                    label: "Pass Through Room Tax",
                    value: "PASS_THROUGH_ROOM_TAX",
                },
                {
                    label: "Pass Through Tourist Tax",
                    value: "PASS_THROUGH_TOURIST_TAX",
                },
                {
                    label: "Pass Through Transient Occupancy Tax",
                    value: "PASS_THROUGH_TRANSIENT_OCCUPANCY_TAX",
                },
                {
                    label: "Pass Through Sales Tax",
                    value: "PASS_THROUGH_SALES_TAX",
                },
                {
                    label: "Pass Through VAT Tax",
                    value: "PASS_THROUGH_VAT_TAX",
                },
                {
                    label: "Pass Through Tourism Assessment Room",
                    value: "PASS_THROUGH_TOURISM_ASSESSMENT_FEE",
                },
                {
                    label: "Airbnb Collected Tax",
                    value: "AIRBNB_COLLECTED_TAX",
                },
            ],
            bookingDotComTypes: [
                { label: "Airport Shuttle Room", value: "AIRPORT_SHUTTLE_FEE" },
                {
                    label: "Air Conditioning Room",
                    value: "AIR_CONDITIONING_FEE",
                },
                { label: "City Tax", value: "CITY_TAX" },
                { label: "Club Card Room", value: "CLUB_CARD_FEE" },
                { label: "Conservation Room", value: "CONSERVATION_FEE" },
                { label: "Credit Card Room", value: "CREDIT_CARD_FEE" },
                { label: "Destination Room", value: "DESTINATION_FEE" },
                { label: "Electricity Room", value: "ELECTRICITY_FEE" },
                { label: "Environment Room", value: "ENVIRONMENT_FEE" },
                { label: "Final Cleaning Room", value: "FINAL_CLEANING_FEE" },
                { label: "Gas Room", value: "GAS_FEE" },
                {
                    label: "Goods and Services Tax",
                    value: "GOODS_AND_SERVICES_TAX",
                },
                { label: "Government Tax", value: "GOVERNMENT_TAX" },
                { label: "Heating Room", value: "HEATING_FEE" },
                { label: "Heritage Room", value: "HERITAGE_FEE" },
                { label: "Hot Spring Tax", value: "HOT_SPRING_TAX" },
                { label: "Housekeeping Room", value: "HOUSEKEEPING_FEE" },
                { label: "Kitchen Linen Room", value: "KITCHEN_LINEN_FEE" },
                { label: "Linen Room", value: "LINEN_FEE" },
                { label: "Linen Package Room", value: "LINEN_PACKAGE_FEE" },
                { label: "Local Council Tax", value: "LOCAL_COUNCIL_TAX" },
                { label: "Municipality Room", value: "MUNICIPALITY_FEE" },
                { label: "Oil Room", value: "OIL_FEE" },
                { label: "Pets Room", value: "PETS_FEE" },
                { label: "Residential Tax", value: "RESIDENTIAL_TAX" },
                { label: "Resort Room", value: "RESORT_FEE" },
                {
                    label: "Sauna Fitness Facilities Tax",
                    value: "SAUNA_FITNESS_FACILITIES_TAX",
                },
                { label: "Sea Plane Room", value: "SEA_PLANE_FEE" },
                { label: "Service Charge Room", value: "SERVICE_CHARGE_FEE" },
                { label: "Shuttle Boat Room", value: "SHUTTLE_BOAT_FEE" },
                { label: "Ski Pass Room", value: "SKI_PASS_FEE" },
                { label: "Spa Tax", value: "SPA_TAX" },
                { label: "Towel Room", value: "TOWEL_FEE" },
                { label: "Transfer Room", value: "TRANSFER_FEE" },
                { label: "Transit Room", value: "TRANSIT_FEE" },
                { label: "VAT", value: "VAT" },
                { label: "Visa Support Room", value: "VISA_SUPPORT_FEE" },
                { label: "Water Room", value: "WATER_FEE" },
                { label: "Water Park Room", value: "WATER_PARK_FEE" },
                { label: "Wood Room", value: "WOOD_FEE" },
                { label: "Wristband Room", value: "WRISTBAND_FEE" },
            ],
            vrboTypes: [
                { label: "Additional Bed", value: "ADDITIONAL_BED" },
                { label: "Administrative", value: "ADMINISTRATIVE" },
                { label: "Air Conditioning", value: "AIR_CONDITIONING" },
                { label: "Arrival Early", value: "ARRIVAL_EARLY" },
                { label: "Arrival Late", value: "ARRIVAL_LATE" },
                {
                    label: "Association Property",
                    value: "ASSOCIATION_PROPERTY",
                },
                { label: "Baby Bed", value: "BABY_BED" },
                { label: "Booking Early", value: "BOOKING_EARLY" },
                { label: "Booking Late", value: "BOOKING_LATE" },
                { label: "Class", value: "CLASS" },
                { label: "Club", value: "CLUB" },
                { label: "Concierge", value: "CONCIERGE" },
                { label: "Departure Early", value: "DEPARTURE_EARLY" },
                { label: "Departure Late", value: "DEPARTURE_LATE" },
                { label: "Electricity", value: "ELECTRICITY" },
                { label: "Equipment", value: "EQUIPMENT" },
                { label: "Food", value: "FOOD" },
                { label: "Gardening", value: "GARDENING" },
                { label: "Gas", value: "GAS" },
                { label: "Heating", value: "HEATING" },
                { label: "High Chair", value: "HIGH_CHAIR" },
                { label: "Hot Tub", value: "HOT_TUB" },
                { label: "Internet", value: "INTERNET" },
                { label: "Labor", value: "LABOR" },
                { label: "Laundry", value: "LAUNDRY" },
                { label: "Linens", value: "LINENS" },
                { label: "Linens Bath", value: "LINENS_BATH" },
                { label: "Linens Bed", value: "LINENS_BED" },
                { label: "Management", value: "MANAGEMENT" },
                { label: "Oil", value: "OIL" },
                {
                    label: "On-Site Payment Method",
                    value: "ON_SITE_PAYMENT_METHOD",
                },
                { label: "Parking", value: "PARKING" },
                { label: "Pet", value: "PET" },
                { label: "Phone", value: "PHONE" },
                { label: "Pool", value: "POOL" },
                { label: "Pool Heating", value: "POOL_HEATING" },
                { label: "Rent", value: "RENT" },
                { label: "Resort", value: "RESORT" },
                { label: "Spa", value: "SPA" },
                { label: "Tax", value: "TAX" },
                { label: "Toiletries", value: "TOILETRIES" },
                { label: "Tour", value: "TOUR" },
                { label: "Transportation", value: "TRANSPORTATION" },
                { label: "Utensils Cleaning", value: "UTENSILS_CLEANING" },
                { label: "Utensils Food", value: "UTENSILS_FOOD" },
                { label: "Vehicle", value: "VEHICLE" },
                { label: "Waiver Damage", value: "WAIVER_DAMAGE" },
                { label: "Water", value: "WATER" },
                { label: "Water Craft", value: "WATER_CRAFT" },
                { label: "Water Craft Mooring", value: "WATER_CRAFT_MOORING" },
                { label: "Water Drinking", value: "WATER_DRINKING" },
                { label: "Wood", value: "WOOD" },
            ],
        };
    },
    methods: {
        editRoom(data) {
            console.log("testing 1234...", data.beds);
            this.form.fill({
                id: data.id,
                room_number: data.room_number,
                beds: data.beds || [],
                room_amenities: data.room_amenities || [],
                room_type: data.room_type,
                is_private: data.is_private == 1 ? true : false,
                metadata: data.metadata || null,
            });
        },
        addBed() {
            this.form.beds.push({
                type: "king_bed",
                quantity: 0,
            });
        },
        removeBed(index) {
            this.form.beds.splice(index, 1);
        },
        addAmenity() {
            this.form.room_amenities.push({
                type: "string",
                quantity: 0,
            });
        },
        removeAmenity(index) {
            this.form.room_amenities.splice(index, 1);
        },
        getRooms() {
            axios
                .get("/api/airbnb/rooms?listing_id=" + this.listingId)
                .then((response) => {
                    const savedRooms = response?.data || [];
                    this.savedRooms = savedRooms.map((i) => {
                        return {
                            ...i,
                            beds: i.beds ? JSON.parse(i.beds) : {},
                            room_amenities: i.room_amenities
                                ? JSON.parse(i.room_amenities)
                                : {},
                        };
                    });
                })
                .catch((error) => {
                    console.error("Error fetching rooms:", error);
                });
        },
        saveRoom() {
            let self = this;
            this.isLoading = true;
            this.form.listing_id = this.listingId;
            this.form
                .post("/api/airbnb/rooms")
                .then((response) => {
                    if (response?.data) {
                        this.isLoading = false;
                        const data = response.data;

                        const roomIndex = self.savedRooms.findIndex(
                            (room) => room.room_number === data.room_number
                        );

                        const roomData = {
                            id: data.id,
                            airbnb_room_id: data.airbnb_room_id,
                            room_number: data.room_number,
                            beds: data.beds ? JSON.parse(data.beds) : {},
                            room_amenities: data.room_amenities
                                ? JSON.parse(data.room_amenities)
                                : {},
                            room_type: data.room_type,
                            is_private: data.is_private,
                            metadata: data.metadata || null,
                        };

                        if (roomIndex !== -1) {
                            self.savedRooms[roomIndex] = roomData;
                        } else {
                            self.savedRooms.push(roomData);
                        }

                        this.$showSuccessToast("Room added successfully!");
                    } else {
                        this.isLoading = false;
                        this.$showErrorToast(
                            "An error occurred. Please try again."
                        );
                    }
                })
                .catch((error) => {
                    const errorMessage =
                        error?.details?.errors?.error_message ||
                        "Something went wrong!";

                    console.error("Error:", errorMessage);
                    this.$showErrorToast(errorMessage);
                    this.isLoading = false;
                });
        },
        updateRoom() {
            let self = this;
            this.isLoading = true;
            this.form.listing_id = this.listingId;
            const roomId = this.form.id;

            this.form
                .put(`/api/airbnb/rooms/${roomId}`)
                .then((response) => {
                    if (response?.data) {
                        this.isLoading = false;
                        const data = response.data;

                        const roomIndex = self.savedRooms.findIndex(
                            (room) => room.room_number === data.room_number
                        );

                        const roomData = {
                            id: data.id,
                            airbnb_room_id: data.airbnb_room_id,
                            room_number: data.room_number,
                            beds: data.beds ? JSON.parse(data.beds) : {},
                            room_amenities: data.room_amenities
                                ? JSON.parse(data.room_amenities)
                                : {},
                            room_type: data.room_type,
                            is_private: data.is_private,
                            metadata: data.metadata || null,
                        };

                        if (roomIndex !== -1) {
                            self.savedRooms[roomIndex] = roomData;
                        }

                        this.$showSuccessToast("Room updated successfully!");
                    } else {
                        this.isLoading = false;
                        this.$showErrorToast(
                            "An error occurred. Please try again."
                        );
                    }
                })
                .catch((error) => {
                    const errorMessage =
                        error?.response?.data?.error ||
                        error?.response?.data ||
                        "Something went wrong!";

                    console.error("Error:", errorMessage);
                    this.$showErrorToast(errorMessage);
                    this.isLoading = false;
                });
        },
        deleteRoom(index) {
            this.deletingRoom[index] = true;
            const room = this.savedRooms[index];
            axios
                .delete("/api/airbnb/rooms/" + room.id)
                .then(() => {
                    this.savedRooms.splice(index, 1);
                    this.$showSuccessToast("Room Deleted!");
                    this.deletingRoom[index] = false;
                })
                .catch((error) => {
                    console.error(
                        "Error in deleting room:",
                        error?.response?.data?.error || error?.response?.data
                    );
                    this.$showErrorToast(
                        error?.response?.data?.error ||
                            error?.response?.data ||
                            "Something went wrong!"
                    );
                    this.deletingRoom[index] = false;
                });
        },
    },
    mounted() {
        this.getRooms();
    },
};
</script>
