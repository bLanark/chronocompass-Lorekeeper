<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Award\AwardCategory;
use App\Models\Award\Award;
use App\Models\Award\AwardTag;

use App\Models\Shop\Shop;
use App\Models\Prompt\Prompt;

use App\Services\AwardService;

use App\Http\Controllers\Controller;

class AwardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Award Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of award categories and awards.
    |
    */

    /**********************************************************************************************
    
        AWARD CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the award category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.awards.award_categories', [
            'categories' => AwardCategory::orderBy('sort', 'DESC')->get()
        ]);
    }
    
    /**
     * Shows the create award category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAwardCategory()
    {
        return view('admin.awards.create_edit_award_category', [
            'category' => new AwardCategory
        ]);
    }
    
    /**
     * Shows the edit award category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAwardCategory($id)
    {
        $category = AwardCategory::find($id);
        if(!$category) abort(404);
        return view('admin.awards.create_edit_award_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits an award category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAwardCategory(Request $request, AwardService $service, $id = null)
    {
        $id ? $request->validate(AwardCategory::$updateRules) : $request->validate(AwardCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_character_owned', 'character_limit'
        ]);
        if($id && $service->updateAwardCategory(AwardCategory::find($id), $data, Auth::user())) {
            flash('Award Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createAwardCategory($data, Auth::user())) {
            flash('Award Category created successfully.')->success();
            return redirect()->to('admin/data/award-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the award category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAwardCategory($id)
    {
        $category = AwardCategory::find($id);
        return view('admin.awards._delete_award_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an award category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAwardCategory(Request $request, AwardService $service, $id)
    {
        if($id && $service->deleteAwardCategory(AwardCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/award-categories');
    }

    /**
     * Sorts award categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortAwardCategory(Request $request, AwardService $service)
    {
        if($service->sortAwardCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************
    
        AWARDS

    **********************************************************************************************/

    /**
     * Shows the award index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAwardIndex(Request $request)
    {
        $query = Award::query();
        $data = $request->only(['award_category_id', 'name']);
        if(isset($data['award_category_id']) && $data['award_category_id'] != 'none') 
            $query->where('award_category_id', $data['award_category_id']);
        if(isset($data['name'])) 
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.awards.awards', [
            'awards' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + AwardCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }
    
    /**
     * Shows the create award page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAward()
    {
        return view('admin.awards.create_edit_award', [
            'award' => new Award,
            'categories' => ['none' => 'No category'] + AwardCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'shops' => Shop::where('is_active', 1)->orderBy('id')->pluck('name', 'id'),
            'prompts' => Prompt::where('is_active', 1)->orderBy('id')->pluck('name', 'id')
        ]);
    }
    
    /**
     * Shows the edit award page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAward($id)
    {
        $award = Award::find($id);
        if(!$award) abort(404);
        return view('admin.awards.create_edit_award', [
            'award' => $award,
            'categories' => ['none' => 'No category'] + AwardCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'shops' => Shop::where('is_active', 1)->orderBy('id')->pluck('name', 'id'),
            'prompts' => Prompt::where('is_active', 1)->orderBy('id')->pluck('name', 'id')
        ]);
    }

    /**
     * Creates or edits an award.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditAward(Request $request, AwardService $service, $id = null)
    {
        $id ? $request->validate(Award::$updateRules) : $request->validate(Award::$createRules);
        $data = $request->only([
            'name', 'allow_transfer', 'award_category_id', 'description', 'image', 'remove_image', 'rarity',
            'reference_url', 'artist_alias', 'artist_url', 'uses', 'shops', 'prompts', 'release'
        ]);
        if($id && $service->updateAward(Award::find($id), $data, Auth::user())) {
            flash('Award updated successfully.')->success();
        }
        else if (!$id && $award = $service->createAward($data, Auth::user())) {
            flash('Award created successfully.')->success();
            return redirect()->to('admin/data/awards/edit/'.$award->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the award deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAward($id)
    {
        $award = Award::find($id);
        return view('admin.awards._delete_award', [
            'award' => $award,
        ]);
    }

    /**
     * Creates or edits an award.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAward(Request $request, AwardService $service, $id)
    {
        if($id && $service->deleteAward(Award::find($id))) {
            flash('Award deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/awards');
    }

    /**********************************************************************************************
    
        AWARD TAGS

    **********************************************************************************************/

    /**
     * Gets the tag addition page.
     *
     * @param  App\Services\AwardService  $service
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAddAwardTag(AwardService $service, $id)
    {
        $award = Award::find($id);
        return view('admin.awards.add_tag', [
            'award' => $award,
            'tags' => array_diff($service->getAwardTags(), $award->tags()->pluck('tag')->toArray())
        ]);
    }

    /**
     * Adds a tag to an award.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAddAwardTag(Request $request, AwardService $service, $id)
    {
        $award = Award::find($id);
        $tag = $request->get('tag');
        if($tag = $service->addAwardTag($award, $tag)) {
            flash('Tag added successfully.')->success();
            return redirect()->to($tag->adminUrl);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the tag editing page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAwardTag(AwardService $service, $id, $tag)
    {
        $award = Award::find($id);
        $tag = $award->tags()->where('tag', $tag)->first();
        if(!$award || !$tag) abort(404);
        return view('admin.awards.edit_tag', [
            'award' => $award,
            'tag' => $tag
        ] + $tag->getEditData());
    }

    /**
     * Edits tag data for an award.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int                       $id
     * @param  string                    $tag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditAwardTag(Request $request, AwardService $service, $id, $tag)
    {
        $award = Award::find($id);
        if($service->editAwardTag($award, $tag, $request->all())) {
            flash('Tag edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the award tag deletion modal.
     *
     * @param  int  $id
     * @param  string                    $tag
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAwardTag($id, $tag)
    {
        $award = Award::find($id);
        $tag = $award->tags()->where('tag', $tag)->first();
        return view('admin.awards._delete_award_tag', [
            'award' => $award,
            'tag' => $tag
        ]);
    }

    /**
     * Deletes a tag from an award.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\AwardService  $service
     * @param  int                       $id
     * @param  string                    $tag
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAwardTag(Request $request, AwardService $service, $id, $tag)
    {
        $award = Award::find($id);
        if($service->deleteAwardTag($award, $tag)) {
            flash('Tag deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/awards/edit/'.$award->id);
    }
}
