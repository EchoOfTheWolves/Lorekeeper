<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Pet\Pet;
use App\Models\Shop\Shop;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopStock;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller {
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
    public function getIndex() {
        return view('admin.shops.shops', [
            'shops' => Shop::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create shop page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShop() {
        // get all items where they have a tag 'coupon'
        $coupons = Item::whereHas('tags', function ($query) {
            $query->where('tag', 'coupon')->where('is_active', 1);
        })->orderBy('name')->pluck('name', 'id');

        return view('admin.shops.create_edit_shop', [
            'shop'            => new Shop,
            'shop_categories' => ['none' => 'No category'] + ShopCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'items'   => Item::orderBy('name')->pluck('name', 'id'),
            'coupons' => $coupons,
        ]);
    }

    /**
     * Shows the edit shop page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShop($id) {
        $shop = Shop::find($id);
        if (!$shop) {
            abort(404);
        }

        // get all items where they have a tag 'coupon'
        $coupons = Item::released()->whereHas('tags', function ($query) {
            $query->where('tag', 'coupon');
        })->orderBy('name')->pluck('name', 'id');

        return view('admin.shops.create_edit_shop', [
            'shop'            => $shop,
            'items'           => Item::orderBy('name')->pluck('name', 'id'),
            'pets'       => Pet::orderBy('name')->pluck('name', 'id'),
            'currencies'      => Currency::orderBy('name')->pluck('name', 'id'),
            'shop_categories' => ['none' => 'No category'] + ShopCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'coupons'    => $coupons,
        ]);
    }

    /**
     * Creates or edits a shop.
     *
     * @param App\Services\ShopService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShop(Request $request, ShopService $service, $id = null) {
        $id ? $request->validate(Shop::$updateRules) : $request->validate(Shop::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_active', 'visible_only', 'shop_category_id', 'is_staff', 'use_coupons', 'is_fto', 'allowed_coupons', 'is_timed_shop', 'start_at', 'end_at', 'quotes',
        ]);
        if ($id && $service->updateShop(Shop::find($id), $data, Auth::user())) {
            flash('Shop updated successfully.')->success();
        } elseif (!$id && $shop = $service->createShop($data, Auth::user())) {
            flash('Shop created successfully.')->success();

            return redirect()->to('admin/data/shops/edit/'.$shop->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * loads the create stock modal.
     *
     * @param mixed $id
     */
    public function getCreateShopStock($id) {
        $shop = Shop::find($id);
        if (!$shop) {
            abort(404);
        }

        return view('admin.shops._stock_modal', [
            'shop'       => $shop,
            'currencies' => Currency::orderBy('name')->pluck('name', 'id'),
            'stock'      => new ShopStock,
        ]);
    }

    /**
     * loads the edit stock modal.
     *
     * @param mixed $id
     */
    public function getEditShopStock($id) {
        $stock = ShopStock::find($id);
        if (!$stock) {
            abort(404);
        }

        return view('admin.shops._stock_modal', [
            'shop'       => $stock->shop,
            'stock'      => $stock,
            'currencies' => Currency::orderBy('name')->pluck('name', 'id'),
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * gets stock of a certain type.
     */
    public function getShopStockType(Request $request) {
        $type = $request->input('type');
        if (!$type) {
            return null;
        }
        // get base modal from type using asset helper
        $model = getAssetModelString(strtolower($type));
        log::info([$model, $type]);

        return view('admin.shops._stock_item', [
            'items' => $model::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Edits a shop's stock.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditShopStock(Request $request, ShopService $service, $id) {
        $data = $request->only([
            'shop_id', 'item_id', 'currency_id', 'cost', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit', 'purchase_limit_timeframe', 'is_fto', 'stock_type', 'is_visible',
            'restock', 'restock_quantity', 'restock_interval', 'range', 'disallow_transfer', 'is_timed_stock', 'start_at', 'end_at',
        ]);
        if ($service->editShopStock(ShopStock::find($id), $data, Auth::user())) {
            flash('Shop stock updated successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Edits a shop's stock.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateShopStock(Request $request, ShopService $service, $id) {
        $data = $request->only([
            'shop_id', 'item_id', 'currency_id', 'cost', 'use_user_bank', 'use_character_bank', 'is_limited_stock', 'quantity', 'purchase_limit', 'purchase_limit_timeframe', 'is_fto', 'stock_type', 'is_visible',
            'restock', 'restock_quantity', 'restock_interval', 'range', 'is_timed_stock', 'start_at', 'end_at',
        ]);
        if ($service->updateShopStock(Shop::find($id), $data, Auth::user())) {
            flash('Shop stock updated successfully.')->success();

            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the stock deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShopStock($id) {
        $stock = ShopStock::find($id);

        return view('admin.shops._delete_stock', [
            'stock' => $stock,
        ]);
    }

    /**
     * Deletes a stock.
     *
     * @param App\Services\StockService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShopStock(Request $request, ShopService $service, $id) {
        $stock = ShopStock::find($id);
        $shop = $stock->shop;
        if ($id && $service->deleteStock($stock)) {
            flash('Stock deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/shops/edit/'.$shop->id);
    }

    /**
     * Gets the shop deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShop($id) {
        $shop = Shop::find($id);

        return view('admin.shops._delete_shop', [
            'shop' => $shop,
        ]);
    }

    /**
     * Deletes a shop.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShop(Request $request, ShopService $service, $id) {
        if ($id && $service->deleteShop(Shop::find($id))) {
            flash('Shop deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/shops');
    }

    /**
     * Sorts shops.
     *
     * @param App\Services\ShopService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShop(Request $request, ShopService $service) {
        if ($service->sortShop($request->get('sort'))) {
            flash('Shop order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    public function postRestrictShop(Request $request, ShopService $service, $id) {
        $data = $request->only([
            'item_id', 'is_restricted',
        ]);

        if ($service->restrictShop($data, $id)) {
            flash('Shop limits updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
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
    public function getShopCategoryIndex() {
        return view('admin.shops.shop_categories', [
            'categories' => ShopCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create shop category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShopCategory() {
        return view('admin.shops.create_edit_shop_category', [
            'category' => new ShopCategory,
        ]);
    }

    /**
     * Shows the edit shop category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShopCategory($id) {
        $category = ShopCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.shops.create_edit_shop_category', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits an shop category.
     *
     * @param App\Services\ShopService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShopCategory(Request $request, ShopService $service, $id = null) {
        $id ? $request->validate(ShopCategory::$updateRules) : $request->validate(ShopCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image',
        ]);
        if ($id && $service->updateShopCategory(ShopCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } elseif (!$id && $category = $service->createShopCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();

            return redirect()->to('admin/data/shops/shop-categories/edit/'.$category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the shop category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShopCategory($id) {
        $category = ShopCategory::find($id);

        return view('admin.shops._delete_shop_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an shop category.
     *
     * @param App\Services\ShopService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShopCategory(Request $request, ShopService $service, $id) {
        if ($id && $service->deleteShopCategory(ShopCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/shops/shop-categories');
    }

    /**
     * Sorts shop categories.
     *
     * @param App\Services\ShopService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShopCategory(Request $request, ShopService $service) {
        if ($service->sortShopCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
