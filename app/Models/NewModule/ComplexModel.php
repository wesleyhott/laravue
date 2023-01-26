<?php

namespace App\Models\Recipe;

use App\Models\Meal\Meal;
use App\Models\LaravueModel;
use App\Models\Meal\MealProduct;
use App\Models\Commons\Traits\CanBeMarketable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class Product
 *
 * @package App\Models\Recipe
 *
 * @property String $name
 * @property String $description
 * @property int $portion_ready
 * @property int $buffer
 * @property int $product_category_id
 * @property int $portion_ready_unit_id
 * @property int $buffer_unit_id
 * @property ProductCategory $category
 * @property Unit $portionReadyUnit
 * @property Unit $bufferUnit
 * @property Recipe $recipe
 * {{ laravue-insert:property }}
 */
class ComplexModel extends LaravueModel
{

    protected $table = 'new_module.complex_models';

    public function category(): BelongsTo
    {
        return $this->belongsTo(tCategory::class);
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, (new MealProduct)->getTable());
    }

    // {{ laravue-insert:relationship }}
}
