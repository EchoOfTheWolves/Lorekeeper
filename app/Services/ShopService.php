<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Shop\Shop;
use App\Models\Shop\ShopStock;
use App\Models\Shop\ShopCategory;

class ShopService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Shop Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of shops and shop stock.
    |
    */

    /**********************************************************************************************
     
        SHOPS

    **********************************************************************************************/
    
    /**
     * Creates a new shop.
     *
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\Shop
     */
    public function createShop($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateShopData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            if(isset($data['shop_category_id']) && $data['shop_category_id'] == 'none') $data['shop_category_id'] = null;
            if((isset($data['shop_category_id']) && $data['shop_category_id']) && !ShopCategory::where('id', $data['shop_category_id'])->exists()) throw new \Exception("The selected shop category is invalid.");

            $shop = Shop::create($data);

            if ($image) $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Updates a shop.
     *
     * @param  \App\Models\Shop\Shop  $shop
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\Shop
     */
    public function updateShop($shop, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Shop::where('name', $data['name'])->where('id', '!=', $shop->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateShopData($data, $shop);

            $image = null;            
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            if(isset($data['shop_category_id']) && $data['shop_category_id'] == 'none') $data['shop_category_id'] = null;
            if((isset($data['shop_category_id']) && $data['shop_category_id']) && !ShopCategory::where('id', $data['shop_category_id'])->exists()) throw new \Exception("The selected shop category is invalid.");

            $shop->update($data);

            if ($shop) $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    
    /**
     * Updates shop stock.
     *
     * @param  \App\Models\Shop\Shop  $shop
     * @param  array                  $data 
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Shop\Shop
     */
    public function updateShopStock($shop, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(isset($data['item_id'])) {
                foreach($data['item_id'] as $key => $itemId)
                {
                    if($data['cost'][$key] == null) throw new \Exception("One or more of the items is missing a cost.");
                    if($data['cost'][$key] < 0) throw new \Exception("One or more of the items has a negative cost.");
                }

                // Clear the existing shop stock
                $shop->stock()->delete();

                foreach($data['item_id'] as $key => $itemId)
                {
                    $shop->stock()->create([
                        'shop_id'               => $shop->id,
                        'item_id'               => $data['item_id'][$key],
                        'currency_id'           => $data['currency_id'][$key],
                        'cost'                  => $data['cost'][$key],
                        'use_user_bank'         => isset($data['use_user_bank'][$key]),
                        'use_character_bank'    => isset($data['use_character_bank'][$key]),
                        'is_limited_stock'      => isset($data['is_limited_stock'][$key]),
                        'quantity'              => isset($data['is_limited_stock'][$key]) ? $data['quantity'][$key] : 0,
                        'purchase_limit'        => $data['purchase_limit'][$key],
                    ]);
                }
            } else {
                // Clear the existing shop stock
                $shop->stock()->delete();
            }

            return $this->commitReturn($shop);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a shop.
     *
     * @param  array                  $data 
     * @param  \App\Models\Shop\Shop  $shop
     * @return array
     */
    private function populateShopData($data, $shop = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);
        $data['visible_only'] = isset($data['visible_only']);
        
        if(isset($data['remove_image']))
        {
            if($shop && $shop->has_image && $data['remove_image']) 
            { 
                $data['has_image'] = 0; 
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName); 
            }
            unset($data['remove_image']);
        }

        if(isset($data['shop_category_id']) && $data['shop_category_id'] == 'none') $data['shop_category_id'] = null;
            if((isset($data['shop_category_id']) && $data['shop_category_id']) && !ShopCategory::where('id', $data['shop_category_id'])->exists()) throw new \Exception("The selected shop category is invalid.");


        return $data;
    }
    
    /**
     * Deletes a shop.
     *
     * @param  \App\Models\Shop\Shop  $shop
     * @return bool
     */
    public function deleteShop($shop)
    {
        DB::beginTransaction();

        try {
            // Delete shop stock
            $shop->stock()->delete();

            if($shop->has_image) $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName); 
            $shop->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts shop order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortShop($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Shop::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************
        SHOP CATEGORIES
    **********************************************************************************************/

    /**
     * Create a category.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Shop\ShopCategory|bool
     */
    public function createShopCategory($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateCategoryData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }
            else $data['has_image'] = 0;

            $category = ShopCategory::create($data);

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
     * @param  \App\Models\Shop\ShopCategory  $category
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Shop\ShopCategory|bool
     */
    public function updateShopCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(ShopCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateCategoryData($data, $category);

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
     * @param  \App\Models\Shop\ShopCategory|null  $category
     * @return array
     */
    private function populateCategoryData($data, $category = null)
    {
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
     * @param  \App\Models\Shop\ShopCategory  $category
     * @return bool
     */
    public function deleteShopCategory($category)
    {
        DB::beginTransaction();

        try {
            // Check first if the category is currently in use
            if(Shop::where('shop_category_id', $category->id)->exists()) throw new \Exception("A shop with this category exists. Please change its category first.");

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
    public function sortShopCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                ShopCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}