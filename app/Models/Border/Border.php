<?php

namespace App\Models\Border;

use App\Models\Border\BorderCategory;
use App\Models\Model;
use App\Models\User\User;
use Auth;
use App\Models\Item\ItemTag;

class Border extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parsed_description', 'is_default', 'border_category_id', 'is_active', 'border_style', 'admin_only', 'parent_id', 'border_type', 'artist_id','artist_url'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'borders';

    protected $appends = ['image_url'];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:borders|between:3,100',
        'image' => 'required|mimes:jpeg,jpg,gif,png',
        'border_style' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
        'image' => 'nullable|mimes:jpeg,jpg,gif,png',
        'border_style' => 'required',
    ];

    /**********************************************************************************************
    SCOPES
     **********************************************************************************************/

    /**
     * Scope a query to sort borders in alphabetical order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $reverse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false)
    {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort borders in category order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query)
    {
        if (BorderCategory::all()->count()) {
            return $query->orderBy(BorderCategory::select('sort')->whereColumn('borders.border_category_id', 'border_categories.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort borders by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort borders oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only visible borders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $user = null)
    {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to show only non-variant borders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBase($query)
    {
        return $query->whereNull('parent_id');
    }

    /**********************************************************************************************
    RELATIONS
     **********************************************************************************************/

    /**
     * Get the category the border belongs to.
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Border\BorderCategory', 'border_category_id');
    }

    /**
     * Get the parent the border belongs to.
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Border\Border', 'parent_id');
    }

    /**
     * Get all the border's variants.
     */
    public function variants()
    {
        return $this->hasMany('App\Models\Border\Border', 'parent_id')->active(Auth::user() ?? null)->where('border_type', 'variant');
    }

    /**
     * Get all the border's top layers.
     */
    public function topLayers()
    {
        return $this->hasMany('App\Models\Border\Border', 'parent_id')->active(Auth::user() ?? null)->where('border_type', 'top');
    }

    /**
     * Get all the border's bottom layers.
     */
    public function bottomLayers()
    {
        return $this->hasMany('App\Models\Border\Border', 'parent_id')->active(Auth::user() ?? null)->where('border_type', 'bottom');
    }

    /**
     * Get the user that drew the border art.
     */
    public function artist()
    {
        return $this->belongsTo('App\Models\User\User', 'artist_id');
    }

    /**********************************************************************************************
    ACCESSORS
     **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if (!$this->is_active) {
            return '<i class="fas fa-eye-slash"></i> <a href="' . $this->url . '" class="display-item">' . $this->name . '</a>';
        }
        return '<a href="' . $this->url . '" class="display-item">' . $this->name . '</a>';
    }

    /**
     * get the name if default or not
     *
     * @return string
     */
    public function getSettingsNameAttribute()
    {
        if ($this->admin_only) {
            return $this->name . ' (Staff)';
        }

        if ($this->is_default) {
            return $this->name . ' (Default)';
        } else {
            return $this->name;
        }
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/borders';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute()
    {
        return $this->id . '-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        return asset($this->imageDirectory . '/' . $this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('world/borders?name=' . $this->name);
    }

    /**
     * Gets the URL of the individual item's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('world/borders/' . $this->id);
    }

    /**
     * Gets the border's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'borders';
    }

    /**
     * get the preview for the border
     *
     */
    public function preview($id = null)
    {
        //we will preview the border on various site pages for purposes of fun :}
        //and so people can "test" their look without having to unlock one
        //if we pass $id, return the avatar of that user
        if ($id) {
            $user = User::find($id)->avatarUrl;
            $name = 'Avatar of ' . User::find($id)->name;
            //else, check if logged in
            //if logged in, return the user avatar to preview
        } elseif (Auth::check()) {
            $user = Auth::user()->avatarUrl;
            $name = 'Avatar of ' . Auth::user()->name;
            //finally if not either of these, return default avatar
        } else {
            $user = url('images/avatars/default.jpg');
            $name = 'Default Avatar';
        }
        //basically just an ugly ass string of html for copypasting use
        //would you want to keep posting this everywhere? yeah i thought so. me neither
        //there's probably a less hellish way to do this but it beats having to paste this over everywhere... EVERY SINGLE TIME.
        //especially with the checks

        //get some fun variables for later
        $avatar = '<!-- avatar -->
                <img class="avatar" src="' . $user . '" alt="' . $name . '">';

        $styling = '<div class="user-avatar">';
        $layer = ($this->border_style == 0 ? 'under' : ' ');

        $frame = '<!-- frame -->
                <img src="' . $this->imageUrl . '" class="avatar-border ' . $layer . '" alt="' . $this->name . ' Avatar Frame">';

        return $styling . $avatar . $frame . '</div>';
    }

    /**
     * display the user's icon and border styling
     *
     */
    public function previewLayers($top, $bottom, $id = null)
    {
        //we will preview the border on various site pages for purposes of fun :}
        //and so people can "test" their look without having to unlock one
        //if we pass $id, return the avatar of that user
        if ($id) {
            $user = User::find($id)->avatarUrl;
            $name = 'Avatar of ' . User::find($id)->name;
            //else, check if logged in
            //if logged in, return the user avatar to preview
        } elseif (Auth::check()) {
            $user = Auth::user()->avatarUrl;
            $name = 'Avatar of ' . Auth::user()->name;
            //finally if not either of these, return default avatar
        } else {
            $user = url('images/avatars/default.jpg');
            $name = 'Default Avatar';
        }
        //basically just an ugly ass string of html for copypasting use
        //would you want to keep posting this everywhere? yeah i thought so. me neither
        //there's probably a less hellish way to do this but it beats having to paste this over everywhere... EVERY SINGLE TIME.
        //especially with the checks

        //get some fun variables for later
        $avatar = '<!-- avatar -->
                <img class="avatar" src="' . $user . '" alt="' . $name . '">';

        $styling = '<div class="user-avatar">';

        if ($top->border_style == 0 && $bottom->border_style == 0) {
            // If both layers are UNDER the avatar
            //top's layer image
            $mainframe = '<img src="' . $top->imageUrl . '" class="avatar-border under" alt="' . $this->name . ' Avatar Frame">';
            //bottom layer's image
            $secondframe = '<img src="' . $bottom->imageUrl . '" class="avatar-border bottom" alt="' . $this->name . ' Avatar Frame">';
        } elseif ($top->border_style == 1 && $bottom->border_style == 1) {
            // If both layers are OVER the avatar
            //top's layer image
            $mainframe = '<img src="' . $top->imageUrl . '" class="avatar-border top" alt="' . $this->name . ' Avatar Frame">';
            //bottom layer's image
            $secondframe = '<img src="' . $bottom->imageUrl . '" class="avatar-border" alt="' . $this->name . ' Avatar Frame">';
        } else {
            // If one layer is UNDER and one is OVER the avatar
            $mainlayer = ($top->border_style == 0 ? 'under' : ' ');
            $secondlayer = ($bottom->border_style == 0 ? 'under' : ' ');

            //top's layer image
            $mainframe = '<img src="' . $top->imageUrl . '" class="avatar-border ' . $mainlayer . '" alt="' . $this->name . ' Avatar Frame">';
            //bottom layer's image
            $secondframe = '<img src="' . $bottom->imageUrl . '" class="avatar-border ' . $secondlayer . '" alt="' . $this->name . ' Avatar Frame">';
        }

        return $styling . $avatar . $mainframe . $secondframe . '</div>';
    }

    /**
     * Get the artist of the border's image.
     *
     * @return string
     */
    public function getBorderArtistAttribute()
    {
        if (!$this->artist_url && !$this->artist_id) {
            return null;
        }

        // Check to see if the artist exists on site
        $artist = checkAlias($this->artist_url, false);
        if (is_object($artist)) {
            $this->artist_id = $artist->id;
            $this->artist_url = null;
            $this->save();
        }

        if ($this->artist_id) {
            return $this->artist->displayName;
        } else if ($this->artist_url) {
            return prettyProfileLink($this->artist_url);
        }
    }

    public function hasItem() {
        $item = ItemTag::where('tag', 'border')->where('data', '{"borders":{"'.$this->id.'":1}}')->first();

        if ($item) {
            return true;
        }

        return false;
    }

    public function borderItem() {
        $item = ItemTag::where('tag', 'border')->where('data', '{"borders":{"'.$this->id.'":1}}')->first();

        return $item->item;
    }

}
