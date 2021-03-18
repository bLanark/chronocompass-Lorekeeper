<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Award\AwardCategory;
use App\Models\Award\Award;
use App\Models\Award\AwardTag;

class AwardService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Award Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of award categories and awards.
    |
    */

    /**********************************************************************************************
     
        AWARD CATEGORIES

    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Award\AwardCategory|bool
     */
    public function createAwardCategory($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateCategoryData($data);

            isset($data['character_limit']) && $data['character_limit'] ? $data['character_limit'] : $data['character_limit'] = 0;

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $category = AwardCategory::create($data);

            if ($image) $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);

            return $this->commitReturn($category);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param  \App\Models\Award\AwardCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Award\AwardCategory|bool
     */
    public function updateAwardCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(AwardCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateCategoryData($data, $category);

            isset($data['character_limit']) && $data['character_limit'] ? $data['character_limit'] : $data['character_limit'] = 0;

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $category->update($data);

            if ($category) $this->handleImage($image, $category->categoryImagePath, $category->categoryImageFileName);

            return $this->commitReturn($category);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle category data.
     *
     * @param  array                               $data
     * @param  \App\Models\Award\AwardCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        
        if(isset($data['remove_image']))
        {
            if($category && $category->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }

    /**
     * Delete a category.
     *
     * @param  \App\Models\Award\AwardCategory  $category
     * @return bool
     */
    public function deleteAwardCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Award::where('award_category_id', $category->id)->exists()) throw new \Exception("An award with this category exists. Please change its category first.");
            
            if($category->has_image) $this->deleteImage($category->categoryImagePath, $category->categoryImageFileName); 
            $category->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortAwardCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                AwardCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**********************************************************************************************
     
        AWARDS

    **********************************************************************************************/

    /**
     * Creates a new award.
     *
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Award\Award
     */
    public function createAward($data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['award_category_id']) && $data['award_category_id'] == 'none') $data['award_category_id'] = null;

            if((isset($data['award_category_id']) && $data['award_category_id']) && !AwardCategory::where('id', $data['award_category_id'])->exists()) throw new \Exception("The selected award category is invalid.");

            $data = $this->populateData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $award = Award::create($data);

            $award->update([
                'data' => json_encode([
                    'rarity' => isset($data['rarity']) && $data['rarity'] ? $data['rarity'] : null,
                    'uses' => isset($data['uses']) && $data['uses'] ? $data['uses'] : null,
                    'release' => isset($data['release']) && $data['release'] ? $data['release'] : null,
                    'shops' => isset($data['shops']) && $data['shops'] ? $data['shops'] : null,
                    'prompts' => isset($data['prompts']) && $data['prompts'] ? $data['prompts'] : null
                    ]) // rarity, availability info (original source, purchase locations, drop locations)
            ]);

            if ($image) $this->handleImage($image, $award->imagePath, $award->imageFileName);

            return $this->commitReturn($award);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Award\Award
     */
    public function updateAward($award, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['award_category_id']) && $data['award_category_id'] == 'none') $data['award_category_id'] = null;

            // More specific validation
            if(Award::where('name', $data['name'])->where('id', '!=', $award->id)->exists()) throw new \Exception("The name has already been taken.");
            if((isset($data['award_category_id']) && $data['award_category_id']) && !AwardCategory::where('id', $data['award_category_id'])->exists()) throw new \Exception("The selected award category is invalid.");

            $data = $this->populateData($data);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $award->update($data);

            $award->update([
                'data' => json_encode([
                    'rarity' => isset($data['rarity']) && $data['rarity'] ? $data['rarity'] : null,
                    'uses' => isset($data['uses']) && $data['uses'] ? $data['uses'] : null,
                    'release' => isset($data['release']) && $data['release'] ? $data['release'] : null,
                    'shops' => isset($data['shops']) && $data['shops'] ? $data['shops'] : null,
                    'prompts' => isset($data['prompts']) && $data['prompts'] ? $data['prompts'] : null
                    ]) // rarity, availability info (original source, purchase locations, drop locations)
            ]);

            if ($award) $this->handleImage($image, $award->imagePath, $award->imageFileName);

            return $this->commitReturn($award);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an award.
     *
     * @param  array                  $data 
     * @param  \App\Models\Award\Award  $award
     * @return array
     */
    private function populateData($data, $award = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        
        if(!isset($data['allow_transfer'])) $data['allow_transfer'] = 0;

        if(isset($data['remove_image']))
        {
            if($award && $award->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($award->imagePath, $award->imageFileName); 
            }
            unset($data['remove_image']);
        }

        return $data;
    }
    
    /**
     * Deletes an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @return bool
     */
    public function deleteAward($award)
    {
        DB::beginTransaction();

        try {
            // Check first if the award is currently owned or if some other site feature uses it
            if(DB::table('user_awards')->where([['award_id', '=', $award->id], ['count', '>', 0]])->exists()) throw new \Exception("At least one user currently owns this award. Please remove the award(s) before deleting it.");
            if(DB::table('character_awards')->where([['award_id', '=', $award->id], ['count', '>', 0]])->exists()) throw new \Exception("At least one character currently owns this award. Please remove the award(s) before deleting it.");
            if(DB::table('loots')->where('rewardable_type', 'Award')->where('rewardable_id', $award->id)->exists()) throw new \Exception("A loot table currently distributes this award as a potential reward. Please remove the award before deleting it.");
            if(DB::table('prompt_rewards')->where('rewardable_type', 'Award')->where('rewardable_id', $award->id)->exists()) throw new \Exception("A prompt currently distributes this award as a reward. Please remove the award before deleting it.");
            if(DB::table('shop_stock')->where('award_id', $award->id)->exists()) throw new \Exception("A shop currently stocks this award. Please remove the award before deleting it.");
            
            DB::table('awards_log')->where('award_id', $award->id)->delete();
            DB::table('user_awards')->where('award_id', $award->id)->delete();
            DB::table('character_awards')->where('award_id', $award->id)->delete();
            $award->tags()->delete();
            if($award->has_image) $this->deleteImage($award->imagePath, $award->imageFileName); 
            $award->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**********************************************************************************************
     
        AWARD TAGS

    **********************************************************************************************/
    
    /**
     * Gets a list of award tags for selection.
     *
     * @return array
     */
    public function getAwardTags()
    {
        $tags = Config::get('lorekeeper.award_tags');
        $result = [];
        foreach($tags as $tag => $tagData)
            $result[$tag] = $tagData['name'];

        return $result;
    }
    
    /**
     * Adds an award tag to an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @param  string                 $tag
     * @return string|bool
     */
    public function addAwardTag($award, $tag)
    {
        DB::beginTransaction();

        try {
            if(!$award) throw new \Exception("Invalid award selected.");
            if($award->tags()->where('tag', $tag)->exists()) throw new \Exception("This award already has this tag attached to it.");
            if(!$tag) throw new \Exception("No tag selected.");
            
            $tag = AwardTag::create([
                'award_id' => $award->id,
                'tag' => $tag
            ]);

            return $this->commitReturn($tag);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Edits the data associated with an award tag on an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @param  string                 $tag
     * @param  array                  $data
     * @return string|bool
     */
    public function editAwardTag($award, $tag, $data)
    {
        DB::beginTransaction();

        try {
            if(!$award) throw new \Exception("Invalid award selected.");
            if(!$award->tags()->where('tag', $tag)->exists()) throw new \Exception("This award does not have this tag attached to it.");
            
            $tag = $award->tags()->where('tag', $tag)->first();

            $service = $tag->service;
            if(!$service->updateData($tag, $data)) {
                $this->setErrors($service->errors());
                throw new \Exception('sdlfk');
            }

            // Update the tag's active setting
            $tag->is_active = isset($data['is_active']);
            $tag->save();

            return $this->commitReturn($tag);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Removes an award tag from an award.
     *
     * @param  \App\Models\Award\Award  $award
     * @param  string                 $tag
     * @return string|bool
     */
    public function deleteAwardTag($award, $tag)
    {
        DB::beginTransaction();

        try {
            if(!$award) throw new \Exception("Invalid award selected.");
            if(!$award->tags()->where('tag', $tag)->exists()) throw new \Exception("This award does not have this tag attached to it.");
            
            $award->tags()->where('tag', $tag)->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}