<?php

namespace App\Models\Award;

use Config;
use DB;
use App\Models\Model;
use App\Models\Award\AwardCategory;

use App\Models\User\User;
use App\Models\Shop\Shop;
use App\Models\Prompt\Prompt;

class Award extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'award_category_id', 'name', 'has_image', 'description', 'parsed_description',
        'data', 'reference_url', 'artist_alias', 'artist_url'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'awards';
    
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'award_category_id' => 'nullable',
        'name' => 'required|unique:awards|between:3,100',
        'description' => 'nullable',
        'image' => 'mimes:png',
        'rarity' => 'nullable',
        'reference_url' => 'nullable|between:3,200',
        'uses' => 'nullable|between:3,250',
        'release' => 'nullable|between:3,100'
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'award_category_id' => 'nullable',
        'name' => 'required|between:3,100',
        'description' => 'nullable',
        'image' => 'mimes:png',
        'reference_url' => 'nullable|between:3,200',
        'uses' => 'nullable|between:3,250',
        'release' => 'nullable|between:3,100'
    ];

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the award belongs to.
     */
    public function category() 
    {
        return $this->belongsTo('App\Models\Award\AwardCategory', 'award_category_id');
    }

    /**
     * Get the award's tags.
     */
    public function tags() 
    {
        return $this->hasMany('App\Models\Award\AwardTag', 'award_id');
    }

    /**********************************************************************************************
    
        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort awards in alphabetical order.
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
     * Scope a query to sort awards in category order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query)
    {
        $ids = AwardCategory::orderBy('sort', 'DESC')->pluck('id')->toArray();
        return count($ids) ? $query->orderByRaw(DB::raw('FIELD(award_category_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort awards by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
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
        return '<a href="'.$this->url.'" class="display-award">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/awards';
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
        if (!$this->has_image) return null;
        return asset($this->imageDirectory . '/' . $this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('world/awards?name='.$this->name);
    }

    /**
     * Gets the URL of the individual award's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('world/awards/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'awards';
    }

    /**
     * Get the artist of the award's image.
     * 
     * @return string
     */
    public function getArtistAttribute() 
    {
        if(!$this->artist_url && !$this->artist_alias) return null;
        if ($this->artist_url)
        {
            return '<a href="'.$this->artist_url.'" class="display-creator">'. ($this->artist_alias ? : $this->artist_url) .'</a>';
        }
        else if($this->artist_alias)
        {
            $user = User::where('alias', trim($this->artist_alias))->first();
            if($user) return $user->displayName;
            else return '<a href="https://www.deviantart.com/'.$this->artist_alias.'">'.$this->artist_alias.'@dA</a>';
        }
    }

    /**
     * Get the reference url attribute.
     *
     * @return string
     */
    public function getReferenceAttribute() 
    {
        if (!$this->reference_url) return null;
        return $this->reference_url;
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() 
    {
        if (!$this->id) return null;
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Get the rarity attribute.
     *
     * @return string
     */
    public function getRarityAttribute() 
    {
        if (!$this->data) return null;
        return $this->data['rarity'];
    }

    /**
     * Get the uses attribute.
     *
     * @return string
     */
    public function getUsesAttribute() 
    {
        if (!$this->data) return null;
        return $this->data['uses'];
    }

    /**
     * Get the source attribute.
     *
     * @return string
     */
    public function getSourceAttribute() 
    {
        if (!$this->data) return null;
        return $this->data['release'];
    }

    /**
     * Get the shops attribute as an associative array.
     *
     * @return array
     */
    public function getShopsAttribute() 
    {
        if (!$this->data) return null;
        $awardShops = $this->data['shops'];
        return Shop::whereIn('id', $awardShops)->get();
    }

    /**
     * Get the prompts attribute as an associative array.
     *
     * @return array
     */
    public function getPromptsAttribute() 
    {
        if (!$this->data) return null;
        $awardPrompts = $this->data['prompts'];
        return Prompt::whereIn('id', $awardPrompts)->get();
    }

    /**********************************************************************************************
    
        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if the award has a particular tag.
     *
     * @return bool
     */
    public function hasTag($tag)
    {
        return $this->tags()->where('tag', $tag)->where('is_active', 1)->exists();
    }

    /**
     * Gets a particular tag attached to the award.
     *
     * @return \App\Models\Award\AwardTag
     */
    public function tag($tag)
    {
        return $this->tags()->where('tag', $tag)->where('is_active', 1)->first();
    }
}
