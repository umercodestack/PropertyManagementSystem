@extends('Admin.layouts.app')
@section('content')

<style>
    #respect_house_rules_1:checked ~ section [for=respect_house_rules_1] svg, #respect_house_rules_2:checked ~ section [for=respect_house_rules_1] svg, #respect_house_rules_2:checked ~ section [for=respect_house_rules_2] svg, #respect_house_rules_3:checked ~ section [for=respect_house_rules_1] svg, #respect_house_rules_3:checked ~ section [for=respect_house_rules_2] svg, #respect_house_rules_3:checked ~ section [for=respect_house_rules_3] svg, #respect_house_rules_4:checked ~ section [for=respect_house_rules_1] svg, #respect_house_rules_4:checked ~ section [for=respect_house_rules_2] svg, #respect_house_rules_4:checked ~ section [for=respect_house_rules_3] svg, #respect_house_rules_4:checked ~ section [for=respect_house_rules_4] svg, #respect_house_rules_5:checked ~ section [for=respect_house_rules_1] svg, #respect_house_rules_5:checked ~ section [for=respect_house_rules_2] svg, #respect_house_rules_5:checked ~ section [for=respect_house_rules_3] svg, #respect_house_rules_5:checked ~ section [for=respect_house_rules_4] svg, #respect_house_rules_5:checked ~ section [for=respect_house_rules_5] svg {
  transform: scale(1);
}

#respect_house_rules_1:checked ~ section [for=respect_house_rules_1] svg path, #respect_house_rules_2:checked ~ section [for=respect_house_rules_1] svg path, #respect_house_rules_2:checked ~ section [for=respect_house_rules_2] svg path, #respect_house_rules_3:checked ~ section [for=respect_house_rules_1] svg path, #respect_house_rules_3:checked ~ section [for=respect_house_rules_2] svg path, #respect_house_rules_3:checked ~ section [for=respect_house_rules_3] svg path, #respect_house_rules_4:checked ~ section [for=respect_house_rules_1] svg path, #respect_house_rules_4:checked ~ section [for=respect_house_rules_2] svg path, #respect_house_rules_4:checked ~ section [for=respect_house_rules_3] svg path, #respect_house_rules_4:checked ~ section [for=respect_house_rules_4] svg path, #respect_house_rules_5:checked ~ section [for=respect_house_rules_1] svg path, #respect_house_rules_5:checked ~ section [for=respect_house_rules_2] svg path, #respect_house_rules_5:checked ~ section [for=respect_house_rules_3] svg path, #respect_house_rules_5:checked ~ section [for=respect_house_rules_4] svg path, #respect_house_rules_5:checked ~ section [for=respect_house_rules_5] svg path {
  fill: #FFBB00;
  stroke: #cc9600;
}

#communication_1:checked ~ section [for=communication_1] svg, #communication_2:checked ~ section [for=communication_1] svg, #communication_2:checked ~ section [for=communication_2] svg, #communication_3:checked ~ section [for=communication_1] svg, #communication_3:checked ~ section [for=communication_2] svg, #communication_3:checked ~ section [for=communication_3] svg, #communication_4:checked ~ section [for=communication_1] svg, #communication_4:checked ~ section [for=communication_2] svg, #communication_4:checked ~ section [for=communication_3] svg, #communication_4:checked ~ section [for=communication_4] svg, #communication_5:checked ~ section [for=communication_1] svg, #communication_5:checked ~ section [for=communication_2] svg, #communication_5:checked ~ section [for=communication_3] svg, #communication_5:checked ~ section [for=communication_4] svg, #communication_5:checked ~ section [for=communication_5] svg {
      transform: scale(1);
    }

    #communication_1:checked ~ section [for=communication_1] svg path, #communication_2:checked ~ section [for=communication_1] svg path, #communication_2:checked ~ section [for=communication_2] svg path, #communication_3:checked ~ section [for=communication_1] svg path, #communication_3:checked ~ section [for=communication_2] svg path, #communication_3:checked ~ section [for=communication_3] svg path, #communication_4:checked ~ section [for=communication_1] svg path, #communication_4:checked ~ section [for=communication_2] svg path, #communication_4:checked ~ section [for=communication_3] svg path, #communication_4:checked ~ section [for=communication_4] svg path, #communication_5:checked ~ section [for=communication_1] svg path, #communication_5:checked ~ section [for=communication_2] svg path, #communication_5:checked ~ section [for=communication_3] svg path, #communication_5:checked ~ section [for=communication_4] svg path, #communication_5:checked ~ section [for=communication_5] svg path {
      fill: #FFBB00;
      stroke: #cc9600;
    }

    #cleanliness_1:checked ~ section [for=cleanliness_1] svg, #cleanliness_2:checked ~ section [for=cleanliness_1] svg, #cleanliness_2:checked ~ section [for=cleanliness_2] svg, #cleanliness_3:checked ~ section [for=cleanliness_1] svg, #cleanliness_3:checked ~ section [for=cleanliness_2] svg, #cleanliness_3:checked ~ section [for=cleanliness_3] svg, #cleanliness_4:checked ~ section [for=cleanliness_1] svg, #cleanliness_4:checked ~ section [for=cleanliness_2] svg, #cleanliness_4:checked ~ section [for=cleanliness_3] svg, #cleanliness_4:checked ~ section [for=cleanliness_4] svg, #cleanliness_5:checked ~ section [for=cleanliness_1] svg, #cleanliness_5:checked ~ section [for=cleanliness_2] svg, #cleanliness_5:checked ~ section [for=cleanliness_3] svg, #cleanliness_5:checked ~ section [for=cleanliness_4] svg, #cleanliness_5:checked ~ section [for=cleanliness_5] svg {
      transform: scale(1);
    }

    #cleanliness_1:checked ~ section [for=cleanliness_1] svg path, #cleanliness_2:checked ~ section [for=cleanliness_1] svg path, #cleanliness_2:checked ~ section [for=cleanliness_2] svg path, #cleanliness_3:checked ~ section [for=cleanliness_1] svg path, #cleanliness_3:checked ~ section [for=cleanliness_2] svg path, #cleanliness_3:checked ~ section [for=cleanliness_3] svg path, #cleanliness_4:checked ~ section [for=cleanliness_1] svg path, #cleanliness_4:checked ~ section [for=cleanliness_2] svg path, #cleanliness_4:checked ~ section [for=cleanliness_3] svg path, #cleanliness_4:checked ~ section [for=cleanliness_4] svg path, #cleanliness_5:checked ~ section [for=cleanliness_1] svg path, #cleanliness_5:checked ~ section [for=cleanliness_2] svg path, #cleanliness_5:checked ~ section [for=cleanliness_3] svg path, #cleanliness_5:checked ~ section [for=cleanliness_4] svg path, #cleanliness_5:checked ~ section [for=cleanliness_5] svg path {
      fill: #FFBB00;
      stroke: #cc9600;
    }


/* section {
  width: 300px;
  text-align: center;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate3d(-50%, -50%, 0);
} */

.lab_review {
  display: inline-block;
  width: 50px;
  text-align: center;
  cursor: pointer;
}
.lab_review svg {
  width: 100%;
  height: auto;
  fill: white;
  stroke: #CCC;
  transform: scale(0.8);
  transition: transform 200ms ease-in-out;
}
.lab_review svg path {
  transition: fill 200ms ease-in-out, stroke 100ms ease-in-out;
}

.lab_review[for=star-null] {
  display: block;
  margin: 0 auto;
  color: #999;
}

.d_none {
    display: none;
}

.input_checkbox {
    background: grey;
    padding: 5px 10px 5px 10px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
}

input[type="checkbox"]:checked + .input_checkbox {
    background: yellow;
    color: black;
}

</style>
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Guest Review</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                        @php
                            $review_json = json_decode($review->review_json);
                            // dd($review_json);
                            $listing_id = $review_json->meta->listing_id;
                            $listing = \App\Models\Listings::where('listing_id',$listing_id)->first();
                            $booking = \App\Models\BookingOtasDetails::where('listing_id',$listing_id)->first();
                            $listing_json = json_decode($listing->listing_json);
                            $guest_name = $review_json->guest_name;
                            // dd($review_json,$guest_name)
                        @endphp
                        <div class="row gy-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="name">Guest Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$guest_name}}" placeholder="Guest Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="name">Apartment Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$listing_json->title}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">Booking ID</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$booking->id}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">OTA</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->ota}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">Overall Score</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->overall_score}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="name">Received At</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->received_at}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-label" for="name">Content</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{$review_json->content}}" placeholder="Apartment Name" disabled>
                                    @error('name')
                                    <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>



        {{-- Guest Review --}}


        <div class="card card-bordered mt-3">
            <div class="card-inner">

                <form action="{{route('guestReview.store', $review->id)}}" method="POST">
                    @csrf
                    @php
                        $guest_review_json = $guestReview !== null ? $guestReview->guest_review_json : json_encode([]);
                        $guestReview = json_decode($guest_review_json);

                        // if (in_array('host_review_guest_negative_stayed_past_checkout', $guestReview->tags)) {
                        //     dd("Value found in the array.");
                        // } else {
                        //      dd("Value not found in the array.");
                        // }
                        // dd( $guestReview);
                    @endphp
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="public_review">Public Review</label>
                                <textarea class="form-control" name="public_review" id="public_review" rows="2" required>{{ isset($guestReview->public_review) && $guestReview->public_review ? $guestReview->public_review : ''  }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-group">
                                <label for="private_review">Private Review</label>
                                <textarea class="form-control" name="private_review" id="private_review" rows="2" required>{{ isset($guestReview->private_review) && $guestReview->private_review ? $guestReview->private_review : ''  }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12 mt-2">
                            <div class="form-group">
                                <label for="private_review">Recommended Guest For Others?</label>
                                <input type="checkbox" name="is_reviewee_recommended" {{ isset($guestReview->is_reviewee_recommended) && $guestReview->is_reviewee_recommended == true ? 'checked' : ''  }}>
                            </div>
                        </div>

                        <div class="col-md-12 mt-2">
                            <label for="">Respect House Rules</label>
                            <br>
                            <input type="radio" name="respect_house_rules" id="respect_house_rules_null" {{ isset($guestReview->scores[0]->rating) && $guestReview->scores[0]->rating == 0 ? 'checked' : '' }} value="0"/>
                            <input type="radio" name="respect_house_rules" id="respect_house_rules_1" {{ isset($guestReview->scores[0]->rating) && $guestReview->scores[0]->rating == 1 ? 'checked' : '' }}  value="1"/>
                            <input type="radio" name="respect_house_rules" id="respect_house_rules_2" {{ isset($guestReview->scores[0]->rating) && $guestReview->scores[0]->rating == 2 ? 'checked' : '' }}  value="2"/>
                            <input type="radio" name="respect_house_rules" id="respect_house_rules_3" {{ isset($guestReview->scores[0]->rating) && $guestReview->scores[0]->rating == 3 ? 'checked' : '' }}  value="3"/>
                            <input type="radio" name="respect_house_rules" id="respect_house_rules_4" {{ isset($guestReview->scores[0]->rating) && $guestReview->scores[0]->rating == 4 ? 'checked' : '' }} value="4" />
                            <input type="radio" name="respect_house_rules" id="respect_house_rules_5" {{ isset($guestReview->scores[0]->rating) && $guestReview->scores[0]->rating == 5 ? 'checked' : '' }}  value="5"/>

                            <section>
                              <label class = "lab_review" for="respect_house_rules_1">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="respect_house_rules_2">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="respect_house_rules_3">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="respect_house_rules_4">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="respect_house_rules_5">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label> 
                            </section>

                            <div class="row mt-3">
                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_arrived_early">Arrived too early</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_arrived_early')" id="host_review_guest_negative_arrived_early" value="host_review_guest_negative_arrived_early">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_stayed_past_checkout">Stayed past checkout</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_stayed_past_checkout')" id="host_review_guest_negative_stayed_past_checkout" value="host_review_guest_negative_stayed_past_checkout">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_unapproved_guests">Unapproved guests</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_unapproved_guests')" id="host_review_guest_negative_unapproved_guests" value="host_review_guest_negative_unapproved_guests">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_unapproved_pet">Unapproved pet</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_unapproved_pet')" id="host_review_guest_negative_unapproved_pet" value="host_review_guest_negative_unapproved_pet">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_did_not_respect_quiet_hours">Didn’t respect quiet hours</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_did_not_respect_quiet_hours')" id="host_review_guest_negative_did_not_respect_quiet_hours" value="host_review_guest_negative_did_not_respect_quiet_hours">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_unapproved_filming">Unapproved filming or photography</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_unapproved_filming')" id="host_review_guest_negative_unapproved_filming" value="host_review_guest_negative_unapproved_filming">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_unapproved_event">Unapproved event</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_unapproved_event')" id="host_review_guest_negative_unapproved_event" value="host_review_guest_negative_unapproved_event">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_smoking">Smoking</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_smoking')" id="host_review_guest_negative_smoking" value="host_review_guest_negative_smoking">
                                </div>
                            </div>


                            {{-- <div class="row">
                                <div class="col-md-12">
                                    <label class="input_checkbox" for="host_review_guest_negative_arrived_early">Arrived too early</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_arrived_early')" id = "host_review_guest_negative_arrived_early" value="host_review_guest_negative_arrived_early">
                                </div>
                            </div> --}}
                        </div>

                        <input type="hidden" name="review_id" value="{{ $review->id }}">

                        <div class="col-md-12 mt-2">
                            <label for="">Communication</label>
                            <br>
                            <input type="radio" name="communication" id="communication_null" {{ isset($guestReview->scores[1]->rating) && $guestReview->scores[0]->rating == 0 ? 'checked' : '' }}  value="0"/>
                            <input type="radio" name="communication" id="communication_1"  {{ isset($guestReview->scores[1]->rating) && $guestReview->scores[0]->rating == 1 ? 'checked' : '' }}  value="1"/>
                            <input type="radio" name="communication" id="communication_2"  {{ isset($guestReview->scores[1]->rating) && $guestReview->scores[0]->rating == 2 ? 'checked' : '' }}  value="2"/>
                            <input type="radio" name="communication" id="communication_3"  {{ isset($guestReview->scores[1]->rating) && $guestReview->scores[0]->rating == 3 ? 'checked' : '' }}  value="3"/>
                            <input type="radio" name="communication" id="communication_4"  {{ isset($guestReview->scores[1]->rating) && $guestReview->scores[0]->rating == 4 ? 'checked' : '' }} value="4" />
                            <input type="radio" name="communication" id="communication_5"  {{ isset($guestReview->scores[1]->rating) && $guestReview->scores[0]->rating == 5 ? 'checked' : '' }}  value="5"/>

                            <section>
                              <label class = "lab_review" for="communication_1">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="communication_2">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="communication_3">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="communication_4">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="communication_5">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                            </section>

                            <div class="row mt-4">
                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_positive_helpful_messages">Helpful messages</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_positive_helpful_messages')" id="host_review_guest_positive_helpful_messages" value="host_review_guest_positive_helpful_messages">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_positive_respectful">Respectful</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_positive_respectful')" id="host_review_guest_positive_respectful" value="host_review_guest_positive_respectful">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_positive_always_responded">Always responded</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_positive_always_responded')" id="host_review_guest_positive_always_responded" value="host_review_guest_positive_always_responded">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_unhelpful_messages">Unhelpful responses</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_unhelpful_messages')" id="host_review_guest_negative_unhelpful_messages" value="host_review_guest_negative_unhelpful_messages">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_disrespectful">Disrespectful</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_disrespectful')" id="host_review_guest_negative_disrespectful" value="host_review_guest_negative_disrespectful">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_unreachable">Unreachable</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_unreachable')" id="host_review_guest_negative_unreachable" value="host_review_guest_negative_unreachable">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_slow_responses">Slow responses</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_slow_responses')" id="host_review_guest_negative_slow_responses" value="host_review_guest_negative_slow_responses">
                                </div>
                            </div>

                        </div>


                        <div class="col-md-12 mt-2">
                            <label for="">Cleanliness</label>
                            <br>
                            <input type="radio" name="cleanliness" id="cleanliness_null" {{ isset($guestReview->scores[2]->rating) && $guestReview->scores[2]->rating == 0 ? 'checked' : '' }} value="0"/>
                            <input type="radio" name="cleanliness" id="cleanliness_1" {{ isset($guestReview->scores[2]->rating) && $guestReview->scores[2]->rating == 1 ? 'checked' : '' }}  value="1"/>
                            <input type="radio" name="cleanliness" id="cleanliness_2" {{ isset($guestReview->scores[2]->rating) && $guestReview->scores[2]->rating == 2 ? 'checked' : '' }}  value="2"/>
                            <input type="radio" name="cleanliness" id="cleanliness_3" {{ isset($guestReview->scores[2]->rating) && $guestReview->scores[2]->rating == 3 ? 'checked' : '' }}  value="3"/>
                            <input type="radio" name="cleanliness" id="cleanliness_4" {{ isset($guestReview->scores[2]->rating) && $guestReview->scores[2]->rating == 4 ? 'checked' : '' }} value="4" />
                            <input type="radio" name="cleanliness" id="cleanliness_5"  {{ isset($guestReview->scores[2]->rating) && $guestReview->scores[2]->rating == 5 ? 'checked' : '' }} value="5"/>

                            <section>
                              <label class = "lab_review" for="cleanliness_1">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>

                              <label class = "lab_review" for="cleanliness_2">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="cleanliness_3">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="cleanliness_4">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                              <label class = "lab_review" for="cleanliness_5">
                                <svg width="255" height="240" viewBox="0 0 51 48">
                                  <path d="m25,1 6,17h18l-14,11 5,17-15-10-15,10 5-17-14-11h18z"/>
                                </svg>
                              </label>
                            </section>

                            <div class="row mt-4">
                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_positive_neat_and_tidy">Neat & tidy</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_positive_neat_and_tidy')" id="host_review_guest_positive_neat_and_tidy" value="host_review_guest_positive_neat_and_tidy">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_positive_kept_in_good_condition">Kept in good condition</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_positive_kept_in_good_condition')" id="host_review_guest_positive_kept_in_good_condition" value="host_review_guest_positive_kept_in_good_condition">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_positive_took_care_of_garbage">Took care of garbage</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_positive_took_care_of_garbage')" id="host_review_guest_positive_took_care_of_garbage" value="host_review_guest_positive_took_care_of_garbage">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_ignored_checkout_directions">Ignored check-out directions</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_ignored_checkout_directions')" id="host_review_guest_negative_ignored_checkout_directions" value="host_review_guest_negative_ignored_checkout_directions">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_garbage">Excessive garbage</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_garbage')" id="host_review_guest_negative_garbage" value="host_review_guest_negative_garbage">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_messy_kitchen">Messy kitchen</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_messy_kitchen')" id="host_review_guest_negative_messy_kitchen" value="host_review_guest_negative_messy_kitchen">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_damage">Damaged property</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_damage')" id="host_review_guest_negative_damage" value="host_review_guest_negative_damage">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="input_checkbox" for="host_review_guest_negative_ruined_bed_linens">Ruined bed linens</label>
                                    <input type="checkbox" class="input_checkbox d_none" name="tags[]" onchange="changeToggleColor('host_review_guest_negative_ruined_bed_linens')" id="host_review_guest_negative_ruined_bed_linens" value="host_review_guest_negative_ruined_bed_linens">
                                </div>
                            </div>

                        </div>


                        <div class="col-md-12 mt-2">
                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary" {{ !empty(json_decode($guest_review_json)) ? 'disabled' : '' }}>Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function changeToggleColor(id) {
            document.getElementById(id).addEventListener('change', function() {
            const label = document.querySelector('label[for='+id+']');
            if (this.checked) {
                label.style.backgroundColor = '#aeae00';
                label.style.color = 'black';
            } else {
                label.style.backgroundColor = 'grey';
                label.style.color = 'white';
            }
        });
                }
    </script>
@endsection
