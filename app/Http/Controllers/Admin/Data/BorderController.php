<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Border\Border;
use App\Models\Border\BorderCategory;
use App\Models\User\User;
use App\Services\BorderService;
use Auth;
use Illuminate\Http\Request;

class BorderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Border Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of borders.
    |
     */

    /**********************************************************************************************
    Border CATEGORIES
     **********************************************************************************************/

    /**
     * Shows the border category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.borders.border_categories', [
            'categories' => BorderCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create border category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBorderCategory()
    {
        return view('admin.borders.create_edit_border_category', [
            'category' => new BorderCategory,
        ]);
    }

    /**
     * Shows the edit border category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBorderCategory($id)
    {
        $category = BorderCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.borders.create_edit_border_category', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits an border category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BorderService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBorderCategory(Request $request, BorderService $service, $id = null)
    {
        $id ? $request->validate(BorderCategory::$updateRules) : $request->validate(BorderCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image',
        ]);
        if ($id && $service->updateBorderCategory(BorderCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } else if (!$id && $category = $service->createBorderCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/data/border-categories/edit/' . $category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the border category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBorderCategory($id)
    {
        $category = BorderCategory::find($id);
        return view('admin.borders._delete_border_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an border category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BorderService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBorderCategory(Request $request, BorderService $service, $id)
    {
        if ($id && $service->deleteBorderCategory(BorderCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->to('admin/data/border-categories');
    }

    /**
     * Sorts border categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BorderService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortBorderCategory(Request $request, BorderService $service)
    {
        if ($service->sortBorderCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**********************************************************************************************
    BorderS
     **********************************************************************************************/

    /**
     * Shows the border index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBorderIndex(Request $request)
    {
        $query = Border::base();
        $data = $request->only(['name', 'border_category_id']);
        if (isset($data['border_category_id']) && $data['border_category_id'] != 'none') {
            $query->where('border_category_id', $data['border_category_id']);
        }

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%' . $data['name'] . '%');
        }

        return view('admin.borders.borders', [
            'borders' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + BorderCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create border page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBorder()
    {
        return view('admin.borders.create_edit_border', [
            'border' => new Border,
            'categories' => ['none' => 'No category'] + BorderCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the edit border page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBorder($id)
    {
        $border = Border::find($id);
        if (!$border) {
            abort(404);
        }

        return view('admin.borders.create_edit_border', [
            'border' => $border,
            'categories' => ['none' => 'No category'] + BorderCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Creates or edits an border.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BorderService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBorder(Request $request, BorderService $service, $id = null)
    {
        $id ? $request->validate(Border::$updateRules) : $request->validate(Border::$createRules);
        $data = $request->only([
            'name', 'description', 'border_category_id', 'is_default', 'image', 'is_active', 'border_style', 'admin_only','artist_id','artist_url'
        ]);
        if ($id && $service->updateBorder(Border::find($id), $data, Auth::user())) {
            flash('Border updated successfully.')->success();
        } else if (!$id && $border = $service->createBorder($data, Auth::user())) {
            flash('Border created successfully.')->success();
            return redirect()->to('admin/data/borders/edit/' . $border->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the border deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBorder($id)
    {
        $border = Border::find($id);
        return view('admin.borders._delete_border', [
            'border' => $border,
        ]);
    }

    /**
     * Creates or edits an border.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BorderService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBorder(Request $request, BorderService $service, $id)
    {
        if ($id && $service->deleteBorder(Border::find($id))) {
            flash('Border deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->to('admin/data/borders');
    }

    /**********************************************************************************************

    VARIANTS

     **********************************************************************************************/

    /**
     * Gets the create / edit border variant page.
     *
     * @param mixed      $border_id
     * @param mixed|null $id
     */
    public function getCreateEditVariant($border_id, $type, $id = null)
    {
        return view('admin.borders._create_edit_border_variant', [
            'border' => Border::find($border_id),
            'variant' => $id ? Border::find($id) : new Border,
            'type' => $type,
            'userOptions' => User::query()->orderBy('name')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Edits border variants.
     *
     * @param App\Services\BorderService $service
     * @param int                     $id
     * @param mixed                   $border_id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditVariant(Request $request, BorderService $service, $border_id, $type, $id = null)
    {
        $data = $request->only(['name', 'image', 'is_active', 'border_style','delete','artist_id','artist_url']);
        if ($id && $service->editVariant(Border::findOrFail($id), $data, $type)) {
        } elseif (!$id && $service->createVariant(Border::find($border_id), $data, $type)) {
            flash($type.' created successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }


    /**********************************************************************************************

    ETC

     **********************************************************************************************/

     /**
     * Creates or edits a border item.
     *
     * @param App\Services\BorderService $service
     * @param int|null                   $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateItem(Request $request, BorderService $service, $id) {
        if ($item = $service->createItem(Border::find($id), Auth::user())) {
            flash('Border item created successfully.')->success();

            return redirect()->to('admin/data/items/edit/'.$item->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

}
