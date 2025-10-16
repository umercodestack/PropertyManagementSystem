<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Hostaboard extends Model
{
    // use HasFactory;
    // Agar table ka naam different ho, to aap usse specify kar sakte hain
    protected $table = 'hostaboard';
    // use HasFactory;
    // Agar aap mass assignment allow karna chahte hain, to fillable properties define karein
    protected $fillable = [
        'host_id',
        'property_id',
        'owner_name',
        'city_name',
        'type',
        'unit_type',
        'unit_number',
        'floor',
        'location',
        'contract_file',
        'host_bank_detail',
        'existing_ota_links',
        'property_address',
        'property_google_map_link',
        'property_images_link',
        'is_photo_exists',
        'door_locks_mechanism',
        'door_lock_code',
        'wi_fi_password',
        'amenities',
        'host_number',
        'building_Caretaker_name',
        'building_Caretaker_number',
        'account_manager_id',
        'title',
        'user_id',
        'last_name',
        'host_email',
        'bank_name',
        'iban_no',
        'swift_code',
        'postal_code',
        
        'be_listing_name',
        'property_about',
        'bedrooms',
        'beds',
        'bathrooms',
        'district',
        'street',
        'is_allow_pets',
        'is_self_check_in',
        'discounts',
        'tax',
        'cleaning_fee',
        'living_room',
        'laundry_area',
        'corridor',
        'outdoor_area',
        'kitchen',
        'is_old',
        'block_dates_via_host_app',
        'ota_charges_on_direct_booking',
        'cleaning_fee_on_direct_booking',
        'fixed_cleaning_fee',
        'fixed_cleaning_fee_amount',
        'co_hosting_account',
        'length_type',
        'min_days_for_ltr',
        'services',
        'livedin_share_after_discount',
        'share_percentage',
        'utiltiy_bills',
        'licence_doc',
        'deep_cleaning_required',
        'airbnb_email',
        'airbnb_password',
        'lenght_type_document',
        'building_type',
        'floor_number',
        'host_exclusivity',
        'cleaning_done_by_livedin',
        'host_rental_lease',
        'national_address_document',


        'type_of_ownership_document',
        'ownership_document_number_and_type',
        'date_of_birth',
        'building_number',
        'additional_number',
        'property_area',
        'property_services',
        'property_age',
        'room_number',
        'existing_property_obligations'

    ];

    protected $casts = [
        'type_of_ownership_document' => 'array',
    ];


    

    public function ownershipDocuments()
    {
        return $this->hasMany(HostaboardOwnershipDocument::class, 'hostaboard_id');
    }

    public function ownerDocuments()
    {
        return $this->hasMany(HostaboardOwnerDocument::class, 'hostaboard_id');
    }

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function userdetail()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class,'id','host_activation_id');
    }
    
    public function owner_documents()
    {
        return $this->hasMany(HostaboardOwnerDocument::class, 'hostaboard_id');
    }

    public function ownership_documents()
    {
        return $this->hasMany(HostaboardOwnershipDocument::class, 'hostaboard_id');
    }
    
    
}
