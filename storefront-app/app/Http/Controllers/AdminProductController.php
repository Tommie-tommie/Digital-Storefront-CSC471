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
            'stock_change' => 'nullable|integer|min:-1000000|max:1000000'
        ]);
        if (!$this->products->get_product_by_sku($sku)) {
            abort(404);
        }
        # stock is +/- instead of overwriting so it doesn't clobber checkouts happening at the same time
        $change = (int) ($data['stock_change'] ?? 0);
        if ($change < 0 && !$this->products->decrease_stock($sku, -$change)) {
            return back()->withErrors(['stock_change' => 'Can\'t remove more stock than there is.'])->withInput();
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

    # FK is ON DELETE RESTRICT, so if it's been ordered/carted we retire it instead
    public function destroy(int $sku):RedirectResponse{
        try {
            $this->products->delete_product($sku);
            return redirect()->route('admin.products.index')->with('status', 'Product #' . $sku . ' deleted.');
        } catch (ProductInUseException $e) {
            $this->products->update_product_status($sku, ProductStatus::RETIRED);
            return redirect()->route('admin.products.index')
                ->with('status', 'Product #' . $sku . ' is in carts/orders, so it was set to RETIRED instead.');
        }
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
