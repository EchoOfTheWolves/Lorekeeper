<?php

namespace App\Models\Weather;

use Config;
use App\Models\Model;

class WeatherTable extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'weather_season_id', 'weather_id','weight'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'weather_table';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'weather_season_id' => 'required',
        'weight' => 'required|integer|min:1',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'weather_season_id' => 'required',
        'weight' => 'required|integer|min:1',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the loot entry.
     */
    public function reward()
    {
            return $this->belongsTo('App\Models\Weather\Weather', 'weather_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Display the loot item and link to it's encylopedia entry.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->reward->url.'">'.$this->reward->name.'</a>';
    }

    /**
     * Displays the drop rate of a loot.
     *
     * @return string
     */
    public function getDropRateAttribute()
    {
        $totalWeight = WeatherTable::where('weather_season_id', $this->weather_season_id)->sum('weight');
        $dropRate = $this->weight / $totalWeight * 100;
        return number_format((float)$dropRate, 2, '.', '').'%';
    }
}
