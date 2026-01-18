<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks\Pages;

use App\Filament\Resources\Ecommerce\ProductStocks\ProductStockResource;
use App\Models\Ecommerce\Product;
use App\Services\MoneyService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CreateProductStock extends CreateRecord
{
    protected static string $resource = ProductStockResource::class;

    protected static ?string $title = 'Create Purchase Entry';



    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->schema([

                Section::make('Purchase Info')
//                    ->aside()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('supplier_id')
                            ->columnSpan(2)
                            ->relationship('supplier', 'name'),
                        TextInput::make('purchase_invoice_number'),

                        Grid::make(2)->schema([
                            DatePicker::make('purchase_date'),
                            DatePicker::make('expiry_date'),
                        ])->columnSpan(2),

                    ]),




                Repeater::make('items')
                    ->label('Items Details')
                    ->columnSpanFull()
                    ->columns(5)
                    ->table([
                        TableColumn::make('Product')->width('300px'),
                        TableColumn::make('Batch')->width('200px'),
                        TableColumn::make('Qty')->width('200px'),
                        TableColumn::make('Cost')->width('200px'),
                        TableColumn::make('WareHouse')->width('300px'),
                    ])
                    ->schema([

                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->columnSpan(2)
                            ->required(),

                        TextInput::make('batch_number'),

                        TextInput::make('quantity')
                            ->required()
                            ->numeric(),

                        TextInput::make('landing_cost')
                            ->label('Cost')
                            ->hintIconTooltip('Landing Cost')
                            ->numeric()
                            ->prefix(MoneyService::make(0)->getCurrency()),

                        Select::make('address_id')
                            ->label('Warehouse')
                            ->relationship('address', 'title')
                            ->columnSpanFull(),

                    ]),




            ]);
    }




    public function create(bool $another = false): void
    {

        $data = $this->form->getState();
        $items = $data['items'];
        $effectedProducts = Product::whereIn('id',collect($items)->pluck('product_id')->toArray())->get();
        foreach ($items as $item)
        {
            $product = $effectedProducts->where('id',$item['product_id'])->first();
            $newStock = $product->stocks()->create([
                'init_quantity' => $item['quantity'],
                'address_id' => $item['address_id'],
                'landing_cost' => $item['landing_cost'],
                'supplier_id' => $data['supplier_id'],
                'purchase_invoice_number' => $data['purchase_invoice_number'],
                'purchase_date'  => $data['purchase_date'],
                'expiry_date'  => $data['expiry_date'],
                'batch_number' => $item['batch_number'],
            ]);

            Notification::make()->title('New Stock Added')->body($item['quantity'].' Stock Added For Product '.$product->name)->success()->send();
        }
        Notification::make()->title('Purchase Entry Saved Successfully')->success()->send();

        $this->redirect(self::$resource::getUrl('index'));
    }


}
