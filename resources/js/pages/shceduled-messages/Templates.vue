<template>
    <div>
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Templates</h3>
                    <div class="nk-block-des text-soft">
                        <p>
                            You have total
                            {{ templates?.pagination?.total }} templates.
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
                                        <button
                                            @click="
                                                (editData = null),
                                                    (addTemplateDialog = true)
                                            "
                                            type="button"
                                            class="text-white btn btn-icon btn-primary"
                                        >
                                            <em class="icon ni ni-plus"></em>
                                        </button>
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
                    :items="templates?.data"
                    :server-items-length="templates?.pagination?.total"
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
                            <th>Template Name</th>
                            <th>Messages</th>
                            <th>Schedule</th>
                            <th>Listings</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in templates" :key="item.id">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item.name }}</td>
                            <td>{{ item?.message?.length > 20 ? item.message?.substring(0, 20) + "..." : item?.message }}</td>
                            <td>{{ getDisplayMessage(item) }}</td>
                            <td>{{ item.listings?.length || 0 }}</td>
                            <td>
                                <button
                                    @click="editTemplate(item)"
                                    class="btn btn-primary btn-sm ml-2"
                                >
                                    <em class="icon ni ni-pen"></em>
                                </button>
                                <button
                                    @click="deleteTemplate(item.id)"
                                    class="btn btn-danger btn-sm ml-2"
                                >
                                    <em class="icon ni ni-trash"></em>
                                </button>
                                
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <AddEditTemplateModal
                :editData="editData"
                :showModal="addTemplateDialog"
                @fetchTemplates="fetchTemplates"
                @setAddEditModal="setAddEditModal"
            />
        </div>
    </div>
</template>

<script>
import axios from "axios";
import AddEditTemplateModal from "@/components/AddEditTemplateModal.vue";

export default {
    data() {
        return {
            editData: null,
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
            addTemplateDialog: false,
            templates: [],
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
                { title: "Template Type", value: "template_type" },
                { title: "Room Type", value: "room_type" },
                { title: "Created At", value: "created_at" },
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
    components: {
        AddEditTemplateModal,
    },
    mounted() {
        this.fetchTemplates();
    },
    methods: {
        editTemplate(item) {
            this.editData = item;
            this.addTemplateDialog = true;
        },
        getDisplayMessage(item) {
            let dayLabel =
                this.days.find((day) => day.value === item.day)?.label || "";
            let timeLabel =
                this.times.find((time) => time.value === item.time)?.label ||
                "";
            let actionLabel = item.action.replace(/_/g, " ").toLowerCase();
            if (item.action === "check_in" || item.action === "check_out") {
                return `${dayLabel} ${actionLabel} at ${timeLabel}`;
            } else {
                const value = item.when_to_send;
                return (
                    this.timings.find((i) => i.value == value)?.label +
                    " booking"
                );
            }
        },
        setAddEditModal(value) {
            this.addTemplateDialog = value || false;
        },
        fetchTemplates() {
            axios
                .get("/api/templates")
                .then((response) => {
                    this.templates = response.data?.data || response?.data;
                    console.log("templates...", this.templates[0]["title"]);
                })
                .catch((error) => {
                    console.error("Error fetching templates:", error);
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
        deleteTemplate(id) {
            if (
                confirm(
                    "Are you sure you want to delete this template?"
                )
            ) {
                axios
                    .delete("/api/templates/" + id)
                    .then((response) => {
                        if (response?.data) {
                            this.$showSuccessToast("Template Deleted!");
                            this.templates = this.templates.filter(
                                (i) => i.id != id
                            );
                        }
                    })
                    .catch((error) => {
                        console.error(
                            "Error in deleting template:",
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

