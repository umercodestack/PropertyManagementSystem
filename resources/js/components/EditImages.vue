<template>
    <div class="image-upload-container">
        <button @click="triggerFileUpload" class="btn btn-success mb-3">
            + Add photo...
        </button>
        <img
            :src="'/assets/icons/airbnb_favicon.ico'"
            height="15"
            width="15"
            style="margin-left: 5px; margin-bottom: 2px"
        />
        <img
            :src="'/assets/icons/bookingcom_favicon.ico'"
            height="15"
            width="15"
            style="margin-left: 5px; margin-bottom: 2px"
        />
        <img
            :src="'/assets/icons/vrbo_favicon.ico'"
            height="15"
            width="15"
            style="margin-left: 5px; margin-bottom: 2px"
        />
        <input
            type="file"
            ref="fileInput"
            class="d-none"
            @change="handleImageUpload"
        />

        <div
            v-for="(image, index) in uploadedImages"
            :key="index"
            class="image-card mb-4"
        >
            <div class="row align-items-center">
                <div class="col-md-2">
                    <img
                        :src="image.url"
                        alt="Uploaded Image"
                        class="uploaded-image"
                    />
                </div>
                <div class="col-md-4">
                    <label>Description</label>
                    <textarea
                        v-model="image.description"
                        class="form-control"
                        placeholder="Enter a description for the image"
                    ></textarea>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <button
                        style="margin-right: 10px"
                        class="btn btn-primary mr-2"
                        @click="saveImage(index)"
                        :disabled="isLoading"
                    >
                        <span
                            v-if="isLoading"
                            class="spinner-border spinner-border-sm"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        <span v-if="!isLoading">Save</span>
                        <span v-else>Saving...</span>
                    </button>

                    <button class="btn btn-danger" @click="deleteImage(index)">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div v-if="savedImages.length" class="saved-images-section mt-5">
            <h4>Saved Images</h4>
            <div
                v-for="(image, index) in savedImages"
                :key="index"
                class="image-card mb-4"
            >
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img
                            :src="image.url"
                            alt="Saved Image"
                            class="uploaded-image"
                        />
                    </div>
                    <div v-if="image.description" class="col-md-4">
                        <label>Description</label>
                        <p>{{ image.description }}</p>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button
                            :disabled="deletingImage[index]"
                            class="btn btn-danger"
                            @click="deleteSavedImage(index)"
                        >
                            <span
                                v-if="deletingImage[index]"
                                class="spinner-border spinner-border-sm"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            <span v-if="!deletingImage[index]">Delete</span>
                            <span v-else>Delete...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            uploadedImages: [],
            savedImages: [],
            isLoading: false,
            deletingImage: [],
        };
    },
    props: ["listingId"],
    methods: {
        triggerFileUpload() {
            this.$refs.fileInput.click();
        },
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.uploadedImages.push({
                        file: file,
                        url: e.target.result,
                        description: "",
                    });
                };
                reader.readAsDataURL(file);
            }
        },
        saveImage(index) {
            this.isLoading = true;
            const image = this.uploadedImages[index];

            const formData = new FormData();
            formData.append("listing_id", this.listingId);
            formData.append("image", image.file);
            formData.append("description", image.description);

            axios
                .post("/api/airbnb/image", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    if (response.data?.data) {
                        this.savedImages.push(response.data?.data);
                        this.uploadedImages.splice(index, 1);
                        this.isLoading = false;
                        this.$showSuccessToast("Image Uploaded!");
                    } else {
                        this.$showErrorToast("Something Went Wrong!");
                        this.isLoading = false;
                    }
                })
                .catch((error) => {
                    console.error(
                        "Error in uploading image:",
                        error?.response?.data?.error || error?.response?.data
                    );
                    this.$showErrorToast(
                        error?.response?.data?.error ||
                            error?.response?.data ||
                            "Something went wrong!"
                    );
                    this.isLoading = false;
                });
        },
        deleteImage(index) {
            this.uploadedImages.splice(index, 1);
        },
        deleteSavedImage(index) {
            this.deletingImage[index] = true;
            const image = this.savedImages[index];
            axios
                .delete("/api/airbnb/image/" + image.id)
                .then(() => {
                    this.savedImages.splice(index, 1);
                    this.$showSuccessToast("Delete Image!");
                    this.deletingImage[index] = false;
                })
                .catch((error) => {
                    console.error(
                        "Error in deleting image:",
                        error?.response?.data?.error || error?.response?.data
                    );
                    this.$showErrorToast(
                        error?.response?.data?.error ||
                            error?.response?.data ||
                            "Something went wrong!"
                    );
                    this.deletingImage[index] = false;
                });
        },
        fetchImages() {
            axios
                .get("/api/airbnb/images/" + this.listingId)
                .then((response) => {
                    this.savedImages = response.data?.data || response.data;
                })
                .catch((error) => {
                    console.error("Error fetching properties:", error);
                });
        },
    },
    mounted() {
        this.fetchImages();
    },
};
</script>

<style scoped>
.image-upload-container {
    width: 100%;
    max-width: 100%;
}

.uploaded-image {
    width: 100%;
    height: auto;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.btn-success {
    background-color: #28a745;
    color: white;
    padding: 10px;
    font-weight: bold;
}

textarea {
    resize: none;
    height: 80px;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.saved-images-section {
    border-top: 1px solid #ccc;
    padding-top: 20px;
}

p {
    margin: 0;
}
</style>
