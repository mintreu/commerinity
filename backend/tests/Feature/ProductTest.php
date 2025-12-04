<?php



use Mintreu\LaravelProductCatalogue\Models\Product;


beforeEach(function () {
    $this->seed(\Database\Seeders\FilterSeeder::class);
    $this->seed(\Database\Seeders\CategorySeeder::class);

    $this->filterGroup = \Mintreu\LaravelProductCatalogue\Models\FilterGroup::find(1);
});



test('product can have multiple stock records', function () {

    $product = \Mintreu\LaravelProductCatalogue\Services\ProductManager::create(Product::factory()->raw([
        'name' => 'Sample Product',
        'url' => 'sample-product',
        'filter_group_id' => $this->filterGroup->id
    ]));

    $this->assertDatabaseHas('products', ['id' => $product->id]);
    $this->assertDatabaseHas('products', ['url' => $product->url]);

});


