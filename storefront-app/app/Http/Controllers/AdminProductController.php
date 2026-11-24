<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Exceptions\InvalidProductDataException;
use App\Exceptions\ProductInUseException;
use App\Repositories\ProductRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

# admin product management: list / add / edit / delete
class AdminProductController extends Controller
{
    public function __construct(protected ProductRepository $products){}

    public function index():View{
        return view('admin.index', ['products' => $this->products->get_all_products()]);
    }

    public function create():View{
        return view('admin.products.form', ['product' => null]);
    }

    public function store(Request $request):RedirectResponse{
        $data = $request->validate($this->rules() + [
            'stock' => 'required|integer|min:0|max:1000000'
        ]);
        try {
            $sku = $this->products->insert_product($data['product_name'], $data['description'],
                $this->to_cents($data['price']), (int) $data['stock'], $data['img_url'] ?? '',
                ProductStatus::from($data['status']));
        } catch (InvalidProductDataException $e) {
            return back()->withErrors(['price' => $e->getMessage()])->withInput();
        }
        return redirect()->route('admin.products.index')->with('status', 'Product #' . $sku . ' added.');
    }

    public function edit(int $sku):View{
        $product = $this->products->get_product_by_sku($sku);
        if (!$product) {
            abort(404);
        }
        return view('admin.products.form', ['product' => $product]);
    }

    public function update(Request $request, int $sku):RedirectResponse{
        $data = $request->validate($this->rules() + [
            'stock' => 'required|integer|min:0|max:1000000',
            'original_stock' => 'required|integer|min:0'
        ]);
        if (!$this->products->get_product_by_sku($sku)) {
            abort(404);
        }
        # admin types the new number, but we apply the difference (+/-) instead of overwriting
        # so a checkout that happened while the form was open doesn't get clobbered
        $change = (int) $data['stock'] - (int) $data['original_stock'];
        if ($change < 0 && !$this->products->decrease_stock($sku, -$change)) {
            return back()->withErrors(['stock' => 'Stock changed while you were editing (someone bought some), reload and try again.'])->withInput();
        }
        if ($change > 0) {
            $this->products->increase_stock($sku, $change);
        }
        try {
            $this->products->update_product($sku, $data['product_name'], $data['description'],
                $this->to_cents($data['price']), $data['img_url'] ?? '');
        } catch (InvalidProductDataException $e) {
            return back()->withErrors(['price' => $e->getMessage()])->withInput();
        }
        $this->products->update_product_status($sku, ProductStatus::from($data['status']));
        return redirect()->route('admin.products.index')->with('status', 'Product #' . $sku . ' updated.');
    }

    # "delete" = soft delete, set to RETIRED so it disappears from the shop
    # hard deleting would break old orders (ordered_cart FK is ON DELETE RESTRICT anyway)
    # can bring it back by setting status to ACTIVE in the edit form
    public function retire(int $sku):RedirectResponse{
        if (!$this->products->update_product_status($sku, ProductStatus::RETIRED)) {
            abort(404);
        }
        return redirect()->route('admin.products.index')
            ->with('status', 'Product #' . $sku . ' retired, it\'s hidden from the shop now.');
    }

    # permanent delete, only from the edit page
    # FK is ON DELETE RESTRICT so postgres blocks it if it's in any cart/order -> tell them to retire instead
    public function destroy(int $sku):RedirectResponse{
        try {
            if (!$this->products->delete_product($sku)) {
                abort(404);
            }
        } catch (ProductInUseException $e) {
            return back()->withErrors(['delete' => 'Can\'t delete, this product is in a cart or an order. Retire it instead.']);
        }
        return redirect()->route('admin.products.index')->with('status', 'Product #' . $sku . ' permanently deleted.');
    }

    # shared by store + update || Rule::enum keeps status to the 3 enum values
    protected function rules():array{
        return [
            'product_name' => 'required|max:100',
            'description' => 'required',
            'price' => 'required|numeric|min:0.01|max:1000000',
            'img_url' => 'nullable|max:2000',
            'status' => ['required', Rule::enum(ProductStatus::class)]
        ];
    }

    # form takes dollars, db stores cents
    protected function to_cents(string $price):int{
        return (int) round(((float) $price) * 100);
    }
}
