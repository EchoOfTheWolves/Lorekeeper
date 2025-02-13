<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;
use App\Models\Item\Item;
use App\Models\Currency\Currency;

use App\Services\ShopService;
use App\Models\Shop\ShopCategory;

use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Shop Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of shops and shop stock.
    |
    */

    /**
     * Shows the shop index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.shops.shops', [
            'shops' => Shop::orderBy('sort', 'DESC')->get()
        ]);
    }
    
    /**
     * Shows the create shop page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShop()
    {
        return view('admin.shops.create_edit_shop', [
            'shop' => new Shop,
            'shop_categories' => ['none' => 'No category'] + ShopCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }
    
    /**
     * Shows the edit shop page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShop($id)
    {
        $shop = Shop::find($id);
        if(!$shop) abort(404);
        return view('admin.shops.create_edit_shop', [
            'shop' => $shop,
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::orderBy('name')->pluck('name', 'id'),
            'shop_categories' => ['none' => 'No category'] + ShopCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a shop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShop(Request $request, ShopService $service, $id = null)
    {
        $id ? $request->validate(Shop::$updateRules) : $request->validate(Shop::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_active', 'visible_only', 'shop_category_id'
        ]);
        if($id && $service->updateShop(Shop::find($id), $data, Auth::user())) {
            flash('Shop updated successfully.')->success();
        }
        else if (!$id && $shop = $service->createShop($data, Auth::user())) {
            flash('Shop created successfully.')->success();
            return redirect()->to('admin/data/shops/edit/'.$shop->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Edits a shop's stock.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditShopStock(Request $request, ShopService $service, $id)
    {
        $data = $request->only([
            'shop_id', 'item_id', 'currency_id', 'cost', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit'
        ]);
        if($service->updateShopStock(Shop::find($id), $data, Auth::user())) {
            flash('Shop stock updated successfully.')->success();
            return redirect()->back();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Gets the shop deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShop($id)
    {
        $shop = Shop::find($id);
        return view('admin.shops._delete_shop', [
            'shop' => $shop,
        ]);
    }

    /**
     * Deletes a shop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShop(Request $request, ShopService $service, $id)
    {
        if($id && $service->deleteShop(Shop::find($id))) {
            flash('Shop deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/shops');
    }

    /**
     * Sorts shops.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShop(Request $request, ShopService $service)
    {
        if($service->sortShop($request->get('sort'))) {
            flash('Shop order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************
        SHOP CATEGORIES
    **********************************************************************************************/

    /**
     * Shows the shop category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShopCategoryIndex()
    {
        return view('admin.shops.shop_categories', [
            'categories' => ShopCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create shop category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShopCategory()
    {
        return view('admin.shops.create_edit_shop_category', [
            'category' => new ShopCategory
        ]);
    }

    /**
     * Shows the edit shop category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShopCategory($id)
    {
        $category = ShopCategory::find($id);
        if(!$category) abort(404);
        return view('admin.shops.create_edit_shop_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits an shop category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShopCategory(Request $request, ShopService $service, $id = null)
    {
        $id ? $request->validate(ShopCategory::$updateRules) : $request->validate(ShopCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image'
        ]);
        if($id && $service->updateShopCategory(ShopCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createShopCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/data/shops/shop-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the shop category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShopCategory($id)
    {
        $category = ShopCategory::find($id);
        return view('admin.shops._delete_shop_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an shop category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShopCategory(Request $request, ShopService $service, $id)
    {
        if($id && $service->deleteShopCategory(ShopCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/shops/shop-categories');
    }

    /**
     * Sorts shop categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ShopService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShopCategory(Request $request, ShopService $service)
    {
        if($service->sortShopCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}
