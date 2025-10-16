<template>
    <div>
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Listings</h3>
                    <div class="nk-block-des text-soft">
                        <p>
                            You have total
                            {{ listings?.pagination?.total }} listings.
                        </p>
                    </div>
                </div>
                <div class="nk-block-head-content">
                    <div class="toggle-wrap nk-block-tools-toggle">
                        <div
                            class="toggle-expand-content"
                            data-content="pageMenu"
                        >
                            <ul class="nk-block-tools g-3">
                                <li class="nk-block-tools-opt">
                                    <div class="dropdown">
                                        <router-link
                                            :to="{ name: 'AddListing' }"
                                            class="btn btn-icon btn-primary"
                                        >
                                            <em class="icon ni ni-plus"></em>
                                        </router-link>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-bordered card-preview">
            <div class="card-body">
                <!-- <v-data-table
                    :headers="headers"
                    :items="listings?.data"
                    :server-items-length="listings?.pagination?.total"
                    class="elevation-2"
                    :loading="loading"
                    density="compact"
                    :options.sync="options"
                    :search="search"
                    :footer-props="{
                        showFirstLastPage: true,
                        'items-per-page-options': [10, 50, 100, 500, -1],
                    }"
                >
                    <template v-slot:item.created_at="{ item }">
                        <span>{{ $formatDate(item.created_at) }}</span>
                    </template>
                </v-data-table> -->
                <table
                    class="datatable-init-export nowrap table"
                    data-export-title="Export"
                >
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Listing ID</th>
                            <th>Listing Name</th>
                            <th>Create At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in listings" :key="item.id">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item.listing_id }}</td>
                            <td>{{ item.name }}</td>
                            <td>{{ $formatDate(item.created_at) }}</td>
                            <td>
                                <router-link
                                    :to="{
                                        name: 'EditListing',
                                        params: { id: item.listing_id },
                                    }"
                                    class="btn btn-primary btn-sm"
                                >
                                    <em class="icon ni ni-pen"></em>
                                </router-link>
                                <button
                                    @click="deleteListing(item.listing_id)"
                                    class="btn btn-danger btn-sm ml-2"
                                >
                                    <em class="icon ni ni-trash"></em>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            listings: [],
            isMenuExpanded: false,
            curpage: 1,
            search: "",
            itemsPerPage: 10,
            loading: false,
            options: {},
            sortBy: "",
            sortDesc: "",
            headers: [
                { title: "Name", value: "name" },
                { title: "Listing Type", value: "listing_type" },
                { title: "Room Type", value: "room_type" },
                { title: "Created At", value: "created_at" },
            ],
        };
    },
    mounted() {
        this.fetchListings();
    },
    methods: {
        fetchListings() {
            axios
                .get("/api/airbnb/listings")
                .then((response) => {
                    this.listings = response.data?.data || response?.data;
                    console.log("listings...", this.listings[0]["title"]);
                })
                .catch((error) => {
                    console.error("Error fetching listings:", error);
                });
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
            });
        },
        toggleMenu() {
            this.isMenuExpanded = !this.isMenuExpanded;
        },
        deleteListing(id) {
            if (
                confirm(
                    "Are you sure you want to delete this listing? All related data, including images, calendar records, rooms, and settings, will also be deleted."
                )
            ) {
                axios
                    .delete("/api/airbnb/listings/" + id)
                    .then((response) => {
                        if (response?.data) {
                            this.$showSuccessToast("Listing Deleted!");
                            this.listings = this.listings.filter(
                                (i) => i.listing_id != id
                            );
                        }
                    })
                    .catch((error) => {
                        console.error(
                            "Error in deleting listing:",
                            error?.response?.data?.error
                        );
                        this.$showErrorToast(
                            error?.response?.data?.error ||
                                "Something went wrong!"
                        );
                    });
            }
        },
    },
};
</script>

<style scoped></style>
