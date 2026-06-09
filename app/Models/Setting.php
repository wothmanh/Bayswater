<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'logo_path',
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'favicon_path',
        'cutoff_date',
        'quotation_extraction_date',
        // New configurable tab fields
        'market_discount_tab_title',
        'market_discount_iframe_url',
        'search_accommodation_tab_title',
        'search_accommodation_page_link',
        'partner_zone_tab_title',
        'partner_zone_page_link',
        'course_details_button_text',
        // WhatsApp chat settings
        'whatsapp_number',
        'whatsapp_default_message',
    ];

    /**
     * Get a setting by key
     *
     * @param string $key
     * @return mixed
     */
    public static function getSetting($key)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }

    /**
     * Get all settings as key-value pairs
     *
     * @return array
     */
    public static function getAllSettings()
    {
        return self::first() ?: new self();
    }
}
