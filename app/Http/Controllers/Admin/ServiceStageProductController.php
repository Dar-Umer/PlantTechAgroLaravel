<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ServiceStage;
use App\Models\ServiceStageProduct;
use Illuminate\Http\Request;

class ServiceStageProductController extends Controller
{
    public function index(ServiceStage $stage)
    {
        $stage->load(['service', 'products.product']);
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'unit', 'rate', 'gst_rate']);

        return view('admin.stage_products.index', compact('stage', 'products'));
    }

    public function store(Request $request, ServiceStage $stage)
    {
        $data = $this->validated($request);

        $exists = ServiceStageProduct::where('service_stage_id', $stage->id)
            ->where('product_id', $data['product_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This product is already added to the stage.');
        }

        ServiceStageProduct::create($data + ['service_stage_id' => $stage->id]);

        return redirect()->route('admin.stage-products.index', $stage)->with('success', 'Material added to stage template.');
    }

    public function update(Request $request, ServiceStage $stage, ServiceStageProduct $stageProduct)
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $stageProduct->update($data);

        return redirect()->route('admin.stage-products.index', $stage)->with('success', 'Material updated.');
    }

    public function destroy(ServiceStage $stage, ServiceStageProduct $stageProduct)
    {
        $stageProduct->delete();

        return redirect()->route('admin.stage-products.index', $stage)->with('success', 'Material removed from stage template.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
