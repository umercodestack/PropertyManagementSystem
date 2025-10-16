<template>
  <form @submit.prevent="updateDescription">
    <div class="row">
      <!-- Name -->
      <!-- <div class="col-md-12 mb-3">
        <label for="name">Name</label>
        <input type="text" id="name" class="form-control" v-model="form.name" />
        <div
          class="error-message text-danger"
          v-if="form.errors.has('name')"
          v-html="form.errors.get('name')"
        />
      </div> -->

      <!-- Description -->
      <!-- <div class="col-md-12 mb-3">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    class="form-control"
                    v-model="form.description"
                    rows="1"
                ></textarea>
                <div
                    class="error-message text-danger"
                    v-if="form.errors.has('description')"
                    v-html="form.errors.get('description')"
                />
            </div> -->

      <!-- Summary -->
      <div class="col-md-12 mb-3">
        <label for="summary">Summary</label>
        <textarea
          id="summary"
          class="form-control"
          v-model="form.summary"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('summary')"
          v-html="form.errors.get('summary')"
        />
      </div>

      <!-- Space -->
      <div class="col-md-6 mb-3">
        <label for="space">Space</label>
        <textarea
          id="space"
          class="form-control"
          v-model="form.space"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('space')"
          v-html="form.errors.get('space')"
        />
      </div>

      <!-- Access -->
      <div class="col-md-6 mb-3">
        <label for="access">Access</label>
        <textarea
          id="access"
          class="form-control"
          v-model="form.access"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('access')"
          v-html="form.errors.get('access')"
        />
      </div>

      <!-- Interaction -->
      <div class="col-md-6 mb-3">
        <label for="interaction">Interaction</label>
        <textarea
          id="interaction"
          class="form-control"
          v-model="form.interaction"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('interaction')"
          v-html="form.errors.get('interaction')"
        />
      </div>

      <!-- Neighbourhood Overview -->
      <div class="col-md-6 mb-3">
        <label for="neighborhood_overview">Neighbourhood Overview</label>
        <textarea
          id="neighborhood_overview"
          class="form-control"
          v-model="form.neighborhood_overview"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('neighborhood_overview')"
          v-html="form.errors.get('neighborhood_overview')"
        />
      </div>

      <!-- Transit -->
      <div class="col-md-6 mb-3">
        <label for="transit">Transit</label>
        <textarea
          id="transit"
          class="form-control"
          v-model="form.transit"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('transit')"
          v-html="form.errors.get('transit')"
        />
      </div>

      <!-- Notes -->
      <div class="col-md-6 mb-3">
        <label for="notes">Notes</label>
        <textarea
          id="notes"
          class="form-control"
          v-model="form.notes"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('notes')"
          v-html="form.errors.get('notes')"
        />
      </div>

      <!-- House Rules -->
      <div class="col-md-6 mb-3">
        <label for="house_rules">House Rules</label>
        <textarea
          id="house_rules"
          class="form-control"
          v-model="form.house_rules"
          rows="3"
        ></textarea>
        <div
          class="error-message text-danger"
          v-if="form.errors.has('house_rules')"
          v-html="form.errors.get('house_rules')"
        />
      </div>

      <!-- Submit Button -->
      <div class="text-right">
        <button type="submit" class="btn btn-primary text-light" :disabled="isLoading">
          <span
            v-if="isLoading"
            class="spinner-border spinner-border-sm"
            role="status"
            aria-hidden="true"
          ></span>
          <span v-if="!isLoading">Update Description</span>
          <span v-else>Updating...</span>
        </button>
      </div>
    </div>
  </form>
</template>

<script>
import axios from "axios";
export default {
  props: ["listingId", "listingName", "description"],
  data() {
    return {
      isTextareaLocked: false,
      isLoading: false,
      form: new form({
        description: "",
        name: "",
        summary: "",
        space: "",
        access: "",
        interaction: "",
        neighborhood_overview: "",
        transit: "",
        notes: "",
        house_rules: "",
        locale: "EN",
      }),
    };
  },
  mounted() {
    if (this.description && this.description?.listing_id) {
      this.form.fill(this.description);
    }
    this.form.name = this.listingName || null
  },
  methods: {
    checkCharacterLimit() {
      if (this.form.summary.length >= 500) {
        this.form.summary = this.form.summary.substring(0, 500);
      }
    },
    updateDescription() {
      this.isLoading = true;
      this.form
        .put("/api/airbnb/listings/description/" + this.listingId)
        .then((response) => {
          this.isLoading = false;
          this.$showSuccessToast("Description Updated!");
        })
        .catch((error) => {
          console.error("Error in updating property:", error?.response?.data?.error);
          this.$showErrorToast(error?.response?.data?.error || "Something went wrong!");
          this.isLoading = false;
        });
    },
  },
};
</script>
