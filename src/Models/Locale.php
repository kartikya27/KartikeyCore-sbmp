<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Kartikey\Core\Database\Factories\LocaleFactory;
use Kartikey\Core\Interface\Locale as InterfaceLocale;

class Locale extends Model implements InterfaceLocale
{
    use SoftDeletes;
    protected $table = LOCALE_TABLE;
    protected $guarded = ['id'];

      /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['logo_url'];

     /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return LocaleFactory::new();
    }

    /**
     * Get the logo full path of the locale.
     *
     * @return string|null
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo_url();
    }

    /**
     * Get the logo full path of the locale.
     *
     * @return string|void
     */
    public function logo_url()
    {
        if (empty($this->logo_path)) {
            return;
        }

        return Storage::url($this->logo_path);
    }
}
